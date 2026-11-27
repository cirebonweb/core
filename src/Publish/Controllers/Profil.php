<?php

namespace App\Controllers;

use Cirebonweb\Controllers\Profil as ProfilCore;

/**
 * Override halaman Profil milik CORE.
 *
 * Untuk memakai class ini, ubah pendaftaran grup profil di
 * app/Config/Routes.php agar memakai namespace APP:
 *
 *     $crb->profil()->routes($routes, [
 *         'namespace' => 'App\\Controllers',
 *         'filter'    => 'crbAkses:profil',
 *     ]);
 *
 * Jangan daftarkan grup profil CORE dua kali. Dengan namespace APP, seluruh
 * endpoint profil menggunakan class ini dan mewarisi implementasi CORE.
 *
 * Semua method aksi ProfilCore bersifat `public` (syarat CI4 untuk target route)
 * sehingga aman di-override dengan visibilitas yang sama. Panggil parent::*
 * untuk memakai perilaku core, atau tulis ulang bila perlu.
 */
class Profil extends ProfilCore
{
    /**
     * Hook: data tambahan untuk halaman profil.
     *
     * Tambahkan variabel apa pun ke view tanpa menyentuh controller core.
     * Variabel ini dipakai di app/Views/profil.php, mis. $kota.
     *
     * @param object   $user   Baris hasil UserModel::getProfil()
     * @param int|null $userId Id user yang sedang dilihat
     */
    protected function profilData($user, $userId): array
    {
        return [
            // 'kota'  => $this->tabelLain()->getKota($user->id_profil),
            // 'judul' => 'Profil ' . ($user->username ?? ''),
        ];
    }

    /**
     * Hook: view yang dipakai halaman profil.
     * Return nama view lain bila ingin memakai halaman profil versi project.
     */
    protected function profilView(): string
    {
        return 'profil';
    }

    /**
     * Hook: aturan validasi tambahan untuk tab Info.
     *
     * Field yang divalidasi di sini otomatis ikut terambil dari POST dan
     * diteruskan ke UserProfilModel::update(). Pastikan kolomnya ada di tabel
     * user_profil dan terdaftar pada $allowedFields model APP.
     */
    protected function infoValidationRules(): array
    {
        return [
            // 'kota' => ['label' => 'Kota', 'rules' => 'permit_empty|max_length[50]'],
        ];
    }

    /**
     * Contoh override method core: bungkus perilaku bawaan.
     */
    public function updateInfo()
    {
        // Contoh validasi tambahan khusus project sebelum proses core berjalan.
        // $kota = $this->request->getPost('kota');
        // if ($kota !== null && mb_strlen(trim($kota)) > 50) {
        //     return $this->response->setJSON([
        //         'success'  => false,
        //         'messages' => ['kota' => 'Kota maksimal 50 karakter.'],
        //     ]);
        // }

        return parent::updateInfo();
    }
}
