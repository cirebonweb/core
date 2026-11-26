<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CrbMenu extends BaseConfig
{
    /**
     * Menu tambahan milik APP.
     *
     * @var list<array<string, mixed>>
     */
    public array $menuApp = [
        [
            'key'   => 'home.dashboard',
            'label' => 'Dashboard',
            'url'   => '/',
            'icon'  => 'bi bi-speedometer2',
        ],
    ];

    /**
     * Override tampilan menu bawaan CORE berdasarkan key.
     * Hanya label dan icon yang dapat diubah. Key dan URL tetap ditentukan
     * CORE; kebijakan akses diatur pada app/Config/CrbAkses.php.
     *
     * @var array<string, array{label?:string, icon?:string}>
     */
    public array $menuCore = [
        // 'vendor.user-list' => [
        //     'label'         => 'Manajemen Pengguna',
        //     'icon'          => 'bi bi-people',
        // ],
    ];
}
