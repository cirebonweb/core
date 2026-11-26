<?php

namespace Cirebonweb\Config;

use CodeIgniter\Config\BaseService;
use Cirebonweb\Libraries\CacheLibrari;
use Cirebonweb\Libraries\DropdownLibrari;

class Services extends BaseService
{
    public static function cirebonweb($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('cirebonweb');
        }

        return new CrbRoutes();
    }

    public static function dropdown(bool $getShared = true): DropdownLibrari
    {
        if ($getShared) {
            return static::getSharedInstance('dropdown');
        }

        return new DropdownLibrari();
    }

    /**
     * Sumber durasi TTL cache milik core.
     *
     * Nama method TIDAK boleh `cache()` — CI4 sudah memakai `service('cache')`
     * untuk CacheInterface, dan menimpanya merusak `ResponseCache` dengan
     * TypeError. Namanya wajib berbeda.
     */
    public static function cacheTtl(bool $getShared = true): CacheLibrari
    {
        if ($getShared) {
            return static::getSharedInstance('cacheTtl');
        }

        return new CacheLibrari();
    }
}