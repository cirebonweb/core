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

## 2026-11-25 08:44:12 +07:00 — MAI (Microsoft AI)

- **Ringkasan:** Memperbarui pencatatan progres dan menindaklanjuti temuan uji APP pada User List (tambah user dan pengelolaan hak akses multi-group).
- **Pekerjaan:**
  - `[doc]` Memindahkan "Pembaruan terakhir" pada [daftar-kerja.md](daftar-kerja.md) menjadi tautan ke log ini, serta menambahkan panduan penulisan log.
  - `[fix]` Memperbaiki alur modal/tambah user: URL submit disimpan dinamis, validator form diinisialisasi sekali, dan reset form/id agar submit simpan tidak mengarah ke endpoint update.
  - `[rev]` Menyesuaikan pengelolaan hak akses User List agar mendukung beberapa group: pembacaan group user dari relasi Shield, opsi multi-select untuk aktor dengan lebih dari satu pilihan, normalisasi/validasi/penerapan sekumpulan group di controller, dan respons `get-id` menyertakan `groups`.
- **File:** `src/Dokumen/daftar-kerja.md`, `src/Dokumen/log-ai.md`, `src/Controllers/User/UserList.php`, `src/Models/UserModel.php`, `src/Views/user/user_list.php`, `src/Views/user/user_list.js`
- **Verifikasi:** `php -l` pada controller/model/view PHP yang diubah dan `node --check` pada `src/Views/user/user_list.js` lulus; `git diff --check` pada file yang disentuh lulus. Uji integrasi/manual APP belum dilakukan.
- **Catatan:** Temuan APP yang ditindaklanjuti: tambah user baru memunculkan "Peringatan ID user tidak ditemukan" dan edit hak akses hanya memilih satu role meski data bisa multi-group (contoh `admin, superadmin`). Perbaikan perlu disinkronkan ke APP dan diuji ulang, termasuk sinkronisasi artefak publish CORE â†’ APP.

## 2026-11-25 08:59:02 +07:00 — MAI (Microsoft AI)

- **Ringkasan:** Menambahkan rancangan konsep custom role dinamis.
- **Pekerjaan:**
  - [doc] Menambahkan bagian rancangan "custom role dinamis" pada [modul-hak-akses.md](modul-hak-akses.md) (alur pengelolaan role, aktor superadmin/developer, integrasi Shield/AuthGroups, penggunaan di User List dan CrbAkses, validasi/batasan).
  - [doc] Menambahkan butir tindak lanjut rancangan/implementasi custom role dinamis pada [daftar-kerja.md](daftar-kerja.md).
- **File:** src/Dokumen/modul-hak-akses.md, src/Dokumen/daftar-kerja.md, src/Dokumen/log-ai.md
- **Verifikasi:** Pemeriksaan isi dokumen; belum ada implementasi kode atau uji.
- **Catatan:** Rancangan ini dapat memengaruhi keputusan sumber daftar group dan sifat kebijakan modul; perlu ditinjau saat implementasi.

## 2026-11-25 12:43:01 +07:00 — MAI (Microsoft AI)

- **Ringkasan:** Eksperimen label role via vendor/user-role/settings dibatalkan; kembali ke alur manual. User List menampilkan 	itle dari AuthGroups, key tetap dipakai untuk fungsi/policy/penyimpanan.
- **Pekerjaan:**
  - [rev] Menghapus modul vendor/user-role (route/menu/policy/controller/view/JS) dan helper override settings; crb_group_label() membaca title langsung dari config('AuthGroups').
  - [rev] [UserList.php](../Controllers/User/UserList.php) menampilkan label role dari AuthGroups (key tetap dikirim/disimpan); kompatibel groupOptions/groupOptionLabels.
  - [rev] [user_list.php](../Views/user/user_list.php) dan [user_list.js](../Views/user/user_list.js): bidang Hak Akses menggunakan Select2 multi-value (multiple), opsi grup tetap tampil saat Add/Edit sesuai kewenangan.
  - [doc] [modul-hak-akses.md](modul-hak-akses.md) dan [daftar-kerja.md](daftar-kerja.md) disesuaikan dengan keputusan manual.
- **File:** src/Helpers/crb_akses_helper.php, src/Controllers/User/UserList.php, src/Views/user/user_list.php, src/Views/user/user_list.js, src/Config/CrbRoutes.php, src/Config/CoreMenu.php, src/Publish/Config/CrbAkses.php, src/Publish/Config/Routes.php, src/Dokumen/modul-hak-akses.md, src/Dokumen/daftar-kerja.md, src/Dokumen/log-ai.md
- **Verifikasi:** php -l lulus untuk file PHP yang diubah;
ode --check lulus untuk user_list.js.
- **Catatan:** Custom role/label dikelola manual di app/Config/AuthGroups.php; pemetaan modul manual di app/Config/CrbAkses.php.
