# Daftar Kerja Perombakan CORE

Dokumen ini adalah checklist status implementasi dan pengujian `cirebonweb-core`.
Perbarui setiap kali pekerjaan atau pengujian selesai agar sesi baru dapat
melanjutkan pekerjaan tanpa mengandalkan riwayat percakapan.

**Pembaruan terakhir:** [log-ai.md](log-ai.md)

## Arti status

- `[ ]` Null: Belum dikerjakan, belum dibuat, atau belum diuji.
- `[p]` Progress: Sedang dikerjakan.
- `[r]` Review: Implementasi selesai dan siap ditinjau, tetapi uji coba APP/manual belum dinyatakan sukses.
- `[x]` eXit: Implementasi final; uji yang sesuai sudah berhasil tanpa bug.

Jangan ubah item menjadi `[x]` hanya karena file sudah ada atau lolos lint.
Catat pengujian yang berhasil di samping item. Uji integrasi/manual APP hanya
boleh ditandai selesai setelah hasilnya dikonfirmasi.

## Aturan melanjutkan pekerjaan

1. Mulai sesi baru dengan membaca dokumen ini dan bagian modul terkait di
   [`Konsep.md`](Konsep.md).
2. Baca dokumentasi modul yang ditautkan sebelum mengubah implementasi.
3. Jangan mengubah konsep final tanpa instruksi; dokumentasikan pertanyaan
   konsep secara terpisah dan tunggu keputusan.
4. Sebelum mulai, tandai pekerjaan aktif `[p]`. Setelah implementasi selesai,
   gunakan `[r]` sampai uji yang sesuai benar-benar sukses.
5. Setelah setiap sesi, perbarui status, bukti uji, dan catatan tindak lanjut.

## Modul hak akses, user, dan menu

Dokumentasi: [`modul-hak-akses.md`](modul-hak-akses.md)

### Implementasi

