<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$crb = service('cirebonweb');

$routes->get('/', 'Home::index', ['filter' => 'crbAkses:home.dashboard']);

/**
 * Setiap grup route CORE memakai key modul yang sama dengan CrbAkses::$modules
 * dan key menu CORE terkait. Atur group pada app/Config/CrbAkses.php. Daftar
 * group kosong berarti modul publik.
 */
$crb->profil()->routes($routes, ['filter' => 'crbAkses:profil']);
$crb->userList()->routes($routes, ['filter' => 'crbAkses:vendor.user-list']);
$crb->userLogin()->routes($routes, ['filter' => 'crbAkses:vendor.user-login']);
$crb->statistik()->routes($routes, ['filter' => 'crbAkses:vendor.statistik']);
$crb->settingUmum()->routes($routes, ['filter' => 'crbAkses:vendor.setting-umum']);
$crb->settingCache()->routes($routes, ['filter' => 'crbAkses:vendor.setting-cache']);
$crb->settingOptimasi()->routes($routes, ['filter' => 'crbAkses:vendor.setting-optimasi']);
$crb->log()->routes($routes, ['filter' => 'crbAkses:vendor.log-email']);
$crb->dropdown()->routes($routes, ['filter' => 'crbAkses:dropdown']);

// Auth: login & magic link ditangani core, sisanya Shield.
service('auth')->routes($routes, ['except' => ['magic-link']]);
$crb->auth()->routes($routes);
