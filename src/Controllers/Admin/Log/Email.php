<?php

namespace Cirebonweb\Controllers\Admin\Log;

use App\Controllers\BaseController;
use Cirebonweb\Models\Log\LogEmailModel;

class Email extends BaseController
{
    protected $logEmailModel;

    public function __construct()
    {
        $this->logEmailModel = new LogEmailModel();
    }

    public function index()
    {
        $data = [
            'pageTitle' => 'Log Proses Email',
            'navTitle' => 'Log Email',
            'navigasi' => '<a href="/admin/log">Log</a> &nbsp;',
        ];
        return view('Cirebonweb\admin\log\log_email', $data);
    }

    public function tabel()
    {
        $data['data'] = array();
        $result = $this->logEmailModel->findAll();

        foreach ($result as $key => $value) {
            $status = $value->status ? '✅ Berhasil' : '❌ Gagal';

            $data['data'][$key] = [
                $value->id,
                '<input type="checkbox" class="checkItem" value="' . $value->id . '">',
                $value->admin,
                $value->tipe,
                $value->template,
                $value->email,
                $value->judul,
                $value->render . ' detik',
                $status,
                $value->error ? $value->error : '-',
                $value->dibuat
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

        $isDelete = $this->logEmailModel->whereIn('id', $ids)->delete();

        if ($isDelete) {
            $isEmpty = $this->logEmailModel->cekNull() === null;
            if ($isEmpty) {
                $this->db->query("ALTER TABLE log_email AUTO_INCREMENT = 1");
            }

            cache()->delete('statistik_log_email');

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
            $this->logEmailModel->truncate();

            cache()->delete('statistik_log_email');

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
            $path = WRITEPATH . 'cache/statistik_log_email';
            unlink($path);

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