- [r] `src/Config/CrbRoutes.php` — builder route CORE; filter APP diteruskan melalui opsi pendaftaran.
- [r] `src/Config/CoreMenu.php` — konfigurasi menu bawaan CORE.
- [r] `src/Filters/CrbAkses.php` — filter route dengan alias `crbAkses`.
- [r] `src/Helpers/crb_akses_helper.php` — pemeriksaan akses publik, session Shield, atau group berdasarkan `CrbAkses::$modules`.
- [r] `src/Helpers/crb_menu_helper.php` — resolusi menu dan helper `aksesMenu()` yang memakai key kebijakan modul yang sama.
- [r] `src/Publish/Config/CrbAkses.php` — konfigurasi policy group per modul untuk APP.
- [r] `src/Publish/Config/CrbMenu.php` — menu APP dan override label/ikon menu CORE untuk APP.
- [r] `src/Publish/Config/Filters.php` — alias filter `crbAkses`.
- [r] `src/Publish/Config/Autoload.php` — autoload helper akses dan menu.
- [r] `src/Publish/Config/Routes.php` — contoh route APP dan route CORE dengan filter `crbAkses`.
- [r] `src/Publish/Views/layout/sidebar_menu.php` — sidebar APP memakai `aksesMenu()`.
- [r] `src/Views/layout/sidebar_vendor.php` — sidebar menu CORE memakai `aksesMenu()`.
- [r] `src/Publish/Views/layout/template.php` — judul halaman dicari dari URL `CrbMenu`; `pageTitle` menjadi fallback untuk halaman yang belum dipetakan.
- [r] `CrbMenu.url(key, endpoint)` di layout menyediakan URL item menu untuk endpoint AJAX.
- [r] `src/Views/user/user_list.js` — endpoint CRUD/tabel memakai URL dasar `vendor.user-list` dari `CrbMenu`.
- [r] `src/Dokumen/Konsep.md` dan `src/Dokumen/modul-hak-akses.md` — konsep dan panduan penerapan menggunakan nama konfigurasi/filter terkini.
- [r] Uji integrasi publish ke APP baru: konfigurasi `CrbAkses.php`, `CrbMenu.php`, helper, filter, dan sidebar tersedia serta berjalan. (Catatan: 2026-11-25, `src/Publish/Views/layout/template.php` disalin manual ke APP; artefak publish lain belum dikonfirmasi tersinkron penuh.)
- [r] Uji akses anonim ke route yang policy group-nya `[]`.
- [r] Uji kebijakan `'session'`: user anonim diarahkan ke login Shield dan semua group yang sudah login mendapat akses.
- [r] Uji route Dashboard yang hanya boleh diakses group `admin` dan `developer`; pastikan akses langsung URL ditolak untuk group lain.
- [r] Uji akses `/vendor/user-list` sesuai group yang diatur di `CrbAkses::$modules`.
- [r] Uji seluruh endpoint CRUD/AJAX `/vendor/user-list` terhadap group yang diizinkan dan ditolak.
- [r] Uji `aksesMenu()` untuk menu APP dan menu CORE: menu terlihat hanya untuk group sesuai policy yang sama dengan filter route. (Parsial APP 2026-11-25: policy `vendor.user-login => ['user']` menyembunyikan item menu CORE untuk user tidak berhak.)
- [r] Uji override `CrbMenu::$menuCore`: label dan icon dapat berubah, sedangkan key dan URL tetap.
- [r] Uji judul/tab halaman otomatis mengikuti label menu dan fallback judul halaman yang belum memiliki item menu.
- [r] Uji builder URL `CrbMenu.url(key, endpoint)` dengan URL dasar menu APP, menu CORE, dan endpoint AJAX.
- [r] Uji perubahan group pada User List: `superadmin` memilih group dari `AuthGroups`; `admin`/`developer` hanya dapat memberi `user` dan tidak dapat mengubah akun yang sudah istimewa.
- [x] Keputusan: tidak ada modul `vendor/user-role`. `app/Config/AuthGroups.php` dan `app/Config/CrbAkses.php` tetap dikelola manual. CORE hanya menampilkan `title` dari `AuthGroups` di User List; key tetap dipakai untuk fungsi/policy/penyimpanan (konsep: [modul-hak-akses.md](modul-hak-akses.md#10-keputusan-label-role-dari-authgroups-manual)).

Catatan verifikasi yang sudah dilakukan oleh Copilot: PHP lint pada file
implementasi terkait, pemeriksaan sintaks JavaScript, serta uji terisolasi
helper untuk kebijakan publik, session, group, resolusi label menu, dan peta URL
berhasil. Uji tersebut belum menggantikan uji filter, integrasi pada APP, atau
uji coba manual.

Catatan verifikasi manual APP (2026-11-25): policy `vendor.user-login` diubah
menjadi `['user']`; item menu terkait tidak tampil untuk user yang tidak berhak
dan akses langsung ke `http://localhost:8080/vendor/user-login` mengembalikan
403 Forbidden. Perilaku ini sesuai konsep, tetapi belum mencakup seluruh butir
uji integrasi, endpoint AJAX lain, atau variasi policy pada modul lain.
Temuan APP (2026-11-25): fungsi hak akses (group) pada `/vendor/user-list`
awalnya belum tersedia, kemudian diimplementasikan di CORE. Uji APP melaporkan
(1) tambah user baru memunculkan "Peringatan ID user tidak ditemukan" dan (2)
edit hak akses hanya dapat memilih satu role meskipun data memiliki beberapa
group (contoh `admin, superadmin`). Perbaikan alur tambah/edit dan dukungan
multi-group telah diterapkan di CORE; perlu uji ulang APP setelah sinkronisasi
file ke APP.

## Modul autentikasi dan profil

Dokumentasi konsep modul: [`modul-profil.md`](modul-profil.md) (batas CORE/APP dan override).

- [r] `src/Controllers/Auth/MagicLink.php` — implementasi magic link tersedia.
- [r] `src/Controllers/Profil.php` — implementasi profil tersedia.
- [r] `src/Controllers/Profil.php` — hook APP (`profilData()`, `profilView()`,
  `infoValidationRules()`) tersedia; `index()` aman terhadap `?id=` yang gagal
  didekripsi dan data profil yang tidak ditemukan.
- [r] `src/Controllers/Profil.php` — `updateInfo()` memakai
  `UserProfilModel::getValidationRules()` (sebelumnya memanggil properti
  `validationRules()` sehingga selalu error).
- [r] Otorisasi profil di server: pemilik dapat mengubah profil sendiri;
  pengguna lain harus lolos policy `vendor.user-list`; endpoint perubahan
  memeriksa kecocokan `user_id` dan `profil_id`.
- [r] `src/Config/CrbRoutes.php` — opsi `namespace` grup route terdokumentasi
  sebagai mekanisme override controller; `daftarRute()` tersedia untuk inspeksi
  daftar route modul.
- [r] `src/Publish/Controllers/Profil.php` — contoh `App\Controllers\Profil`
  (`extends Cirebonweb\Controllers\Profil`) untuk publish ke APP.
- [r] `src/Libraries/Auth*Librari.php` dan `src/Models/Auth*Model.php` —
  dukungan sesi/perangkat autentikasi tersedia.
- [r] `src/Publish/Config/Auth.php` — konfigurasi view Shield milik CORE.
- [ ] Uji login/magic link, verifikasi email, profil, perubahan foto/informasi,
  logout perangkat, dan sesi pada APP.
- [ ] Uji akses profil pada APP bersih: pemilik dapat melihat/mengubah profil
  sendiri; user di luar policy `vendor.user-list` ditolak untuk profil lain;
  group yang lolos policy dapat melihat/mengubahnya; validasi juga berlaku
  pada endpoint update dengan pasangan ID profil/user yang tidak cocok.
- [ ] Uji override profil pada APP bersih: `namespace` grup route diarahkan ke
  APP, hook `profilData()` menambah variabel view, dan `infoValidationRules()`
  menambah kolom tab Info.
- [ ] Susun dokumentasi modul setelah perilaku fitur terverifikasi.

## Modul administrasi pengguna

Dokumentasi konsep modul: belum disusun.

- [r] `src/Controllers/User/UserList.php` dan `src/Controllers/User/UserLogin.php` — implementasi fitur tersedia.
- [r] `src/Controllers/Statistik.php` — endpoint statistik tersedia.
- [r] `src/Models/UserModel.php` dan `src/Models/UserProfilModel.php` — implementasi model tersedia.
- [r] `src/Views/user/user_list.php` dan `src/Views/user/user_list.js` — pengelolaan hak akses (group) pada modal User List; pilihan dan validasi dibatasi sesuai aktor/target di server. Mendukung user dengan beberapa group (multi-select untuk aktor yang berwenang), dan alur tambah/edit diperbaiki; perlu uji ulang APP.
- [r] Uji daftar, pencarian, tambah/perubahan akun, status aktif, pengaturan group, serta riwayat login pada APP.
- [r] Uji validasi server-side perubahan group dengan semua jenis group.
- [ ] Susun dokumentasi modul setelah uji manual.

## Modul pengaturan CORE

Dokumentasi konsep modul: belum disusun.

- [r] `src/Controllers/Setting/Umum.php` dan `UmumLogo.php` — pengaturan umum, SMTP, dan logo tersedia.
- [r] `src/Controllers/Setting/Cache.php` — pengelolaan cache tersedia.
- [r] `src/Controllers/Setting/Optimasi.php` — pengelolaan log/debug dan optimasi database tersedia.
- [r] `src/Models/SettingModel.php` dan `src/Config/Crb.php` — komponen konfigurasi/model tersedia.
- [r] Uji simpan pengaturan situs/SMTP dan pengunggahan logo.
- [r] Uji penghapusan cache/log dan operasi optimasi database pada APP uji.
- [ ] Susun dokumentasi modul setelah uji manual.

## Modul statistik dan log email

Dokumentasi konsep modul: belum disusun.

- [r] `src/Controllers/Statistik.php` — statistik user, login, dan email tersedia.
- [r] `src/Controllers/Log/Email.php` dan `src/Models/LogEmailModel.php` — fitur log email tersedia.
- [ ] Uji tampilan statistik serta baca, hapus, reset, dan refresh log email.
- [ ] Susun dokumentasi modul setelah uji manual.

## Modul dropdown dinamis

Dokumentasi konsep modul: belum disusun.

- [r] `src/Controllers/Dropdown.php`, `src/Libraries/DropdownLibrari.php`, dan `src/Config/Dropdown.php` tersedia.
- [ ] Uji endpoint dropdown dengan data valid, parameter kosong, dan akses sesuai policy route APP.
- [ ] Susun dokumentasi modul setelah uji manual.

## Modul tabel dan operasi CRUD

Dokumentasi konsep modul: belum disusun.

- [r] `src/Libraries/TabelLibrari.php` tersedia.
- [r] `src/Controllers/Traits/CrudTrait.php` dan `DropdownTrait.php` tersedia.
- [r] Uji operasi CRUD dan data-table pada fitur CORE di APP.
- [r] Pastikan endpoint AJAX mengikuti filter route modul.
- [ ] Susun dokumentasi modul setelah uji manual.

## Modul helper dan utilitas

Dokumentasi konsep modul: belum disusun.

- [r] Helper aset, format, perangkat, menu, akses, dan nomor telepon tersedia di `src/Helpers/`.
- [r] `src/Libraries/CacheLibrari.php` dan `EmailLibrari.php` tersedia.
- [r] Uji helper dan library pada APP, termasuk kondisi input tidak valid.
- [ ] Susun dokumentasi modul setelah uji manual.

## Modul database CORE

Dokumentasi konsep: bagian publikasi/database di `Konsep.md`.

- [r] Migrasi dan seeder CORE berada pada `src/Database/`, bukan di sumber publish.
- [r] Uji `php spark migrate --all` pada APP bersih.
- [r] Uji seluruh seeder CORE pada APP uji dan pastikan data awal benar.
- [ ] Pastikan migrasi dapat dijalankan ulang sesuai mekanisme CI4 tanpa duplikasi atau kehilangan data.

## Modul publikasi dan bootstrap APP

Dokumentasi konsep: bagian publikasi/instalasi di `Konsep.md`.

- [r] `src/Commands/Publish.php` mem-publish semua file di grup sumber yang ditetapkan dan mempertahankan perlindungan terhadap file APP yang telah dikustomisasi.
- [r] `src/Publish/Config/Routes.php` dan `Home.php` menyediakan contoh awal APP; contoh route memakai filter `crbAkses`.
- [r] `src/Publish/Controllers/Profil.php` menyediakan contoh override controller CORE; dipublish ke `app/Controllers/Profil.php` (tidak ditimpa tanpa `--force`).
- [r] Konfigurasi tambahan vendor dipisahkan namanya dari konfigurasi sistem CI4, termasuk `CrbAkses.php` dan `CrbMenu.php`.
- [r] Uji `php spark cirebonweb:publish --all` pada APP CI4 baru.
- [r] Uji perilaku publish ulang tanpa `--force` untuk file yang belum dan sudah dikustomisasi.
- [r] Uji opsi `--force` pada salinan APP uji dan pastikan dampaknya dipahami.

## Modul layout dan tampilan bawaan

Dokumentasi konsep modul: belum disusun.

- [r] Template, dispatcher sidebar, halaman autentikasi/profil, dan view CORE tersedia pada `src/Views/` serta `src/Publish/Views/`.
- [p] Uji layout/sidebar gabungan menu APP dan CORE di browser.
- [p] Uji tampilan desktop/mobile dan halaman autentikasi pada APP.
- [ ] Susun dokumentasi modul setelah uji manual.

## Modul filter dan konfigurasi layanan

Dokumentasi konsep modul: belum disusun.

- [r] `src/Filters/CrbAkses.php` dan `HtmlMinify.php` tersedia.
- [r] Service route, dropdown, dan cache TTL tersedia di `src/Config/Services.php`.
- [r] Uji integrasi filter `crbAkses`, alias `session`/`group`, minifikasi, dan service pada APP.
- [ ] Susun dokumentasi modul setelah uji manual.

## Dokumentasi utama CORE

- [r] `src/Dokumen/Konsep.md` menjadi indeks konsep dan modul.
- [r] `src/Dokumen/modul-hak-akses.md` menjadi dokumentasi utama modul hak akses.
- [r] `src/Dokumen/modul-profil.md` menjadi dokumentasi konsep modul profil dan mekanisme override controller CORE oleh APP.
- [ ] Susun dokumentasi konsep untuk modul lain setelah implementasi dan uji manualnya selesai.
- [ ] Setelah konsep modul stabil, selaraskan `ABOUT.md`, `ARSITEKTUR.md`, `COMMAND.md`, dan `README.md`.
