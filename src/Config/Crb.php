<?php

namespace Cirebonweb\Config;

use CodeIgniter\Config\BaseConfig;

class Crb extends BaseConfig
{
    /**
     * Durasi cache (detik) untuk tiap key.
     *
     * Nilai runtime bisa diubah dari halaman `vendor/setting-cache` dan
     * disimpan di `writable/json/crb_cache.json` — file itu menang atas config.
     * Nama key harus sama dengan nama field di view setting-cache.
     */
    public array $cache = [
        'default' => 900,
        'items'   => [
            'cache_device'           => 900,
            'statistik_user_list'    => 3600,
            'statistik_user_login'   => 3600,
            'statistik_log_email'    => 3600,
        ],
    ];

    public array $crud = [
        'length'       => 25,
        'lengthMenu'   => [10, 25, 50, 100],
        'searchDelay'  => 400,
        'defaultOrder' => null,
    ];

    public function cacheTtl(string $key): int
    {
        return (int) ($this->cache['items'][$key] ?? $this->cache['default']);
    }
}