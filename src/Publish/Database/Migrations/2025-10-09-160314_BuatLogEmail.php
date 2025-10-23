<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BuatLogEmail extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'admin'    => ['type' => 'varchar', 'constraint' => 50],  // Nama user admin atau Sistem
            'tipe'     => ['type' => 'varchar', 'constraint' => 50],  // Jenis email: manual, otomatis, notifikasi
            'template' => ['type' => 'varchar', 'constraint' => 50],  // Nama template: standar, invoice
            'email'    => ['type' => 'varchar', 'constraint' => 100], // Alamat email penerima
            'judul'    => ['type' => 'varchar', 'constraint' => 255], // Judul email
            'render'   => ['type' => 'float', 'constraint' => '8,2'], // Waktu proses prmbuatan email (contoh = 1.58) detik
            'status'   => ['type' => 'tinyint', 'constraint' => 1],   // 0 = gagal, 1 = berhasil
            'error'    => ['type' => 'text', 'null' => true],         // Informasi error $e->getMessage()
            'dibuat'   => ['type' => 'datetime', 'null' => true]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('admin');
        $this->forge->createTable('log_email', true);
    }

    public function down()
    {
        $this->forge->dropTable('log_email');
    }
}
