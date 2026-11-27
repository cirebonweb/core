# Log AI

Catatan perubahan yang dikerjakan dengan bantuan AI pada `cirebonweb-core`.
Dokumen ini melengkapi [daftar-kerja.md](daftar-kerja.md) sebagai riwayat sesi.

## Panduan penulisan

- Tulis entri terbaru di bagian atas (urutan kronologis terbalik).
- Awali setiap entri dengan heading tanggal/waktu dan identitas model AI.
- Gunakan label perubahan:
  - `[add]` membuat file/kode/fitur baru.
  - `[rev]` merevisi perilaku atau struktur kode/dokumen yang sudah ada.
  - `[fix]` memperbaiki bug atau kesalahan.
  - `[doc]` memperbarui dokumentasi tanpa perubahan perilaku kode.
  - `[test]` menambah atau memperbarui pengujian.
  - `[chore]` pekerjaan pendukung non-fitur.
- Cantumkan: tujuan/ringkasan, label pekerjaan, file yang diubah, hasil verifikasi, dan catatan/temuan yang belum selesai.
- Sertakan timestamp dengan zona waktu jika diketahui.

## 2026-11-28 02:59:39 +07:00 — Copilot SDK (VS Code)

- **Ringkasan:** Menyiapkan revisi 1.0.5 untuk pengujian Composer di APP lain dan meninjau ulang perbandingan kepemilikan profil.
- **Pekerjaan:**
  - `[fix]` [profil.php](../Publish/Views/profil.php): mempertahankan perbandingan ID pemilik yang aman terhadap perbedaan tipe integer/string, sesuai konfirmasi pengguna.
  - `[doc]` [README.md](../../README.md): memperbarui versi target menjadi 1.0.5.
  - `[doc]` [ARSITEKTUR.md](../../ARSITEKTUR.md): menyelaraskan keterangan group user contoh dengan `UserSeeder` (`superadmin` dan `user`).
  - `[doc]` Membuat catatan tentang perubahan Setting Umum, Telegram Chat ID, dan instalasi APP baru di entri sesi sebelumnya; panduan instalasi menganjurkan `php spark cirebonweb:publish --all --force` untuk APP baru.
- **Verifikasi:** `composer validate --no-check-publish`, `node --check src/Views/setting/umum.js`, `php -l` pada file PHP yang berubah, dan `git diff --check` lulus.
- **Catatan:** Belum dilakukan pengujian instalasi Composer end-to-end di APP terpisah. Uji email/SMTP/reset password/magic link masih ditunda ke lingkungan hosting. GitHub Release 1.0.4 telah diterbitkan secara retrospektif.

## 2026-11-28 02:37:06 +07:00 — Copilot SDK (VS Code)

- **Ringkasan:** Memperjelas penyimpanan pengaturan situs dan dokumentasi uji APP; pengujian pengiriman email ditunda sampai lingkungan online/hosting.
- **Pekerjaan:**
  - `[rev]` [umum.js](../Views/setting/umum.js): setelah `setting-umum/simpan-situs` sukses, tampilkan pesan sukses lalu reload halaman agar perubahan seperti `siteNama` langsung terlihat.
  - `[doc]` Menambahkan PHPDoc berlabel tab Sistem, Situs, Logo, dan SMTP pada fungsi/callback di `umum.js` agar mudah ditemukan.
  - `[rev]` Menyelaraskan `siteTelegram` menjadi Telegram Chat ID untuk notifikasi bot (ID numerik atau `@username` channel), opsional; validasi client/server diperbarui, seeder tidak lagi memakai contoh nomor telepon, dan `link_telegram()` tidak membuat tautan t.me untuk ID numerik.
  - `[doc]` Memperbarui [daftar-kerja.md](daftar-kerja.md): mencatat uji manual halaman vendor yang dikonfirmasi pengguna dan menunda uji email/SMTP/reset password/magic link sampai APP online.
- **File:** `src/Views/setting/umum.js`, `src/Views/setting/umum/situs.php`, `src/Models/SettingModel.php`, `src/Helpers/crb_phone_helper.php`, `src/Database/Seeds/SettingsSeeder.php`, `src/Dokumen/daftar-kerja.md`, `src/Dokumen/log-ai.md`
- **Verifikasi:** `node --check` lulus untuk `umum.js`; `php -l` lulus untuk PHP yang diubah; pola chat ID diuji dengan contoh valid/tidak valid; `git diff --check` lulus. Pengujian SMTP/email tidak dilakukan sesuai permintaan.
- **Catatan:** Chat ID numerik tidak dapat digunakan sebagai URL publik `t.me`; tautan Telegram hanya dibentuk untuk username/channel.

