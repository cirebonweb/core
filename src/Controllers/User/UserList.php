<?php

namespace Cirebonweb\Controllers\User;

use Cirebonweb\Controllers\CrbBaseController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Database\BaseBuilder;
use Cirebonweb\Models\UserModel;
use Cirebonweb\Models\UserProfilModel;
use Cirebonweb\Controllers\Traits\CrudTrait;

class UserList extends CrbBaseController
{
    use CrudTrait;
    protected UserProfilModel $userProfilModel;
    protected $searchable = ['a.username', 'b.secret'];
    protected $orderable  = [
        'a.id',
        'a.username',
        'b.secret',
        'group',
        'b.last_used_at',
        'a.active',
    ];
    protected $modelClass = UserModel::class;

    /**
     * @return list<string>
     */
    protected function grupTerdaftar(): array
    {
        $groups = config('AuthGroups')->groups ?? [];
        return is_array($groups) ? array_keys($groups) : [];
    }

    /**
     * @return list<string>
     */
    protected function grupYangBolehDitetapkan(): array
    {
        $actor = auth()->user();
        $registered = $this->grupTerdaftar();

        if ($actor !== null && $actor->inGroup('superadmin')) {
            return $registered;
        }

        if ($actor !== null && ($actor->inGroup('admin') || $actor->inGroup('developer'))) {
            return in_array('user', $registered, true) ? ['user'] : [];
        }

        return [];
    }

    /**
     * @return array<string, string> key => title
     */
    protected function opsiGrupYangBolehDitetapkan(): array
    {
        $groups  = config('AuthGroups')->groups ?? [];
        $options = [];
        foreach ($this->grupYangBolehDitetapkan() as $key) {
            $title = is_array($groups) ? ($groups[$key]['title'] ?? $key) : $key;
            $options[$key] = is_string($title) && trim($title) !== '' ? $title : $key;
        }

        return $options;
    }

    protected function dapatKelolaGrup(): bool
    {
        return $this->grupYangBolehDitetapkan() !== [];
    }

    /**
     * @param mixed $target
     * @return list<string>
     */
    protected function grupTargetSaatIni($target): array
    {
        $groups = $target->getGroups();
        if (! is_array($groups)) {
            return [];
        }

        return array_values(array_filter($groups, static fn($group) => is_string($group) && $group !== ''));
    }

    /**
     * @param mixed $target
     */
    protected function targetBolehDiubahGrup($target): bool
    {
        $actor = auth()->user();
        if ($actor === null) {
            return false;
        }

        if ($actor->inGroup('superadmin')) {
            return true;
        }

        if ($actor->inGroup('admin') || $actor->inGroup('developer')) {
            $currentGroups = $this->grupTargetSaatIni($target);
            return $currentGroups === [] || $currentGroups === ['user'];
        }

        return false;
    }

