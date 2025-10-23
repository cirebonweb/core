<?php

namespace Cirebonweb\Controllers\Admin\User;

use App\Controllers\BaseController;
use Cirebonweb\Models\User\UserModel;
use Cirebonweb\Models\User\UserProfilModel;
use CodeIgniter\Shield\Entities\User;
use Config\AuthGroups;

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
        ];
        return view('Cirebonweb\admin\user\user_list', $data);
    }

    public function tabel()
    {
        $encrypter = service('encrypter');
        // $authGroups = new AuthGroups();
        // $groupList = array_keys($authGroups->groups);
        $groupList = ['admin', 'klien'];
        // $aktifMap = ['1' => 'aktif', '0' => 'non aktif'];
        $aktifMap = [1 => 'aktif', 0 => 'non aktif'];

        $data['data'] = array();
        $result = $this->userModel->tabelUserList();
        foreach ($result as $key => $value) {

            $idUser = base64_encode($encrypter->encrypt($value->iduser));

            $aktifDropdown = '<select class="form-select form-select-sm" onchange="updateAktif(this, ' . $value->iduser . ')">';
            foreach ($aktifMap as $aktifKey => $label) {
                $selected = ($value->active == $aktifKey) ? ' selected' : '';
                $aktifDropdown .= '<option value="' . $aktifKey . '"' . $selected . '>' . esc($label) . '</option>';
            }
            $aktifDropdown .= '</select>';

            $groupDropdown = '<select class="form-select form-select-sm" onchange="updateGrup(this, ' . $value->iduser . ')">';
            foreach ($groupList as $groupKey) {
                $selected = ($value->group === $groupKey) ? 'selected' : '';
                $groupDropdown .= '<option value="' . $groupKey . '" ' . $selected . '>' . esc($groupKey) . '</option>';
            }
            $groupDropdown .= '</select>';

            $ops = '<div class="btn-group" role="group">';
            $ops .= '<a class="btn btn-sm btn-dark" type="button" onclick="simpan(' . $value->iduser . ')">edit</a>';
            $ops .= '<a href="' . base_url('profil/?id=') . urlencode($idUser) . '" class="btn btn-sm btn-success" role="button">profil</a>';
            $ops .= '</div>';

            $data['data'][$key] = array(
                $value->iduser,
                $value->username,
                $value->email,
                $aktifDropdown,
                $groupDropdown,
                $value->last_used_at,
                $value->created_at,
                $value->updated_at,
                $ops,
                $value->active ? 'aktif' : 'non aktif',
                $value->group
            );
        }
        return $this->response->setJSON($data);
    }

    public function getId()
    {
        $id = $this->request->getPost('userId');
        if ($this->validation->check($id, 'required|numeric')) {
            $data = $this->userModel->getId($id);
            return $this->response->setJSON($data);
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
            return $this->response->setJSON(['success'  => false,'messages' => 'Anda tidak memiliki izin untuk mengedit user.']);
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

    public function updateGrup()
    {
        if ($this->request->isAJAX()) {
            try {
                $group = $this->request->getJSON()->grup;
                $iduser = $this->request->getJSON()->iduser;

                if (empty($group) || empty($iduser)) {
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
                // logCriticalToDb($e, $controllerName);

                return $this->response->setJSON([
                    'success' => false,
                    'messages' => 'Terjadi kesalahan: ' . $e->getMessage()
                    // 'messages' => 'Terjadi kesalahan saat memproses data.'
                ]);
            }
        }

        return $this->response->setJSON(['success' => false, 'messages' => lang("App.update-error")]);
    }

    public function updateAktif()
    {
        if ($this->request->isAJAX()) {
            try {
                $active = $this->request->getJSON()->aktif;
                $iduser = $this->request->getJSON()->iduser;

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
                // logCriticalToDb($e, $controllerName);

                return $this->response->setJSON([
                    'success' => false,
                    'messages' => 'Terjadi kesalahan: ' . $e->getMessage()
                    // 'messages' => 'Terjadi kesalahan saat memproses data.'
                ]);
            }
        }

        return $this->response->setJSON(['success' => false, 'messages' => lang("App.update-error")]);
    }

    // function logCriticalToDb(\Throwable $e, string $controller)
    // {
    //     $db = \Config\Database::connect();
    //     $db->table('critical_logs')->insert([
    //         'datetime'   => date('Y-m-d H:i:s'),
    //         'controller' => $controller,
    //         'message'    => $e->getMessage(),
    //         'line'       => $e->getLine()
    //     ]);
    // }
}