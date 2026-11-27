# 📦 Publish Commands Overview

This document provides an overview of the available `php spark cirebonweb:publish` commands used to publish essential resources for the Cirebonweb CI4+ Admin Dashboard.

Each command publishes selected bootstrap resources—such as configuration files,
frontend assets, view templates, and writable data—into your project structure.
CORE database migrations remain in the package and are run directly by CodeIgniter;
they are not copied into the APP. Published resources are designed to be
customizable and override-friendly.

Refer to the sections below for details on each publish option, including the resulting file structure and purpose.

## ⚠️ Attention
Please pay close attention, by running the command
```bash
php spark cirebonweb:publish --all
```

 will run all the commands below.
```bash
php spark cirebonweb:publish --config
php spark cirebonweb:publish --controllers
php spark cirebonweb:publish --views
php spark cirebonweb:publish --public
php spark cirebonweb:publish --writable
```

> Perilaku publish **tanpa `--force`**: file yang isinya sudah berbeda **tidak**
> ditimpa (ditandai `!`, dihitung "dilewati"). Detail anti-timpa:
> `ARSITEKTUR.md` §4.

> Flag `--force` dan `--dry-run` adalah modifier (bisa digabung dengan grup
> mana pun): `--force` menimpa file yang sudah diedit app, `--dry-run` hanya
> menampilkan rencana tanpa menulis file.

> **Awas perintah mirip bawaan CI4:** nama perintah core adalah
> `cirebonweb:publish` (tanpa `s`), bukan `publish` bawaan framework.
> Salah ketik `php spark publish --all` akan menjalankan publisher bawaan CI4,
> bukan perintah core ini.

> **Awas flag mirip:** grup core bernama `--views` (jamak), bukan `--view`.
> Database CORE tidak dipublish; migrasi ditemukan langsung dari paket.

> `--all` menjalankan semua grup dan menyalin seluruh isi masing-masing sumber,
> termasuk aset `plugin/`, `vendor/`, dan `upload/` dari `src/Publish/Public`.

### Instalasi APP baru

Untuk instalasi APP yang baru dibuat dan belum memiliki kustomisasi, jalankan
publisher sekali dengan kedua opsi berikut:

```bash
php spark cirebonweb:publish --all --force
```

`--all` memilih semua grup publish, sedangkan `--force` memastikan file
bootstrap bawaan CI4—terutama `app/Config/Routes.php`,
`app/Controllers/Home.php`, dan `app/Controllers/BaseController.php`—diganti
dengan versi CORE. Tanpa `--force`, publisher dapat melewati file yang
dianggap sudah berbeda dari sumber; perintah tetap selesai, tetapi file penting
mungkin tidak terpasang dan APP dapat mengalami error saat dijalankan.

Gunakan `--force` hanya pada instalasi baru atau ketika memang ingin
menimpa perubahan APP. Untuk APP yang sudah berjalan dan memiliki kustomisasi,
jangan jalankan `--all --force` tanpa meninjau dampaknya; publish grup yang
diperlukan tanpa `--force`, lalu gabungkan perubahan yang dilewati secara
manual. Tidak perlu menjalankan `--all`, `--force`, dan `--all --force`
sebagai tiga perintah terpisah.

## 🚀 Publish config
```bash
php spark cirebonweb:publish --config
```

### 📁 Config file to be published

> Perintah ini mem-publish konfigurasi bootstrap dari `src/Publish/Config`,
> termasuk konfigurasi vendor `CrbAkses.php` dan `CrbMenu.php`, serta contoh
> `Routes.php` untuk APP. Filter route memakai policy group APP pada konfigurasi
> `CrbAkses.php`.

```ruby
app/
└── Config/
    ├── App.php
    ├── Auth.php
    ├── Autoload.php
    ├── CrbAkses.php
    ├── CrbMenu.php
    ├── Filters.php
    ├── Pager.php
    ├── Routes.php
    └── Session.php
```

### Konfigurasi setelah publish

