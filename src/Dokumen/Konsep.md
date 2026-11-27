# Konsep Vendor `cirebonweb-core`

Dokumen ini adalah ringkasan konsep dan indeks modul vendor `CORE`. Gunakan
dokumen ini sebagai sumber rujukan saat memperbarui dokumentasi utama
`ABOUT.md`, `ARSITEKTUR.md`, `COMMAND.md`, dan `README.md`. Rincian khusus suatu
modul ditulis pada dokumen modul yang dirujuk di sini.

Jika dokumen kerja atau rincian modul berbeda dengan keputusan umum pada
dokumen ini, selaraskan dokumen rincian dengan konsep yang telah diputuskan.
Vendor dibuat untuk kebutuhan proyek pribadi dan dipublikasikan agar dapat
digunakan kembali.

## Tujuan CORE

- **Instalasi APP baru:** dibuat sesederhana mungkin agar proyek dapat segera
  digunakan tanpa konfigurasi manual berulang.
- **Fitur umum:** menyediakan kemampuan bersama seperti command, konfigurasi,
  controller, database, filter, helper, library, model, dan view.
- **Perawatan:** memusatkan fitur umum agar pembaruan dapat dikelola dari CORE.
- **Statis dan dinamis:** tampilan statis disediakan CORE; tampilan dinamis
  dapat disesuaikan APP dengan mengikuti mekanisme CORE.

Prinsip batas tanggung jawab modul:

```text
CORE (D:\xampp\htdocs\cirebonweb-core) = menyediakan fitur/capability
APP (D:\xampp\htdocs\cirebonweb-app)  = menentukan kebutuhan dan kebijakan proyek
```

## Indeks Modul CORE

Daftar ini disusun dari fitur yang saat ini tersedia di `src/`, bukan dari
struktur folder semata. Satu modul dapat menggunakan controller, route,
library, model, view, helper, konfigurasi, dan aset sekaligus. Komponen
pendukung lintas fitur dicatat terpisah dari modul fitur.

| Modul/area | Fitur CORE saat ini | Lokasi implementasi utama | Dokumentasi konsep |
| --- | --- | --- | --- |
| Hak akses, user, dan menu | Integrasi Shield, group bawaan, pengelolaan group pada User List, route CORE, menu APP/CORE, dan visibilitas menu | `Config/CrbRoutes.php`, `Config/CoreMenu.php`, `Filters/CrbAkses.php`, `Helpers/crb_akses_helper.php`, `Helpers/crb_menu_helper.php`, `Publish/Config/CrbAkses.php`, `Publish/Config/CrbMenu.php` | [`modul-hak-akses.md`](modul-hak-akses.md) |
| Autentikasi dan profil | Halaman magic link, profil akun, perubahan informasi/foto, serta pengelolaan perangkat dan sesi | `Controllers/Auth/`, `Controllers/Profil.php`, `Libraries/Auth*Librari.php`, `Models/Auth*Model.php`, `Views/shield/` | Belum disusun |
| Administrasi pengguna | Daftar dan perubahan akun, status, group, serta peninjauan riwayat login | `Controllers/User/`, `Controllers/Statistik.php`, `Models/UserModel.php`, `Models/UserProfilModel.php` | Belum disusun |
| Pengaturan CORE | Pengaturan umum situs dan SMTP, logo, cache, serta optimasi/log database | `Controllers/Setting/`, `Models/SettingModel.php`, `Config/Crb.php` | Belum disusun |
| Statistik dan log email | Statistik pengguna, login, dan email; peninjauan serta pengelolaan log email | `Controllers/Statistik.php`, `Controllers/Log/Email.php`, `Models/LogEmailModel.php` | Belum disusun |
| Dropdown dinamis | Endpoint dan library untuk menyediakan pilihan dropdown dari data | `Controllers/Dropdown.php`, `Libraries/DropdownLibrari.php`, `Config/Dropdown.php` | Belum disusun |
| Tabel dan operasi CRUD | Library tabel/data-table, trait CRUD, dan dukungan dropdown pada controller | `Libraries/TabelLibrari.php`, `Controllers/Traits/CrudTrait.php`, `Controllers/Traits/DropdownTrait.php` | Belum disusun |
| Helper dan utilitas | Fungsi bersama untuk aset, perangkat, format, menu, dan nomor telepon; library email/cache | `Helpers/`, `Libraries/CacheLibrari.php`, `Libraries/EmailLibrari.php` | Belum disusun |
| Database CORE | Migrasi untuk profil user, sesi, dan log email; seeder pengaturan serta user | `Database/Migrations/`, `Database/Seeds/`, `Models/` | Dirangkum pada bagian Publikasi dan Database di bawah |
| Publikasi dan bootstrap APP | Publikasi konfigurasi, controller, view, aset statis, dan berkas writable yang digunakan APP | `Commands/Publish.php`, `Publish/` | Dirangkum pada bagian Publikasi dan Database serta Instalasi di bawah |
| Layout dan tampilan bawaan | Template, navigasi APP/CORE, halaman profil, halaman autentikasi, dan potongan view pendukung | `Publish/Views/`, `Views/` | Belum disusun |
| Filter dan konfigurasi layanan | Filter minifikasi HTML, konfigurasi CORE, service route, dropdown, dan sumber TTL cache | `Filters/`, `Config/` | Belum disusun |

