<?php

namespace Cirebonweb\Controllers\Admin\User;

use App\Controllers\BaseController;
use Cirebonweb\Models\Auth\AuthLoginsModel;

class UserLogin extends BaseController
{
    protected $authLoginsModel;

    public function __construct()
    {
        $this->authLoginsModel = new AuthLoginsModel();
    }

    public function index()
    {
        $data = [
            'pageTitle' => 'Informasi User Login',
            'navTitle' => 'User Login',
            'navigasi' => '<a href="/admin/user">User</a> &nbsp;',
        ];
        return view('Cirebonweb\admin\user\user_login', $data);
    }

    public function tabel()
    {
        $data['data'] = array();
        $result = $this->authLoginsModel->tabel();
        foreach ($result as $key => $value) {

            $status = $value->success ? '✅ Berhasil' : '❌ Gagal';
            
            $ip_address = ($value->ip_address === '::1') ? '127.0.0.1' : $value->ip_address;

            $data['data'][$key] = [
                $value->auth_login_id,
                '<input type="checkbox" class="checkItem" value="' . $value->auth_login_id . '">',
                $status,
                $value->date,
                $value->id_type,
                $value->username ? $value->username : '-',
                $value->identifier,
                $ip_address,
                $value->perangkat ? $value->perangkat : '-',
                $value->os,
                $value->browser,
                $value->brand . ' → ' . $value->model,
                $value->user_agent,
                $value->negara ? $value->negara : '-',
                $value->wilayah ? $value->wilayah : '-',
                $value->distrik ? $value->distrik : '-',
                $value->zona_waktu ? $value->zona_waktu : '-',
                $value->isp ? $value->isp : '-',
                $value->tipe ? $value->tipe : '-',
                $value->error ? $value->error : '-'
            ];
        }
        return $this->response->setJSON($data);
    }

    public function hapus()
    {
        $response = [];
        $ids = $this->request->getPost('ids');

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'Tidak ada data yang dipilih.']);
        }

        $ids = array_filter($ids, 'is_numeric');
        if (empty($ids)) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'Data ID tidak valid.']);
        }

        $isDelete = $this->authLoginsModel->whereIn('id', $ids)->delete();

        if ($isDelete) {
            $isEmpty = $this->authLoginsModel->cekNull() === null;
            if ($isEmpty) {
                $this->db->query("ALTER TABLE auth_logins AUTO_INCREMENT = 1");
                $this->db->query("ALTER TABLE user_device AUTO_INCREMENT = 1");
            }

            cache()->delete('statistik_user_login');

            $response = ['sukses'  => true, 'messages' => lang("App.delete-success")];
        } else {
            $response = ['sukses'  => false, 'messages' => lang("App.delete-error")];
        }
        return $this->response->setJSON($response);
    }

    public function reset()
    {
        $response = [];

        try {
            $this->authLoginsModel->emptyTable();
            $this->db->query("ALTER TABLE auth_logins AUTO_INCREMENT = 1");
            $this->db->query("ALTER TABLE user_login AUTO_INCREMENT = 1");

            cache()->delete('statistik_user_login');

            $response = ['success' => true, 'messages' => lang("App.delete-success")];
        } catch (\Throwable $e) {
            log_message('error', 'Reset failed: ' . $e->getMessage());
            $response = ['success' => false, 'messages' => lang("App.delete-error")];
        }

        return $this->response->setJSON($response);
    }

    public function refresh()
    {
        try {
            cache()->delete('statistik_user_login');

            return $this->response->setJSON([
                'success' => true,
                'messages' => 'Database statistik berhasil di generate ulang.'
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'messages' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}