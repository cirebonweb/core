<?php

namespace Cirebonweb\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Models\UserModel;
use Codeigniter\Shield\Models\GroupModel;
use Cirebonweb\Models\AuthLoginsModel;
use Cirebonweb\Models\LogEmailModel;

class Statistik extends Controller
{
    public function userList()
    {
        $cacheKey = 'statistik_user_list';
        $cached   = cache($cacheKey);
        if ($cached !== null) {
            return $this->response->setJSON($cached);
        }

        $userModel  = model(UserModel::class);
        $groupModel = model(GroupModel::class);

        $data = [
            'user_total'    => $userModel->countAll(),
            'user_aktif'    => $userModel->where('active', 1)->countAllResults(),
            'user_nonaktif' => $userModel->where('active', 0)->countAllResults(),
            // 'user_admin'    => $groupModel->where('group', 'admin')->countAllResults(),
            // 'user_klien'    => $groupModel->where('group', 'klien')->countAllResults()
        ];

        cache()->save($cacheKey, $data, service('cacheTtl')->ttl($cacheKey));
        return $this->response->setJSON($data);
    }

    public function userLogin()
    {
        $cacheKey = 'statistik_user_login';
        $cached   = cache($cacheKey);
        if ($cached !== null) {
            return $this->response->setJSON($cached);
        }

        helper('crb_device');
        $authLoginsModel = model(AuthLoginsModel::class);

        $loginTotal    = $authLoginsModel->countAllResults(); // Total semua login
        $loginBerhasil = $authLoginsModel->where('success', 1)->countAllResults();
        $loginGagal    = $authLoginsModel->where('success', 0)->countAllResults();

        // Perangkat dikelompokkan per string User-Agent unik lebih dulu, sehingga
        // yang diparsing hanya UA unik (biasanya puluhan) — bukan seluruh baris.
        $perangkatMap = [
            'Desktop' => 0,
            'Tablet'  => 0,
            'Mobile'  => 0,
            'Robot'   => 0,
        ];

        $uaRows = $authLoginsModel
            ->select('user_agent')
            ->select('COUNT(*) AS jumlah', false)
            ->groupBy('user_agent')
            ->findAll();

        foreach ($uaRows as $row) {
            $perangkat = getDeviceData((string) ($row->user_agent ?? ''))['device'];

            if (array_key_exists($perangkat, $perangkatMap)) {
                $perangkatMap[$perangkat] += (int) $row->jumlah;
            } else {
                $controllerName = get_class($this) . '::' . __FUNCTION__;
                log_message('warning', $controllerName . ': ' . $perangkat);
            }
        }

        $data = [
            'login_total'    => $loginTotal,
            'login_berhasil' => $loginBerhasil,
            'login_gagal'    => $loginGagal,
            'login_desktop'  => $perangkatMap['Desktop'],
            'login_tablet'   => $perangkatMap['Tablet'],
            'login_mobile'   => $perangkatMap['Mobile'],
            'login_robot'    => $perangkatMap['Robot'],
        ];

        cache()->save($cacheKey, $data, service('cacheTtl')->ttl($cacheKey));
        return $this->response->setJSON($data);
    }

    public function logEmail()
    {
        $cacheKey = 'statistik_log_email';
        $cached   = cache($cacheKey);
        if ($cached !== null) {
            return $this->response->setJSON($cached);
        }

        $logEmailModel = model(LogEmailModel::class);

        $data = [
            'log_total'    => $logEmailModel->countAll(),
            'log_berhasil' => $logEmailModel->where('status', 1)->countAllResults(),
            'log_gagal'    => $logEmailModel->where('status', 0)->countAllResults(),
            'log_avg'      => round($logEmailModel->selectAvg('render')->first()->render, 2)
        ];

        cache()->save($cacheKey, $data, service('cacheTtl')->ttl($cacheKey));
        return $this->response->setJSON($data);
    }
}