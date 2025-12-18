<?php

namespace Cirebonweb\Controllers\Admin\User;

use App\Controllers\BaseController;
use Cirebonweb\Models\Auth\AuthLoginsModel;
use Cirebonweb\Libraries\TabelLibrari;

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
        $builder = $this->authLoginsModel->tabel();

        // Filter tambahan dari AJAX
        $filterStatus = $this->request->getPost('filter_status');
        if ($filterStatus !== null && $filterStatus !== '') {
            $builder->where('success', $filterStatus);
        }

        if ($filterPerangkat = $this->request->getPost('filter_perangkat')) {
            $builder->where('perangkat', $filterPerangkat);
        }

        $dataTable = new TabelLibrari($builder, $this->request);
        $dataTable->setSearchable(['id_type', 'identifier', 'os', 'browser', 'brand', 'model', 'negara', 'wilayah', 'distrik', 'isp', 'tipe', 'error', 'username']);
        $dataTable->setDefaultOrder(['a.id' => 'desc']);

        $dataTable->setRowCallback(function ($row) {
            return [
                $row->auth_id,
                '<input type="checkbox" class="checkItem" value="' . $row->auth_id . '">',
                $row->success ? '✅ Berhasil' : '❌ Gagal',
                $row->date,
                $row->id_type,
                $row->username ?? 'null',
                $row->identifier,
                $row->ip_address,
                $row->perangkat ?? 'null',
                $row->os,
                $row->browser,
                $row->brand . ' → ' . $row->model,
                $row->user_agent,
                $row->negara ?? 'null',
                $row->wilayah ?? 'null',
                $row->distrik ?? 'null',
                $row->zona_waktu ?? 'null',
                $row->isp ?? 'null',
                $row->tipe ?? 'null',
                $row->error ?? 'null'
            ];
        });

        return $this->response->setJSON($dataTable->getResult());
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

            $response = ['success'  => true, 'messages' => lang("App.delete-success")];
        } else {
            $response = ['success'  => false, 'messages' => lang("App.delete-error")];
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
