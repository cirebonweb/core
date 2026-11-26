<?php

namespace Cirebonweb\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Buang tabel `user_login` beserta seluruh ketergantungannya.
 *
 * Sejak perubahan ini, riwayat login dibaca langsung dari tabel bawaan Shield
 * `auth_logins` (`ip_address`, `user_agent`, `date`, `success`, `identifier`,
 * `user_id`), sedangkan kolom perangkat / OS / browser diturunkan SAAT PEMBACAAN
 * dengan mem-parse `auth_logins.user_agent` — lihat helper `crb_device_helper`.
 *
 * Tidak ada tulisan data saat login, jadi tabel `user_login` tidak diperlukan
 * lagi. Project yang tabelnya sudah ada perlu menjalankan migration ini;
 * project baru otomatis bersih karena `BuatUserLogin` sudah tidak diterbitkan.
 */
class HapusTabelUserLogin extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('user_login', true);
        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        // Tidak bisa dikembalikan otomatis: definisi kolom geoip sudah dibuang
        // dari `BuatUserLogin` dan file migration lama sudah dihapus dari paket.
        // Kembalikan manual lewat SQL bila benar-benar diperlukan.
    }
}
