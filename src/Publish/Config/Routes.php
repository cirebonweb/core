<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index', ['filter' => 'session']);
$routes->get('admin', 'Admin\Dashboard::index', ['filter' => 'group:admin']);

service('cirebonweb')->profil()->routes($routes, ['filter' => 'session']);
service('cirebonweb')->userList()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->userLogin()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->statistik()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->settingUmum()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->settingCache()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->settingOptimasi()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->log()->routes($routes, ['filter' => 'group:admin']);

service('auth')->routes($routes, ['except' => ['login', 'magic-link']]);
service('cirebonweb')->auth()->routes($routes);