## 2026-11-28 01:10 +07:00 — Copilot SDK (VS Code)

- **Ringkasan:** Meninjau persiapan CORE 1.0.4, memperbaiki otorisasi profil lintas pengguna, dan menyelaraskan dokumentasi publish dengan sumber yang ada.
- **Pekerjaan:**
  - `[fix]` [Profil.php](../Controllers/Profil.php): pemilik dapat mengubah profil sendiri; melihat/mengubah profil pengguna lain mensyaratkan policy `vendor.user-list`. Endpoint ubah foto, akun, dan informasi memeriksa akses; ubah foto/informasi juga memverifikasi pasangan ID profil dan user.
  - `[fix]` [profil.php](../Publish/Views/profil.php): deteksi pemilik menggunakan perbandingan ID numerik agar tab sesi tidak bergantung pada tipe string/int.
  - `[doc]` [modul-profil.md](modul-profil.md): aturan akses profil lintas pengguna dan fungsi ID terenkripsi dijelaskan; batasan lama terkait pemeriksaan kepemilikan diperbarui.
  - `[doc]` [daftar-kerja.md](daftar-kerja.md): mencatat implementasi otorisasi dan butir uji yang masih menunggu APP bersih.
  - `[doc]` [README.md](../../README.md), [COMMAND.md](../../COMMAND.md), dan [ARSITEKTUR.md](../../ARSITEKTUR.md): versi target menjadi 1.0.4 dan daftar publish controller diselaraskan dengan `BaseController.php`, `Home.php`, serta `Profil.php`.
  - `[doc]` [Publish/Controllers/Profil.php](../Publish/Controllers/Profil.php): contoh konfigurasi override diperbaiki agar mengganti namespace grup profil, bukan mendaftarkan route duplikat.
- **Verifikasi:** `php -l` lulus untuk lima file PHP terkait; `node --check` lulus untuk dua file JS profil/upload; `composer validate --no-check-publish` dan `git diff --check` lulus.
- **Catatan:** Belum dilakukan uji instalasi APP bersih, migrasi/seeder, login, atau uji akses profil end-to-end. Butir integrasi tetap `[r]`/`[ ]`; tidak ditandai lulus.

## 2026-11-28 00:35:00 +07:00 — DeepSeek v4.1 Flash 1017 (CodeGPT)

- **Ringkasan:** Profil dapat diubah/ditambah oleh APP tanpa menyalin controller CORE: controller menyediakan hook, route grup dapat diarahkan ke namespace APP, dan bug `updateInfo()` diperbaiki. Dokumentasi modul profil disusun.
- **Pekerjaan:**
  - `[add]` Hook di [Profil.php](../Controllers/Profil.php): `profilData($user, $userId)`, `profilView()`, dan `infoValidationRules()`. `index()` menggabungkan data hook dan memakai `view($this->profilView(), $data)`.
  - `[add]` [Publish/Controllers/Profil.php](../Publish/Controllers/Profil.php) sebagai contoh override `App\Controllers\Profil` (`extends Cirebonweb\Controllers\Profil`).
  - `[fix]` `Profil::index()` aman terhadap `?id=` yang gagal didekripsi/bukan angka (fallback ke `auth()->id()`) dan terhadap hasil `getProfil()` yang `null`; perbandingan `auth()->id()` memakai cast `int`.
  - `[fix]` `Profil::updateInfo()` memakai `UserProfilModel::getValidationRules()`; sebelumnya memanggil properti `validationRules()` sehingga selalu `Call to undefined method`.
  - `[rev]` [CrbRoutes.php](../Config/CrbRoutes.php) dan [Publish/Config/Routes.php](../Publish/Config/Routes.php): dokumentasi override controller via opsi `namespace` grup route; konstanta `NAMESPACE` diberi keterangan; method `daftarRute()` untuk inspeksi daftar route modul.
  - `[doc]` Menambahkan [modul-profil.md](modul-profil.md) (batas CORE/APP, kontrak method `public`, daftar hook, tiga tingkat perubahan APP, batasan yang diketahui), indeks modul baru pada [Konsep.md](Konsep.md), dan butir kerja/uji pada [daftar-kerja.md](daftar-kerja.md).
