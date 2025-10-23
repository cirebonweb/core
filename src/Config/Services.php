<?php

namespace Cirebonweb\Config;

use CodeIgniter\Config\BaseService;
use Cirebonweb\Config\CrbRoutes;

class Services extends BaseService
{
    public static function cirebonweb($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('cirebonweb');
        }

        return new CrbRoutes();
    }
}