    /**
     * @param mixed $input
     * @return list<string>
     */
    protected function normalisasiGrupDiminta($input): array
    {
        if (is_string($input)) {
            $input = $input === '' ? [] : [$input];
        }

        if (! is_array($input)) {
            return [];
        }

        $groups = [];
        foreach ($input as $group) {
            if (! is_string($group) || $group === '') {
                continue;
            }
            if (! in_array($group, $groups, true)) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
     * @param mixed $target
     * @param list<string> $groups
     */
    protected function validasiPerubahanGrup($target, array $groups): ?string
    {
        $allowed = $this->grupYangBolehDitetapkan();
        if ($allowed === []) {
            return 'Anda tidak berwenang mengubah hak akses.';
        }

        if ($groups === []) {
            return 'Hak akses wajib dipilih.';
        }

        foreach ($groups as $group) {
            if (! in_array($group, $this->grupTerdaftar(), true)) {
                return 'Group tidak terdaftar.';
            }

            if (! in_array($group, $allowed, true)) {
                return 'Group yang dipilih tidak diizinkan.';
            }
        }

        if (! $this->targetBolehDiubahGrup($target)) {
            return 'Akun dengan hak akses istimewa tidak dapat diubah.';
        }

        return null;
    }

    /**
     * @param mixed $target
     * @param list<string> $groups
     */
    protected function terapkanGrup($target, array $groups): void
    {
        foreach ($target->getGroups() as $existingGroup) {
            $target->removeGroup($existingGroup);
        }

        foreach ($groups as $group) {
            $target->addGroup($group);
        }
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userProfilModel = new UserProfilModel();
    }

    public function index()
    {
        return view('Cirebonweb\user\user_list', [
            'navigasi'          => 'User &nbsp;',
            'groupOptions'      => $this->grupYangBolehDitetapkan(),
            'groupOptionLabels' => $this->opsiGrupYangBolehDitetapkan(),
            'canManageGroup'    => $this->dapatKelolaGrup(),
        ]);
    }

    public function getId(): \CodeIgniter\HTTP\ResponseInterface
    {
        if (($res = $this->ajax()) !== null) {
            return $res;
        }

        $id = $this->request->getPost('id');
        if (! $id || ! is_numeric($id)) {
            return $this->json(false, 'ID tidak valid', null, 400);
        }

        $data = $this->crudModel()->getIdCustom($id);
        if (! $data) {
            return $this->json(false, 'Data tidak ditemukan', null, 404);
        }

        $users   = auth()->getProvider();
        $target  = $users->findById((int) $id);
        $allowed = $this->opsiGrupYangBolehDitetapkan();
        $groups  = $target !== null ? $this->grupTargetSaatIni($target) : array_values(array_filter(array_map('trim', explode(',', (string) ($data->group ?? '')))));
        $groupsConfig = config('AuthGroups')->groups ?? [];
        $labels  = array_map(static function (string $group) use ($groupsConfig): string {
            $title = is_array($groupsConfig) ? ($groupsConfig[$group]['title'] ?? $group) : $group;
            return is_string($title) && trim($title) !== '' ? $title : $group;
        }, $groups);

        $canEdit = $target !== null && $allowed !== [] && $this->targetBolehDiubahGrup($target);
        $displayOptions = $allowed;
        if (! $canEdit) {
            $displayOptions = [];
            foreach ($groups as $index => $group) {
                $displayOptions[$group] = $labels[$index] ?? $group;
            }
        }

        $data->group          = implode(', ', $labels);
        $data->groups         = $groups;
        $data->group_options  = $displayOptions;
        $data->can_edit_group = $canEdit;

        return $this->json(true, null, $data);
    }

    protected function filterTabel(BaseBuilder $builder): BaseBuilder
    {
        $filterActive = $this->request->getPost('filter_active');
        if ($filterActive !== null && $filterActive !== '') {
            $builder->where('a.active', $filterActive);
        }

        return $builder;
    }

    protected function dataTabel(\stdClass $row): array
    {
        $encrypter = service('encrypter');

        $statusDropdown = '<select class="form-select form-select-sm" onchange="updateStatus(this, ' . $row->id . ')">';
        $statusDropdown .= '<option value="1"' . ($row->active ? ' selected' : '') . '>Aktif</option>';
        $statusDropdown .= '<option value="0"' . (!$row->active ? ' selected' : '') . '>Non Aktif</option>';
        $statusDropdown .= '</select>';

        $idUser = base64_encode($encrypter->encrypt($row->id));
        $aksi = '<div class="btn-group" role="group"><a class="btn btn-sm btn-dark" type="button" onclick="simpan(' . $row->id . ')">edit</a>';
        $aksi .= '<a href="' . base_url('profil/?id=') . urlencode($idUser) . '" class="btn btn-sm btn-success" role="button">profil</a></div>';

        $groupKeys   = array_values(array_filter(array_map('trim', explode(',', (string) ($row->group ?? '')))));
        $groupsConfig = config('AuthGroups')->groups ?? [];
        $groupLabels = array_map(static function (string $group) use ($groupsConfig): string {
            $title = is_array($groupsConfig) ? ($groupsConfig[$group]['title'] ?? $group) : $group;
            return is_string($title) && trim($title) !== '' ? $title : $group;
        }, $groupKeys);

        return [
            $row->id,
            esc($row->username),
            esc($row->secret),
            $statusDropdown,
            esc(implode(', ', $groupLabels)),
            $row->last_used_at,
            $row->created_at,
            $row->updated_at,
            $aksi,
            $row->active ? 'aktif' : 'non aktif'
        ];
    }

    public function simpan()
    {
        if (($res = $this->ajax()) !== null) return $res;

        $users = auth()->getProvider();

        $data = $this->request->getPost([
            'username',
            'email',
            'password',
            'password_confirm',
        ]);

        $rules = [
            'username' => 'required|max_length[30]|min_length[3]|is_unique[users.username]',
            'email'    => 'required|valid_email|max_length[254]|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]|max_byte[72]',
        ];

        if (! $this->validate($rules)) {
            $errors = implode(', ', $this->validator->getErrors());
            return $this->json(false, $errors);
        }

        $groupsDipilih = $this->normalisasiGrupDiminta($this->request->getPost('group'));
        $groupsBaru    = null;
        if ($groupsDipilih !== []) {
            $allowed    = $this->grupYangBolehDitetapkan();
            $registered = $this->grupTerdaftar();
            if ($allowed === []) {
                return $this->json(false, 'Anda tidak berwenang mengubah hak akses.');
            }
            foreach ($groupsDipilih as $groupDipilih) {
                if (! in_array($groupDipilih, $registered, true) || ! in_array($groupDipilih, $allowed, true)) {
                    return $this->json(false, 'Group yang dipilih tidak diizinkan.');
                }
            }
            $groupsBaru = $groupsDipilih;
        }

        $user = new \CodeIgniter\Shield\Entities\User($data);

        try {
            $this->db->transStart();

            if (! $users->save($user)) {
                $errors = $users->errors() ? implode(', ', $users->errors()) : lang('App.insert-error');
                $this->db->transRollback();
                return $this->json(false, $errors);
            }

            $id = $users->getInsertID();
            if (! $id) {
                $this->db->transRollback();
                return $this->json(false, 'Gagal mendapatkan ID user');
            }

            $user = $users->findById($id);

            if (! $this->userProfilModel->insert(['user_id' => $id])) {
                $errors = $this->userProfilModel->errors() ? implode(', ', $this->userProfilModel->errors()) : 'Gagal buat profil';
                $this->db->transRollback();
                return $this->json(false, $errors);
            }

            $defaultGroup  = config('AuthGroups')->defaultGroup ?? null;
            $groupsToApply = $groupsBaru ?? ((is_string($defaultGroup) && $defaultGroup !== '') ? [$defaultGroup] : []);
            if ($groupsToApply !== []) {
                $this->terapkanGrup($user, $groupsToApply);
            }

            $user->activate();

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->json(false, 'Transaksi gagal');
            }

            cache()->delete('statistik_user_list');
            return $this->json(true, lang('App.insert-success'), ['id' => $id]);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('critical', __METHOD__ . ': ' . $e->getMessage());
            return $this->json(false, 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function update()
    {
        if (($res = $this->ajax()) !== null) return $res;

        $id = $this->request->getPost('id');
        if (! $id || ! is_numeric($id)) {
            return $this->json(false, 'ID user tidak ditemukan');
        }

        $users = auth()->getProvider();
        $user  = $users->findById((int) $id);
        if (! $user) {
            return $this->json(false, 'User tidak ditemukan');
        }

        $data = array_filter($this->request->getPost([
            'username',
            'email',
            'password',
        ]), static fn($value) => $value !== '' && $value !== null);

        $rules = [
            'username' => "required|is_unique[users.username,id,{$id}]",
            'email'    => "required|valid_email|is_unique[auth_identities.secret,user_id,{$id}]",
        ];

        $password = $this->request->getPost('password');
        if (is_string($password) && $password !== '') {
            $rules['password'] = 'min_length[8]|max_byte[72]';
        }

        if (! $this->validate($rules)) {
            $errors = implode(', ', $this->validator->getErrors());
            return $this->json(false, $errors);
        }

        $groupDipilih = $this->request->getPost('group');
        $groupsDipilih = $this->normalisasiGrupDiminta($groupDipilih);
        $ubahGrup      = $groupDipilih !== null && $groupDipilih !== '';
        if ($ubahGrup) {
            $errorGrup = $this->validasiPerubahanGrup($user, $groupsDipilih);
            if ($errorGrup !== null) {
                return $this->json(false, $errorGrup);
            }
        }

        $user->fill($data);

        try {
            $this->db->transStart();

            if (! $users->save($user)) {
                $errors = $users->errors() ? implode(', ', $users->errors()) : 'Gagal memperbarui user';
                $this->db->transRollback();
                return $this->json(false, $errors);
            }

            if ($ubahGrup) {
                $this->terapkanGrup($user, $groupsDipilih);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return $this->json(false, 'Transaksi gagal');
            }

            cache()->delete('statistik_user_list');
            return $this->json(true, lang('App.update-success'));
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('critical', __METHOD__ . ': ' . $e->getMessage());
            return $this->json(false, 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function updateGrup()
    {
        if (($res = $this->ajax()) !== null) return $res;

        $id     = $this->request->getPost('id') ?? $this->request->getPost('iduser');
        $groups = $this->normalisasiGrupDiminta($this->request->getPost('group'));

        if (! $id || ! is_numeric($id) || $groups === []) {
            return $this->json(false, lang('App.invalid-data'), null, 400);
        }

        $users  = auth()->getProvider();
        $target = $users->findById((int) $id);
        if (! $target) {
            return $this->json(false, 'User tidak ditemukan', null, 404);
        }

        $errorGrup = $this->validasiPerubahanGrup($target, $groups);
        if ($errorGrup !== null) {
            return $this->json(false, $errorGrup);
        }

        try {
            $this->terapkanGrup($target, $groups);
            cache()->delete('statistik_user_list');
            return $this->json(true, lang('App.update-success'));
        } catch (\Throwable $e) {
            log_message('critical', __METHOD__ . ': ' . $e->getMessage());
            return $this->json(false, 'Gagal memperbarui hak akses: ' . $e->getMessage());
        }
    }

    public function updateStatus()
    {
        if ($this->request->isAJAX()) {
            try {
                $iduser = $this->request->getPost('iduser');
                $active = $this->request->getPost('active');

                if (!isset($active) || empty($iduser)) {
                    return $this->response->setJSON(['success' => false, 'messages' => lang("App.invalid-data")]);
                }

                $users = auth()->getProvider();
                $user = $users->findById($iduser);

                $user->fill([
                    'status' => $active ? null : 'banned',
                    'active' => $active
                ]);

                if ($users->save($user)) {
                    cache()->delete('statistik_user_list');
                    return $this->response->setJSON(['success' => true, 'messages' => lang("App.update-success")]);
                } else {
                    return $this->response->setJSON(['success' => false, 'messages' => lang("App.update-error")]);
                }
            } catch (\Throwable $e) {
                $controllerName = get_class($this) . '::' . __FUNCTION__;
                log_message('critical', $controllerName . ' ' . $e->getMessage() . ' on line ' . $e->getLine());
                return $this->response->setJSON(['success' => false, 'messages' => 'Critical: ' . $e->getMessage()]);
            }
        }

        return $this->response->setJSON(['success' => false, 'messages' => lang("App.update-error")]);
    }

    public function hapus()
    {
        return $this->json(false, lang("App.delete-disabled"));
    }
}
