<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Database\Query;
use Config\Database;
use Cirebonweb\Libraries\AuthLoginsLastIdLibrari;
use Cirebonweb\Libraries\AuthRememberLastIdLibrari;
use Cirebonweb\Libraries\AuthSesiLibrari;

Events::on('DBQuery', static function (Query $query): void {
    if (! $query->isWriteType()) {
        return;
    }

    $sql = str_replace(['`', '"'], '', $query->getOriginalQuery());

    if (stripos($sql, 'auth_') === false) {
        return;
    }

    if (preg_match('/\binsert\s+into\s+([\w$.]+)/i', $sql, $cocok) !== 1) {
        return;
    }

    $db = Database::connect();
    $namaTabel = $cocok[1];
    $pos       = strrpos($namaTabel, '.');
    $tabel     = strtolower($pos === false ? $namaTabel : substr($namaTabel, $pos + 1));
    $prefix    = strtolower($db->DBPrefix);

    if ($prefix !== '' && str_starts_with($tabel, $prefix)) {
        $tabel = substr($tabel, strlen($prefix));
    }

    if ($tabel === 'auth_logins') {
        AuthLoginsLastIdLibrari::setId($db->insertID());
        return;
    }

    if ($tabel === 'auth_remember_tokens') {
        AuthRememberLastIdLibrari::setId($db->insertID());
        return;
    }

    if ($tabel === 'auth_sesi') {
        (new AuthSesiLibrari())->insertAuthSesi(AuthRememberLastIdLibrari::getId());
    }
});