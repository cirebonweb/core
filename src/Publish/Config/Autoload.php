<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * -------------------------------------------------------------------
 * AUTOLOADER CONFIGURATION
 * -------------------------------------------------------------------
 */
class Autoload extends AutoloadConfig
{
    /**
     * -------------------------------------------------------------------
     * Namespaces
     * -------------------------------------------------------------------
     * @var array<string, list<string>|string>
     */
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
    ];

    /**
     * -------------------------------------------------------------------
     * Class Map
     * -------------------------------------------------------------------
     * Prototype:
     *   $classmap = [
     *       'MyClass'   => '/path/to/class/file.php'
     *   ];
     *
     * @var array<string, string>
     */
    public $classmap = [];

    /**
     * -------------------------------------------------------------------
     * Files
     * -------------------------------------------------------------------
     * Prototype:
     *   $files = [
     *       '/path/to/my/file.php',
     *   ];
     *
     * @var list<string>
     */
    public $files = [];

    /**
     * -------------------------------------------------------------------
     * Helpers
     * -------------------------------------------------------------------
     * Helper milik core ditulis dengan namespace penuh karena helper() hanya
     * mencari nama polos di APPPATH/Helpers. Namespace `Cirebonweb\` sudah
     * dipetakan ke `src/` oleh composer (extra.ci4.resources + psr-4).
     *
     * @var list<string>
     */
    public $helpers = [
        'auth',
        'setting',
        'Cirebonweb\crb_asset',
        'Cirebonweb\crb_format',
        'Cirebonweb\crb_akses',
        'Cirebonweb\crb_menu',
    ];
}