- **File:** `src/Controllers/Profil.php`, `src/Publish/Controllers/Profil.php` (baru), `src/Config/CrbRoutes.php`, `src/Publish/Config/Routes.php`, `src/Dokumen/modul-profil.md` (baru), `src/Dokumen/Konsep.md`, `src/Dokumen/daftar-kerja.md`, `src/Dokumen/log-ai.md`
- **Verifikasi:** `php -l` lulus untuk seluruh file PHP yang diubah; `php spark routes` pada APP menampilkan seluruh endpoint `profil` mengarah ke `\App\Controllers\Profil`; `GET /profil` setelah login menghasilkan 200 dengan form upload dan `preview_foto_profil`; `POST /profil/update-info` dengan field tambahan mengembalikan `{"success": true, "messages": "Data berhasil diperbaharui."}`; log aplikasi tidak lagi memuat `Attempt to read property` maupun `undefined method` untuk profil.
- **Catatan:** Uji dilakukan pada APP di mesin pengembangan (`cirebonweb-app`) menggunakan junction `vendor/cirebonweb/core` → repo CORE. Belum diuji pada APP bersih hasil `composer create-project` + `cirebonweb:publish --all`. `updateInfo()` masih belum memeriksa kepemilikan `profil_id` terhadap `user_id`; jaminan saat ini hanya foreign key. Salinan view/JS profil di `src/Publish/Public` dan `src/Publish/Views/profil.php` sudah berbeda dari CORE awal (perbaikan preview upload dan penyesuaian view) tetapi belum tercatat pada log sebelumnya.

## 2026-11-27 21:38:10 +07:00 - Copilot Fusion

- **Ringkasan:** Mengganti `CrbBaseController` dengan template `App\Controllers\BaseController` yang dipublish CORE, agar APP dapat mengembangkan base controller sendiri sambil mempertahankan kontrak CRUD CORE.
- **Pekerjaan:**
  - `[add]` Menambahkan [BaseController.php](../Publish/Controllers/BaseController.php) sebagai sumber publish `app/Controllers/BaseController.php`, dengan dokumentasi PHPDoc untuk properti dan method yang menjadi kontrak CrudTrait dan tidak boleh dihapus/diubah tanpa penyesuaian terkait.
  - `[rev]` Mengubah controller CORE terkait agar memakai `App\Controllers\BaseController`; menghapus `src/Controllers/CrbBaseController.php`.
  - `[rev]` Memperbarui keterangan dependensi [CrudTrait.php](../Controllers/Traits/CrudTrait.php).
  - `[doc]` Menambahkan kebijakan BaseController publish dan merge manual APP ke [Konsep.md](Konsep.md).
- **File:** `src/Publish/Controllers/BaseController.php`, `src/Controllers/CrbBaseController.php` (dihapus), `src/Controllers/Dropdown.php`, `src/Controllers/Setting/UmumLogo.php`, `src/Controllers/Setting/Optimasi.php`, `src/Controllers/User/UserList.php`, `src/Controllers/User/UserLogin.php`, `src/Controllers/Traits/CrudTrait.php`, `src/Dokumen/Konsep.md`, `src/Dokumen/log-ai.md`
- **Verifikasi:** `php -l` lulus untuk BaseController baru, CrudTrait, serta seluruh controller yang dialihkan; `rg` tidak menemukan referensi tersisa ke `CrbBaseController`; `git diff --check` tanpa error whitespace.
- **Catatan:** Publish biasa tetap mengikuti aturan publisher yang menghindari penimpaan file APP yang sudah berubah. Jika APP menambah kode ke `BaseController.php`, pembaruan CORE perlu digabung manual sesuai keputusan.

## 2026-11-25 08:44:12 +07:00 — Copilot Fusion

- **Ringkasan:** Memperbarui pencatatan progres dan menindaklanjuti temuan uji APP pada User List (tambah user dan pengelolaan hak akses multi-group).
- **Pekerjaan:**
  - `[doc]` Memindahkan "Pembaruan terakhir" pada [daftar-kerja.md](daftar-kerja.md) menjadi tautan ke log ini, serta menambahkan panduan penulisan log.
  - `[fix]` Memperbaiki alur modal/tambah user: URL submit disimpan dinamis, validator form diinisialisasi sekali, dan reset form/id agar submit simpan tidak mengarah ke endpoint update.
  - `[rev]` Menyesuaikan pengelolaan hak akses User List agar mendukung beberapa group: pembacaan group user dari relasi Shield, opsi multi-select untuk aktor dengan lebih dari satu pilihan, normalisasi/validasi/penerapan sekumpulan group di controller, dan respons `get-id` menyertakan `groups`.
