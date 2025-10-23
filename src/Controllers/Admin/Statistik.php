<?php

namespace Cirebonweb\Controllers\Admin;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Models\UserModel;
use Codeigniter\Shield\Models\GroupModel;
use Cirebonweb\Models\Auth\AuthLoginsModel;
use Cirebonweb\Models\User\UserLoginModel;
use Cirebonweb\Models\Log\LogEmailModel;

helper('crb_cache');

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
            'user_admin'    => $groupModel->where('group', 'admin')->countAllResults(),
            'user_klien'    => $groupModel->where('group', 'klien')->countAllResults()
        ];

        cache()->save($cacheKey, $data, getDurasiCache($cacheKey));
        return $this->response->setJSON($data);
    }

    public function userLogin()
    {
        $cacheKey = 'statistik_user_login';
        $cached   = cache($cacheKey);
        if ($cached !== null) {
            return $this->response->setJSON($cached);
        }

        $authLoginsModel = model(AuthLoginsModel::class);
        $userLoginModel  = model(UserLoginModel::class);

        $loginTotal         = $authLoginsModel->countAllResults(); // Total semua login
        $loginBerhasil      = $authLoginsModel->where('success', 1)->countAllResults();
        $loginGagal         = $authLoginsModel->where('success', 0)->countAllResults();
        $loginSalahEmail    = $userLoginModel->where('tipe', 'Salah email')->countAllResults();
        $loginSalahPassword = $userLoginModel->where('tipe', 'Salah password')->countAllResults();
        $loginSalahLainnya  = $userLoginModel->whereNotIn('tipe', ['Salah email', 'Salah password'])->countAllResults();
        $perangkatCounts    = $userLoginModel->select('perangkat, COUNT(*) as jumlah')->groupBy('perangkat')->findAll();

        $perangkatMap = [
            'Desktop' => 0,
            'Tablet'  => 0,
            'Mobile'  => 0,
            'Robot'   => 0,
        ];

        foreach ($perangkatCounts as $row) {
            // log_message('debug', 'Perangkat ditemukan: ' . json_encode($row));
            $key = $row->perangkat;
            if (array_key_exists($key, $perangkatMap)) {
                $perangkatMap[$key] = (int) $row->jumlah;
            } else {
                $controllerName = get_class($this) . '::' . __FUNCTION__;
                log_message('warning', $controllerName . ': ' . $key);
            }
        }

        $data = [
            'login_total'          => $loginTotal,
            'login_berhasil'       => $loginBerhasil,
            'login_gagal'          => $loginGagal,
            'login_salah_email'    => $loginSalahEmail,
            'login_salah_password' => $loginSalahPassword,
            'login_salah_lainnya'  => $loginSalahLainnya,
            'login_desktop'        => $perangkatMap['Desktop'],
            'login_tablet'         => $perangkatMap['Tablet'],
            'login_mobile'         => $perangkatMap['Mobile'],
            'login_robot'          => $perangkatMap['Robot'],
        ];

        cache()->save($cacheKey, $data, getDurasiCache($cacheKey));
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

        cache()->save($cacheKey, $data, getDurasiCache($cacheKey));
        return $this->response->setJSON($data);
    }
}