<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CrbAkses extends BaseConfig
{
    /**
     * Kebijakan akses per modul.
     *
     * [] berarti publik; 'session' berarti semua user yang login; daftar group
     * berarti hanya anggota group tersebut. Semua key modul route dan menu
     * yang diperiksa harus memiliki entri pada daftar ini.
     * 'home.dashboard' => [] → dapat diakses publik tanpa login
     * 'home.dashboard' => ['admin', 'developer'] → hanya dapat diakses role admin dan developer
     * 'home.dashboard' => 'session' → dapat diakses semua role dan wajib login
     * @var array<string, list<string>|'session'>
     */
    public array $modules = [
        'home.dashboard'          => 'session',
        'profil'                  => 'session',
        'dropdown'                => 'session',
        'vendor.user-list'        => ['superadmin', 'developer'],
        'vendor.user-login'       => ['superadmin', 'admin', 'developer'],
        'vendor.statistik'        => 'session',
        'vendor.setting-umum'     => 'session',
        'vendor.setting-cache'    => 'session',
        'vendor.setting-optimasi' => ['superadmin', 'admin', 'developer'],
        'vendor.log-email'        => 'session',
    ];
}