- **File:** `src/Dokumen/daftar-kerja.md`, `src/Dokumen/log-ai.md`, `src/Controllers/User/UserList.php`, `src/Models/UserModel.php`, `src/Views/user/user_list.php`, `src/Views/user/user_list.js`
- **Verifikasi:** `php -l` pada controller/model/view PHP yang diubah dan `node --check` pada `src/Views/user/user_list.js` lulus; `git diff --check` pada file yang disentuh lulus. Uji integrasi/manual APP belum dilakukan.
- **Catatan:** Temuan APP yang ditindaklanjuti: tambah user baru memunculkan "Peringatan ID user tidak ditemukan" dan edit hak akses hanya memilih satu role meski data bisa multi-group (contoh `admin, superadmin`). Perbaikan perlu disinkronkan ke APP dan diuji ulang, termasuk sinkronisasi artefak publish CORE â†’ APP.

## 2026-11-25 08:59:02 +07:00 — Copilot Fusion

- **Ringkasan:** Menambahkan rancangan konsep custom role dinamis.
- **Pekerjaan:**
  - [doc] Menambahkan bagian rancangan "custom role dinamis" pada [modul-hak-akses.md](modul-hak-akses.md) (alur pengelolaan role, aktor superadmin/developer, integrasi Shield/AuthGroups, penggunaan di User List dan CrbAkses, validasi/batasan).
  - [doc] Menambahkan butir tindak lanjut rancangan/implementasi custom role dinamis pada [daftar-kerja.md](daftar-kerja.md).
- **File:** src/Dokumen/modul-hak-akses.md, src/Dokumen/daftar-kerja.md, src/Dokumen/log-ai.md
- **Verifikasi:** Pemeriksaan isi dokumen; belum ada implementasi kode atau uji.
- **Catatan:** Rancangan ini dapat memengaruhi keputusan sumber daftar group dan sifat kebijakan modul; perlu ditinjau saat implementasi.

## 2026-11-25 12:43:01 +07:00 — Copilot Fusion

- **Ringkasan:** Eksperimen label role via `vendor/user-role`/settings dibatalkan; kembali ke alur manual. User List menampilkan `title` dari AuthGroups, key tetap dipakai untuk fungsi/policy/penyimpanan.
- **Pekerjaan:**
  - `[rev]` Menghapus modul `vendor/user-role` (route/menu/policy/controller/view/JS) dan helper override settings; `crb_group_label()` membaca title langsung dari `config('AuthGroups')`.
  - `[rev]` [UserList.php](../Controllers/User/UserList.php) menampilkan label role dari AuthGroups (key tetap dikirim/disimpan); kompatibel `groupOptions`/`groupOptionLabels`.
  - `[rev]` [user_list.php](../Views/user/user_list.php) dan [user_list.js](../Views/user/user_list.js): bidang Hak Akses menggunakan Select2 multi-value (`multiple`), opsi grup tetap tampil saat Add/Edit sesuai kewenangan.
  - `[doc]` [modul-hak-akses.md](modul-hak-akses.md) dan [daftar-kerja.md](daftar-kerja.md) disesuaikan dengan keputusan manual.
- **File:** `src/Helpers/crb_akses_helper.php`, `src/Controllers/User/UserList.php`, `src/Views/user/user_list.php`, `src/Views/user/user_list.js`, `src/Config/CrbRoutes.php`, `src/Config/CoreMenu.php`, `src/Publish/Config/CrbAkses.php`, `src/Publish/Config/Routes.php`, `src/Dokumen/modul-hak-akses.md`, `src/Dokumen/daftar-kerja.md`, `src/Dokumen/log-ai.md`
- **Verifikasi:** `php -l` lulus untuk file PHP yang diubah; `node --check` lulus untuk `user_list.js`.
- **Catatan:** Custom role/label dikelola manual di `app/Config/AuthGroups.php`; pemetaan modul manual di `app/Config/CrbAkses.php`.
