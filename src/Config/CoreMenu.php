<?php

namespace Cirebonweb\Config;

use CodeIgniter\Config\BaseConfig;

class CoreMenu extends BaseConfig
{
    /**
     * Menu bawaan CORE. `key` dan `url` ditetapkan CORE dan tidak dapat
     * diganti melalui konfigurasi APP. Visibilitas menu mengikuti kebijakan
     * akses untuk key tersebut pada konfigurasi CrbAkses milik APP.
     *
     * @var list<array<string, mixed>>
     */
    public array $menus = [
        [
            'key'      => 'vendor.user',
            'label'    => 'Pengguna',
            'icon'     => 'bi bi-person-circle',
            'children' => [
                [
                    'key'   => 'vendor.user-list',
                    'label' => 'Daftar Pengguna',
                    'url'   => 'vendor/user-list',
                ],
                [
                    'key'   => 'vendor.user-login',
                    'label' => 'Riwayat Login',
                    'url'   => 'vendor/user-login',
                ],
            ],
        ],
        [
            'key'      => 'vendor.setting',
            'label'    => 'Pengaturan',
            'icon'     => 'bi bi-gear',
            'children' => [
                [
                    'key'   => 'vendor.setting-umum',
                    'label' => 'Umum',
                    'url'   => 'vendor/setting-umum',
                ],
                [
                    'key'   => 'vendor.setting-cache',
                    'label' => 'Cache',
                    'url'   => 'vendor/setting-cache',
                ],
                [
                    'key'   => 'vendor.setting-optimasi',
                    'label' => 'Optimasi',
                    'url'   => 'vendor/setting-optimasi',
                ],
            ],
        ],
        [
            'key'      => 'vendor.log',
            'label'    => 'Log',
            'icon'     => 'bi bi-journal-text',
            'children' => [
                [
                    'key'   => 'vendor.log-email',
                    'label' => 'Log Email',
                    'url'   => 'vendor/log-email',
                ],
            ],
        ],
    ];
}
