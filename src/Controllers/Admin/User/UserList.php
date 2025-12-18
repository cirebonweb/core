<?php

namespace Cirebonweb\Controllers\Admin\User;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Entities\User;
use Cirebonweb\Models\User\UserModel;
use Cirebonweb\Models\User\UserProfilModel;
use Cirebonweb\Libraries\TabelLibrari;
use CodeIgniter\Shield\Models\GroupModel;

class UserList extends BaseController
{
    protected $userModel;
    protected $userProfilModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userProfilModel = new UserProfilModel();
    }

    public function index()
    {
        $data = [
            'url' => 'admin/user/user-list',
            'pageTitle' => 'Daftar User List',
            'navTitle' => 'User List',
            'navigasi' => '<a href="/admin/user">User</a> &nbsp;',
            'listGroup' => $this->groupList()
        ];
        return view('Cirebonweb\admin\user\user_list', $data);
    }

    protected function groupList()
    {
        // $groupModel = new GroupModel();
        // return $groupModel->findColumn('group');
        return ['admin', 'klien'];
    }

    public function tabel()
    {
        $builder = $this->userModel->tabel();

        // Filter tambahan dari AJAX
        $filterActive = $this->request->getPost('filter_active');
        if ($filterActive !== null && $filterActive !== '') {
            $builder->where('active', $filterActive);
        }

        if ($filterGroup = $this->request->getPost('filter_group')) {
            $builder->where('group', $filterGroup);
        }

        // List group untuk dropdown
        $listGroup = $this->groupList();
        $encrypter = service('encrypter');

        $dataTable = new TabelLibrari($builder, $this->request);
        $dataTable->setSearchable(['a.username', 'b.secret']);
        $dataTable->setDefaultOrder(['a.id' => 'asc']);

        $dataTable->setRowCallback(function ($row) use ($listGroup, $encrypter) {
            $statusDropdown = '<select class="form-select form-select-sm" onchange="updateStatus(this, ' . $row->iduser . ')">';
            $statusDropdown .= '<option value="1"' . ($row->active ? ' selected' : '') . '>Aktif</option>';
            $statusDropdown .= '<option value="0"' . (!$row->active ? ' selected' : '') . '>Non Aktif</option>';
            $statusDropdown .= '</select>';

            $aksesDropdown = '<select class="form-select form-select-sm" onchange="updateGrup(this, ' . $row->iduser . ')">';
            foreach ($listGroup as $group) {
                $selected = ($row->group == $group) ? ' selected' : '';
                $aksesDropdown .= '<option value="' . $group . '"' . $selected . '>' . ucfirst($group) . '</option>';
            }
            $aksesDropdown .= '</select>';

            $idUser = base64_encode($encrypter->encrypt($row->iduser));
            $aksi = '<div class="btn-group" role="group"><a class="btn btn-sm btn-dark" type="button" onclick="simpan(' . $row->iduser . ')">edit</a>';
            $aksi .= '<a href="' . base_url('profil/?id=') . urlencode($idUser) . '" class="btn btn-sm btn-success" role="button">profil</a></div>';

            return [
                $row->iduser,
                esc($row->username),
                esc($row->secret),
                $statusDropdown,
                $aksesDropdown,
                // $row->last_used_at ? date('d M Y → H:i:s', strtotime($row->last_used_at)) : 'null',
                $row->last_used_at,
                $row->created_at,
                $row->updated_at,
                // date('d M Y → H:i:s', strtotime($row->created_at)),
                // date('d M Y → H:i:s', strtotime($row->updated_at)),
                $aksi,
                $row->active ? 'aktif' : 'non aktif',
                esc($row->group)
            ];
        });

        return $this->response->setJSON($dataTable->getResult());
    }

    public function getId()
    {
        $id = $this->request->getPost('userId');
        if ($this->validation->check($id, 'required|numeric')) {
            $data = $this->userModel->getId($id);
            return $this->response->setJSON(['success' => true, 'data' => $data]);
            // return $this->response->setJSON($data);
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    public function simpan()
    {
        // 🔒 Cek permission di awal
        if (! auth()->user()->can('klien.create')) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'Anda tidak memiliki izin untuk menambah user.']);
        }

        $response = [];
        $users = auth()->getProvider();

        $aktivasi = $this->request->getPost('aktivasi');
        $status = ($aktivasi === '0') ? 'banned' : null;

        $user = new User([
            'username' => $this->request->getPost('username'),
            'status' => $status,
            'active' => $aktivasi,
            'email'    => trim($this->request->getPost('email')),
            'password' => $this->request->getPost('password')
        ]);

        $rules = [
            'username' => ['label' => 'Nama User', 'rules' => 'required|min_length[3]|max_length[30]|is_unique[users.username]'],
            'email' => ['label' => 'Alamat Email', 'rules' => 'required|max_length[50]|valid_email|is_unique[auth_identities.secret]'],
            'password' => ['label' => 'Password', 'rules' => 'required|min_length[8]|max_byte[72]|strong_password[]'],
            'password_confirm' => ['label' => 'Konfirmasi Password', 'rules' => 'matches[password]'],
        ];

        $data = $this->request->getPost(array_keys($rules));

        try {
            $this->db->transStart();

            if (! $this->validateData($data, $rules)) {
                $response = ['success'  => false, 'messages' => $this->validation->getErrors()];
            } else {
                if ($users->save($user)) {
                    $idUser = $users->getInsertID();
                    $user = $users->findById($idUser);

                    $hakAkses = $this->request->getPost('akses');
                    $user->addGroup($hakAkses);

                    if (! $this->userProfilModel->insert(['user_id' => $idUser])) {
                        $this->db->transRollback();
                        return $this->response->setJSON(['success'  => false, 'messages' => $this->userProfilModel->getErrors()]);
                    }

                    cache()->delete('statistik_user_list');
                    $response = ['success'  => true, 'messages' => lang("App.insert-success")];
                } else {
                    $response = ['success'  => false, 'messages' => lang("App.insert-error")];
                }
            }

            $this->db->transComplete();

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal simpan transaksi: ' . $e->getMessage());
            return $this->response->setJSON(['success'  => false, 'messages' => 'Terjadi kesalahan saat menyimpan data user.']);
        }
    }

    public function update()
    {
        // 🔒 Cek permission di awal
        if (! auth()->user()->can('klien.edit')) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'Anda tidak memiliki izin untuk mengedit user.']);
        }

        $response = [];
        $users = auth()->getProvider();
        $idUser = $this->request->getPost('iduser');
        $user = $users->findById($idUser);

        // Cek jika user ditemukan
        if (!$user) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'ID User tidak ditemukan.']);
        }

        $aktivasi = $this->request->getPost('aktivasi');
        $status = ($aktivasi === '0') ? 'banned' : null;

        $userData = [
            'username' => $this->request->getPost('username'),
            'status' => $status,
            'active' => $aktivasi,
            'email' => trim($this->request->getPost('email'))
        ];

        if ($this->request->getPost('password')) {
            $userData['password'] = $this->request->getPost('password');
        }

        $rules = [
            'username' => ['label' => 'Nama User', 'rules' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,' . $idUser . ']'],
            'email' => ['label' => 'Alamat Email', 'rules' => 'required|max_length[50]|valid_email|is_unique[auth_identities.secret,user_id,' . $idUser . ']'],
            'password' => ['label' => 'Password', 'rules' => 'permit_empty|min_length[8]|max_byte[72]|strong_password[]'],
            'password_confirm' => ['label' => 'Konfirmasi Password', 'rules' => 'matches[password]'],
        ];

        $data = $this->request->getPost(array_keys($rules));

        if (!$this->validateData($data, $rules)) {
            return $this->response->setJSON(['success'  => false, 'messages' => $this->validation->getErrors()]);
        }

        try {
            $this->db->transStart();

            $user->fill($userData);

            if ($users->save($user)) {
                $hakAkses = $this->request->getPost('akses');

                if (! $user->syncGroups($hakAkses)) {
                    return $this->response->setJSON(['success'  => false, 'messages' => 'Gagal menyimpan hak akses user.']);
                }

                $response = ['success'  => true, 'messages' => lang("App.update-success")];
            } else {
                $response = ['success'  => false, 'messages' => lang("App.update-error")];
            }

            $this->db->transComplete();
            cache()->delete('statistik_user_list');
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal simpan transaksi: ' . $e->getMessage());

            return $this->response->setJSON([
                'success'  => false,
                'messages' => 'Terjadi kesalahan saat merubah data user.'
            ]);
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

    public function updateGrup()
    {
        if ($this->request->isAJAX()) {
            try {
                $iduser = $this->request->getPost('iduser');
                $group = $this->request->getPost('group');

                if (!isset($group) || empty($iduser)) {
                    return $this->response->setJSON(['success' => false, 'messages' => lang("App.invalid-data")]);
                }

                $users = auth()->getProvider();
                $user = $users->findById($iduser);

                if ($user->syncGroups($group)) {
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
}