Tanda **Belum disusun** berarti indeks telah mengidentifikasi fitur dari
implementasi, tetapi konsep lengkapnya belum dirangkum dalam dokumen modul.
Komponen pada satu baris dapat menjadi bagian dari modul lain dan tidak
otomatis berarti setiap folder atau class merupakan modul tersendiri.

`Konsep.md` menjadi peta konsep CORE. Perubahan dokumentasi utama sebaiknya
diturunkan dari ringkasan dan keputusan modul yang tercatat di sini, bukan dari
catatan kerja atau percakapan yang belum dirangkum.

## Publikasi dan Database

Publikasi adalah penyalinan file dari `src/Publish` ke proyek `app`.

Migrasi CORE berada di `src/Database/Migrations`, bukan di `src/Publish`.
Migrasi ditemukan melalui namespace Composer `Cirebonweb` dan ikut dijalankan
dengan `php spark migrate --all`. Seeder CORE berada di `src/Database/Seeds`
dan dipanggil secara eksplisit dengan nama kelas penuh; seeder tidak berjalan
otomatis bersama migrasi.

`src/Publish` berisi berkas bootstrap yang dapat disalin ke APP melalui perintah
publish CORE. Fitur runtime CORE tetap berada di paket dan dimuat melalui
namespace `Cirebonweb`. Aset `plugin/` dan `vendor/` di bawah `src/Publish/Public`
adalah aset rawatan yang tidak ikut dipublish; APP menyalin atau menyelaraskannya
secara manual. Aset instalasi seperti `upload/` dapat dipublish.

`src/Publish/Controllers/BaseController.php` adalah template untuk
`app/Controllers/BaseController.php`. APP boleh menambahkan kode sendiri dan
menggabungkan pembaruan CORE secara manual. Properti `$modelClass` dan `$model`,
inisialisasi model dalam `initController()`, serta method `crudModel()`,
`crudSearchable()`, `crudOrderable()`, `ajax()`, dan `json()` merupakan kontrak
yang dipakai `CrudTrait`; jangan menghapus atau mengubah nama/signature-nya
tanpa menyesuaikan controller dan trait CORE.

Seeder bukan bagian dari proses migrasi otomatis dan tidak dijalankan oleh
perintah publish yang tersedia. Jalankan seeder secara eksplisit sesuai
kebutuhan APP. Sebelum memakai seeder contoh pada lingkungan produksi, periksa
data yang dibuat dan ubah atau hapus kredensial contoh.

**Catatan migrasi untuk APP lama:** riwayat migrasi CI4 dicocokkan menggunakan
versi dan nama kelas, bukan namespace. Migrasi yang sebelumnya dipublish dan
sudah dijalankan sebagai `App` tidak dijalankan lagi sebagai `Cirebonweb` oleh
`migrate --all`. Namun, `migrate:status` menampilkan status per namespace;
migrasi vendor dapat terlihat belum dijalankan walaupun versi/kelasnya sudah
ada di riwayat `App`.

## Modul Hak Akses

