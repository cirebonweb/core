<?php

namespace Cirebonweb\Controllers\User;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseBuilder;
use Cirebonweb\Models\AuthLoginsModel;
use Cirebonweb\Controllers\Traits\CrudTrait;

class UserLogin extends BaseController
{
    use CrudTrait;
    protected UserProfilModel $userProfilModel;
    protected $searchable = ['a.success', 'a.identifier', 'c.username'];
    protected $orderable  = [
        'a.id',
        'a.success',
        'a.identifier',
        'a.date',
    ];
    protected $modelClass = AuthLoginsModel::class;

    /**
     * @var \Cirebonweb\Models\AuthLoginsModel $authLoginsModel
     */
    protected $authLoginsModel;

    protected function defaultOrder(): array
    {
        return ['a.id' => 'desc'];
    }

    public function __construct()
    {
        $this->authLoginsModel = new AuthLoginsModel();
    }

    public function index()
    {
        $data = [
            'navigasi' => '<a href="/vendor/user-list">User List</a> &nbsp;',
        ];
        return view('Cirebonweb\user\user_login', $data);
    }

    protected function filterTabel(BaseBuilder $builder): BaseBuilder
    {
        $filterStatus = $this->request->getPost('filter_status');
        if ($filterStatus !== null && $filterStatus !== '') {
            $builder->where('a.success', $filterStatus);
        }

        return $builder;
    }

    protected function dataTabel(\stdClass $row): array
    {
        helper('crb_device');

        $device = getDeviceData((string) ($row->user_agent ?? ''));

        return [
            '<input type="checkbox" class="checkItem" value="' . $row->auth_id . '">',
            $row->auth_id,
            $row->success ? '✅ Berhasil' : '❌ Gagal',
            ($row->username ?? 'null') . '<br>' . $row->identifier,
            $row->ip_address,
            $device['device'],
            $device['browser'],
            $device['os'],
            date('d-m-Y H:i:s', strtotime($row->date)),
        ];
    }

    // public function tabel()
    // {
    //     helper('crb_device');

    //     $builder = $this->authLoginsModel->tabel();

    //     // Filter tambahan dari AJAX
    //     $filterStatus = $this->request->getPost('filter_status');
    //     if ($filterStatus !== null && $filterStatus !== '') {
    //         $builder->where('a.success', $filterStatus);
    //     }

    //     $dataTable = new TabelLibrari($builder, $this->request);
    //     $dataTable->setSearchable(['a.identifier', 'c.username', 'a.ip_address']);
    //     $dataTable->setOrderable([
    //         1 => 'a.id',
    //         2 => 'a.success',
    //         3 => 'c.username',
    //         4 => 'a.ip_address',
    //         8 => 'a.date',
    //     ]);
    //     $dataTable->setDefaultOrder(['a.id' => 'desc']);

    //     // Parsing User-Agent hanya untuk baris pada halaman ini (setelah LIMIT),
    //     // jadi biayanya sebesar panjang halaman, bukan seluruh tabel.
    //     $dataTable->setRowCallback(function ($row) {
    //         $device = getDeviceData((string) ($row->user_agent ?? ''));

    //         return [
    //             '<input type="checkbox" class="checkItem" value="' . $row->auth_id . '">',
    //             $row->auth_id,
    //             $row->success ? '✅ Berhasil' : '❌ Gagal',
    //             ($row->username ?? 'null') . '<br>' . $row->identifier,
    //             $row->ip_address,
    //             $device['device'],
    //             $device['browser'],
    //             $device['os'],
    //             date('d-m-Y H:i:s', strtotime($row->date)),
    //         ];
    //     });

    //     return $this->response->setJSON($dataTable->getResult());
    // }

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
