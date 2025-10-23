<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Database\Query;
use Config\Database;
use Cirebonweb\Libraries\AuthLoginsLastIdLibrari;
use Cirebonweb\Libraries\AuthRememberLastIdLibrari;
use Cirebonweb\Libraries\AuthSesiLibrari;

Events::on('DBQuery', static function (Query $query) {
    if (! $query->isWriteType()) {
        return;
    }

    $sql = strtolower((string) $query);
    $db = Database::connect();

    if (strpos($sql, 'insert into `auth_logins`') !== false) {
        AuthLoginsLastIdLibrari::setId($db->insertID());
    }

    if (strpos($sql, 'insert into `auth_remember_tokens`') !== false) {
        $lastRememberId = $db->insertID();
        AuthRememberLastIdLibrari::setId($lastRememberId);
        return;
    }

    if (strpos($sql, 'insert into `auth_sesi`') !== false) {
        $rememberId = AuthRememberLastIdLibrari::getId();
        (new AuthSesiLibrari())->insertAuthSesi($rememberId);
    }

    if (auth()->loggedIn()) {return;}
});