CORE menyediakan fitur, route, menu bawaan, group default, dan mekanisme umum;
APP menentukan policy dan security proyek. Menu CORE berasal dari konfigurasi
internal CORE dan otomatis digunakan jika APP tidak memberikan override.
Menu APP dan CORE dapat ditampilkan bersama pada sidebar. APP menentukan
kebijakan group per modul di `app/Config/CrbAkses.php`; definisi menu APP dan
override tampilan menu CORE berada di `app/Config/CrbMenu.php`. `aksesMenu()`
dan filter route membaca kebijakan yang sama menggunakan key modul yang sama.

Kebijakan `[]` berarti publik, `'session'` berarti semua user yang login, dan
daftar group membatasi akses modul serta visibilitas menu. Routes hasil publish
memasang `crbAkses` pada route CORE. Label halaman dan URL endpoint dapat
diambil dari `CrbMenu` berdasarkan key/url menu. APP harus memasang filter yang
sesuai pada route APP dan seluruh endpoint nonpublik, termasuk AJAX/API. CORE
tidak menyimpan mapping role→menu di database, tidak membuat permission CRUD,
dan tidak memaksakan bypass global `superadmin`. Aturan pemberian group pada
User List CORE tetap merupakan aturan internal fitur.

Layout menggunakan label menu yang URL-nya cocok dengan halaman sebagai judul
halaman dan tab browser; `pageTitle` hanya menjadi fallback. Untuk AJAX,
`CrbMenu.url('vendor.user-list', 'get-id')` membentuk URL endpoint dari URL
menu. Contoh konfigurasi dan pemakaian tersedia di
[`modul-hak-akses.md`](modul-hak-akses.md).

Untuk konsep dan panduan penerapan lengkap, lihat
[`modul-hak-akses.md`](modul-hak-akses.md).

## Instalasi Baru

Hanya berlaku untuk proyek baru, misalnya:
`composer create-project codeigniter4/appstarter cirebonweb-app`.

```text
app/
├── Config/
│   ├── App.php
│   ├── Auth.php
│   ├── Autoload.php
│   ├── CrbAkses.php
│   ├── CrbMenu.php
│   ├── Filters.php
│   ├── Pager.php
│   ├── Routes.php
│   └── Session.php
├── Controllers/
│   ├── BaseController.php
│   └── Home.php
├── Public/
│   ├── plugin/*
│   ├── upload/*
│   ├── vendor/*
│   └── .htaccess
├── Views/
│   ├── layout/
│   │   ├── navbar_menu.php
│   │   ├── sidebar_menu.php
│   │   └── template.php
│   ├── plugin/
│   │   ├── css_tabel.php
│   │   └── js_tabel_form.php
│   ├── home.php
│   └── profil.php
├── Writable/
│   ├── flag/
│   └── json/*
└── .env (konfigurasi khusus lingkungan APP)
```

## Instalasi Lama

APP yang sudah menggunakan vendor CORE:

```text
app/
├── Public/
│   ├── plugin/*
│   ├── vendor/*
│   └── .htaccess
└── Views/
    ├── layout/
    │   └── template.php
    └── plugin/
        ├── css_tabel.php
        └── js_tabel_form.php
```

Konfigurasi, route awal APP, controller Home, view, aset instalasi, dan writable
yang tercantum sebagai sumber publish disiapkan dengan `php spark
cirebonweb:publish --all`. APP tetap mengatur nilai khusus lingkungannya sendiri
seperti URL, koneksi database, dan kunci enkripsi pada `.env`.

`app/Config/Routes.php` hasil publish mendaftarkan filter `crbAkses` untuk
route CORE. Filter route dan `aksesMenu()` membaca kebijakan akses modul yang
sama dari `app/Config/CrbAkses.php` (`[]`, `'session'`, atau daftar group).
Menu APP dan override menu CORE berada di
`app/Config/CrbMenu.php`. Route atau endpoint APP yang perlu dilindungi juga
harus memakai filter dengan key kebijakan yang sesuai. Publisher mengganti
file Routes bawaan CI4 yang masih berupa route default dan controller Home
bawaan. Routes atau Home yang sudah dikustomisasi tidak ditimpa tanpa `--force`.

Dokumen di `src/Dokumen` adalah bahan konsep CORE. Setelah konsep-konsep modul
dan dokumentasi utama selesai diperbarui, dokumen kerja yang sudah tidak
diperlukan dapat diarsipkan atau dihapus sesuai tahap penyelesaian CORE.
