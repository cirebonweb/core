<?php

namespace Cirebonweb\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * Builder route milik core.
 *
 * Konvensi URL: semua halaman core berada di bawah `vendor/` dengan path DATAR
 * (tidak bersarang), supaya lebih ringkas dan mudah dibaca:
 *
 *   vendor/user-list        vendor/setting-umum
 *   vendor/user-login       vendor/setting-cache
 *   vendor/log-email        vendor/setting-optimasi
 *
 * Endpoint AJAX memakai bentuk `<halaman>/<aksi>`, contoh
 * `vendor/setting-umum/simpan-situs` atau `vendor/user-list/tabel`.
 *
 * Halaman milik project TIDAK memakai prefiks `vendor/`, sehingga dari URL
 * saja sudah jelas mana core mana project.
 *
 * Contoh pendaftaran route dan filter APP ada di `src/Publish/Config/Routes.php`.
 */
class CrbRoutes
{
    protected string $namespace = 'Cirebonweb\Controllers';

    /**
     * Prefiks URL untuk seluruh halaman bawaan core.
     */
    public const VENDOR = 'vendor';

    protected array $routes = [];
    protected string $group = '';

    /**
     * Builder utama, mengisi daftar route secara singkat.
     */
    protected function set(string $group, array $routes): self
    {
        $this->group = $group;
        $this->routes = $routes;
        return $this;
    }

    public function dropdown(): self
    {
        return $this->set('dropdown', [
            ['get', 'dropdown/(:segment)', 'Dropdown::getList/$1']
        ]);
    }

    public function profil(): self
    {
        return $this->set('profil', [
            ['get',  'profil', 'Profil::index'],
            ['post', 'profil/update-foto', 'Profil::updateFoto'],
            ['post', 'profil/update-akun', 'Profil::updateAkun'],
            ['post', 'profil/update-info', 'Profil::updateInfo'],
            ['get',  'profil/get-perangkat', 'Profil::getPerangkat'],
            ['post', 'profil/get-perangkat', 'Profil::getPerangkat'],
            ['post', 'profil/logout-perangkat', 'Profil::logoutPerangkat']
        ]);
    }

    public function statistik(): self
    {
        $v = self::VENDOR;

        return $this->set('statistik', [
            ['get', $v . '/statistik/user-list', 'Statistik::userList'],
            ['get', $v . '/statistik/user-login', 'Statistik::userLogin'],
            ['get', $v . '/statistik/log-email', 'Statistik::logEmail']
        ]);
    }

    public function userList(): self
    {
        $u = self::VENDOR . '/user-list';

        return $this->set('userList', [
            ['get',  $u, 'User\UserList::index'],
            ['post', $u . '/tabel', 'User\UserList::tabel'],
            ['get', $u . '/tabel', 'User\UserList::tabel'],
            ['post', $u . '/get-id', 'User\UserList::getId'],
            ['post', $u . '/simpan', 'User\UserList::simpan'],
            ['post', $u . '/update', 'User\UserList::update'],
            ['post', $u . '/update-grup', 'User\UserList::updateGrup'],
            ['post', $u . '/update-status', 'User\UserList::updateStatus'],
        ]);
    }

    public function userLogin(): self
    {
        $u = self::VENDOR . '/user-login';

        return $this->set('userLogin', [
            ['get',  $u, 'User\UserLogin::index'],
            ['post', $u . '/tabel', 'User\UserLogin::tabel'],
            ['post', $u . '/hapus', 'User\UserLogin::hapus'],
            ['post', $u . '/reset', 'User\UserLogin::reset'],
            ['post', $u . '/refresh', 'User\UserLogin::refresh'],
        ]);
    }

    public function settingUmum(): self
    {
        $u = self::VENDOR . '/setting-umum';

        return $this->set('settingUmum', [
            ['get',  self::VENDOR . '/setting', 'Setting\Umum::index'],
            ['get',  $u, 'Setting\Umum::index'],
            ['post', $u . '/simpan-sistem', 'Setting\Umum::simpanSistem'],
            ['post', $u . '/simpan-situs', 'Setting\Umum::simpanSitus'],
            ['post', $u . '/simpan-smtp', 'Setting\Umum::simpanSmtp'],
            ['post', $u . '/tes-smtp', 'Setting\Umum::tesSmtp'],
            ['post', $u . '/simpan-logo-warna', 'Setting\UmumLogo::simpanLogoWarna'],
            ['post', $u . '/simpan-logo-putih', 'Setting\UmumLogo::simpanLogoPutih'],
            ['post', $u . '/simpan-logo-ikon', 'Setting\UmumLogo::simpanLogoIkon'],
            ['post', $u . '/simpan-logo-ikon32', 'Setting\UmumLogo::simpanLogoIkon32'],
            ['post', $u . '/simpan-logo-ikon180', 'Setting\UmumLogo::simpanLogoIkon180'],
            ['post', $u . '/simpan-logo-ikon192', 'Setting\UmumLogo::simpanLogoIkon192'],
        ]);
    }

    public function settingCache(): self
    {
        $u = self::VENDOR . '/setting-cache';

        return $this->set('settingCache', [
            ['get',  $u, 'Setting\Cache::index'],
            ['post', $u . '/tabel', 'Setting\Cache::tabel'],
            ['post', $u . '/simpan', 'Setting\Cache::simpan'],
            ['post', $u . '/hapus', 'Setting\Cache::hapus'],
            ['post', $u . '/hapus-expired', 'Setting\Cache::hapusExpired'],
        ]);
    }

    public function settingOptimasi(): self
    {
        $u = self::VENDOR . '/setting-optimasi';

        return $this->set('settingOptimasi', [
            ['get',  $u, 'Setting\Optimasi::index'],
            ['get',  $u . '/get-info', 'Setting\Optimasi::getInfo'],
            ['post', $u . '/hapus-log', 'Setting\Optimasi::hapusLog'],
            ['post', $u . '/hapus-debug', 'Setting\Optimasi::hapusDebug'],
            ['post', $u . '/db-optimasi', 'Setting\Optimasi::dbOptimasi'],
            ['post', $u . '/db-tabel', 'Setting\Optimasi::dbTabel'],
            ['post', $u . '/db-analisis', 'Setting\Optimasi::dbAnalisis'],
            ['post', $u . '/db-refresh', 'Setting\Optimasi::dbRefresh'],
        ]);
    }

    public function log(): self
    {
        $u = self::VENDOR . '/log-email';

        return $this->set('log', [
            ['get',  self::VENDOR . '/log', 'Log\Email::index'],
            ['get',  $u, 'Log\Email::index'],
            ['post', $u . '/tabel', 'Log\Email::tabel'],
            ['post', $u . '/hapus', 'Log\Email::hapus'],
            ['post', $u . '/reset', 'Log\Email::reset'],
            ['post', $u . '/refresh', 'Log\Email::refresh'],
        ]);
    }

    public function auth(): self
    {
        return $this->set('auth', [
            ['get',  'magic-link', 'Auth\MagicLink::loginView'],
            ['post', 'magic-link', 'Auth\MagicLink::loginAction'],
            ['get',  'verify-magic-link', 'Auth\MagicLink::verify']
        ]);
    }

    /**
     * Registrasi semua route ke sistem CI4.
     */
    public function routes(RouteCollection $routes, array $options = []): void
    {
        $options = array_merge(['namespace' => $this->namespace], $options);

        $routes->group('', $options, function ($routes) {
            foreach ($this->routes as $route) {
                $routes->{$route[0]}($route[1], $route[2]);
            }
        });
    }
}
