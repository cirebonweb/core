<?php

namespace Cirebonweb\Config;

use CodeIgniter\Config\BaseConfig;

class Dropdown extends BaseConfig
{
    /**
     * Registry sumber data dropdown.
     *
     * Format tiap item:
     *   'produk' => [
     *       'model'    => \App\Models\ProdukModel::class,
     *       'value'    => 'id',
     *       'label'    => 'nama',
     *       'where'    => ['status' => 'active'],
     *       'orderBy'  => null,
     *       'limit'    => null,
     *       'callback' => null,
     *   ],
     */
    public array $items = [];
}