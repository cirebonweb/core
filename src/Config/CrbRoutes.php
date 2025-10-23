<?php

namespace Cirebonweb\Config;

use CodeIgniter\Router\RouteCollection;

class CrbRoutes
{
    protected string $namespace = 'Cirebonweb\Controllers';
    protected array $routes = [];

    /**
     * Builder utama, mengisi daftar route secara singkat.
     */
    protected function set(string $group, array $routes): self
    {
        $this->routes = $routes;
        return $this;
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
        return $this->set('statistik', [
            ['get', 'admin/statistik/user-list', 'Admin\Statistik::userList'],
            ['get', 'admin/statistik/user-login', 'Admin\Statistik::userLogin'],
            ['get', 'admin/statistik/log-email', 'Admin\Statistik::logEmail']
        ]);
    }

    public function userList(): self
    {
        return $this->set('userList', [
            ['get',  'admin/user/user-list', 'Admin\User\UserList::index'],
            ['post', 'admin/user/user-list/tabel', 'Admin\User\UserList::tabel'],
            ['post', 'admin/user/user-list/get-id', 'Admin\User\UserList::getId'],
            ['post', 'admin/user/user-list/simpan', 'Admin\User\UserList::simpan'],
            ['post', 'admin/user/user-list/update', 'Admin\User\UserList::update'],
            ['post', 'admin/user/user-list/update-grup', 'Admin\User\UserList::updateGrup'],
            ['post', 'admin/user/user-list/update-aktif', 'Admin\User\UserList::updateAktif']
        ]);
    }

    public function userLogin(): self
    {
        return $this->set('userLogin', [
            ['get',  'admin/user/user-login', 'Admin\User\UserLogin::index'],
            ['post', 'admin/user/user-login/tabel', 'Admin\User\UserLogin::tabel'],
            ['post', 'admin/user/user-login/hapus', 'Admin\User\UserLogin::hapus'],
            ['post', 'admin/user/user-login/reset', 'Admin\User\UserLogin::reset'],
            ['post', 'admin/user/user-login/refresh', 'Admin\User\UserLogin::refresh']
        ]);
    }

    public function settingUmum(): self
    {
        return $this->set('settingUmum', [
            ['get',  'admin/setting', 'Admin\Setting\Umum::index'],
            ['get',  'admin/setting/umum', 'Admin\Setting\Umum::index'],
            ['post', 'admin/setting/umum/simpan-sistem', 'Admin\Setting\Umum::simpanSistem'],
            ['post', 'admin/setting/umum/simpan-situs', 'Admin\Setting\Umum::simpanSitus'],
            ['post', 'admin/setting/umum/simpan-smtp', 'Admin\Setting\Umum::simpanSmtp'],
            ['post', 'admin/setting/umum/tes-smtp', 'Admin\Setting\Umum::tesSmtp'],
            ['post', 'admin/setting/umum/simpan-recaptcha', 'Admin\Setting\Umum::simpanRecaptcha'],
            ['post', 'admin/setting/umum/simpan-logo-warna', 'Admin\Setting\UmumLogo::simpanLogoWarna'],
            ['post', 'admin/setting/umum/simpan-logo-putih', 'Admin\Setting\UmumLogo::simpanLogoPutih'],
            ['post', 'admin/setting/umum/simpan-logo-ikon', 'Admin\Setting\UmumLogo::simpanLogoIkon'],
            ['post', 'admin/setting/umum/simpan-logo-ikon32', 'Admin\Setting\UmumLogo::simpanLogoIkon32'],
            ['post', 'admin/setting/umum/simpan-logo-ikon180', 'Admin\Setting\UmumLogo::simpanLogoIkon180'],
            ['post', 'admin/setting/umum/simpan-logo-ikon192', 'Admin\Setting\UmumLogo::simpanLogoIkon192']
        ]);
    }

    public function settingCache(): self
    {
        return $this->set('settingCache', [
            ['get',  'admin/setting/cache', 'Admin\Setting\Cache::index'],
            ['post', 'admin/setting/cache/tabel', 'Admin\Setting\Cache::tabel'],
            ['post', 'admin/setting/cache/simpan', 'Admin\Setting\Cache::simpan'],
            ['post', 'admin/setting/cache/hapus', 'Admin\Setting\Cache::hapus'],
            ['post', 'admin/setting/cache/hapus-expired', 'Admin\Setting\Cache::hapusExpired']
        ]);
    }

    public function settingOptimasi(): self
    {
        return $this->set('settingOptimasi', [
            ['get',  'admin/setting/optimasi', 'Admin\Setting\Optimasi::index'],
            ['get', 'admin/setting/optimasi/get-info', 'Admin\Setting\Optimasi::getInfo'],
            ['post', 'admin/setting/optimasi/hapus-log', 'Admin\Setting\Optimasi::hapusLog'],
            ['post', 'admin/setting/optimasi/hapus-debug', 'Admin\Setting\Optimasi::hapusDebug'],
            ['post', 'admin/setting/optimasi/db-optimasi', 'Admin\Setting\Optimasi::dbOptimasi'],
            ['post', 'admin/setting/optimasi/db-tabel', 'Admin\Setting\Optimasi::dbTabel'],
            ['post', 'admin/setting/optimasi/db-analisis', 'Admin\Setting\Optimasi::dbAnalisis'],
            ['post', 'admin/setting/optimasi/db-refresh', 'Admin\Setting\Optimasi::dbRefresh']
        ]);
    }

    public function log(): self
    {
        return $this->set('log', [
            ['get',  'admin/log', 'Admin\Log\Email::index'],
            ['get',  'admin/log/email', 'Admin\Log\Email::index'],
            ['post', 'admin/log/email/tabel', 'Admin\Log\Email::tabel'],
            ['post', 'admin/log/email/hapus', 'Admin\Log\Email::hapus'],
            ['post', 'admin/log/email/reset', 'Admin\Log\Email::reset'],
            ['post', 'admin/log/email/refresh', 'Admin\Log\Email::refresh'],
        ]);
    }

    public function auth(): self
    {
        return $this->set('auth', [
            ['get',  'login', 'Auth\Login::loginView', ['as' => 'login']],
            ['post', 'login', 'Auth\Login::loginAction'],
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
            foreach ($this->routes as [$method, $uri, $handler]) {
                $routes->{$method}($uri, $handler);
            }
        });
    }
}