`php spark cirebonweb:publish --all` menyalin konfigurasi awal CORE, termasuk
route contoh ke `app/Config/Routes.php`. Tinjau filter pada route tersebut dan
sesuaikan dengan policy APP. Publisher mengganti `Routes.php` bawaan CI4 yang
masih berupa route default dan `Home.php` bawaan yang masih menampilkan halaman
sambutan. File yang telah dikustomisasi tidak ditimpa tanpa `--force`.

Nilai `.env` seperti URL, koneksi database, dan kunci enkripsi tetap khusus
lingkungan APP dan tidak dipublish. Pastikan konfigurasi database valid sebelum
menjalankan migrasi. Alias `session` dan `group` disediakan pada konfigurasi
filter hasil publish. Filter CSRF nonaktif secara bawaan; aktifkan setelah
mengaudit semua form dan request AJAX.
## 🚀 Publish controllers
```bash
 php spark cirebonweb:publish --controllers
```

> Grup `controllers` menyalin seluruh isi `src/Publish/Controllers`, termasuk
> `BaseController.php` yang menjadi kontrak controller CORE, serta contoh
> `Home.php` dan `Profil.php`.
>
> | File | Status |
> |---|---|
> | `BaseController.php` | ✅ dipublish sebagai base controller APP untuk CORE |
> | `Home.php` | ✅ dipublish sebagai contoh halaman awal APP |
> | `Profil.php` | ✅ dipublish sebagai contoh override profil CORE |

### 📁 controllers files to be published
```ruby
app/
└── Controllers/
    ├── BaseController.php
    ├── Profil.php
    └── Home.php
```

`BaseController.php` merupakan fondasi bagi controller CORE yang menggunakan
`CrudTrait`. Pertahankan kontrak properti dan method yang didokumentasikan di
dalam file tersebut. `Home.php` dan `Profil.php` adalah contoh yang dapat
disesuaikan APP.

## 🗄️ CORE database

Migrasi CORE berada di `src/Database/Migrations` dan ditemukan melalui namespace
Composer. Setelah mengatur koneksi database APP, jalankan:

```bash
php spark migrate --all
```

Seeder CORE berada di `src/Database/Seeds/`, tidak ikut dipublish, dan tidak
berjalan otomatis bersama migrasi. Jalankan seeder yang diperlukan secara
eksplisit menggunakan nama kelas penuh, misalnya:

```bash
php spark db:seed 'Cirebonweb\Database\Seeds\SettingsSeeder'
```

Tinjau isi seeder contoh sebelum dijalankan, terutama di lingkungan produksi.

## 🚀 Publish public
```bash
php spark cirebonweb:publish --public
```

### 📁 Asset files to be published

> Grup `public` menyalin seluruh isi `src/Publish/Public`, termasuk `.htaccess`,
> `plugin/`, `upload/`, dan `vendor/`.

```ruby
public/
├── .htaccess
├── plugin/      # Bootstrap, DataTables, jQuery, Pica, Select2, SweetAlert
├── upload/      # logo dan gambar profil default
└── vendor/
    ├── css/     # AdminLTE dan stylesheet CORE
    └── js/      # AdminLTE dan helper CORE
```

Pada publish pertama, aset-aset tersebut ikut disalin otomatis. Jika file tujuan
sudah ada dan isinya berbeda, publisher melewatinya kecuali `--force` digunakan.
Gunakan `--force` hanya jika memang ingin mengganti salinan APP yang sudah
disesuaikan.

## 🚀 Publish views
```bash
php spark cirebonweb:publish --views
```

> Grup `views` menyalin seluruh isi `src/Publish/Views`; tidak ada daftar file
> yang dikecualikan.

### 📁 View files to be published
```ruby
app/
└── Views/
    ├── home.php
    ├── profil.php
    ├── layout/
    │   ├── navbar_menu.php
    │   ├── sidebar_menu.php
    │   └── template.php
    └── plugin/
        ├── css_tabel.php
        └── js_tabel_form.php
```

`navbar_menu.php` dan `sidebar_menu.php` adalah titik penyesuaian navigasi APP.

## 🚀 Publish writable
```bash
php spark cirebonweb:publish --writable
```

### 📁 Writable files to be published
```ruby
writable/
└── json/
    └── crb_cache.json   # durasi cache runtime (cache_device, statistik_*)
```