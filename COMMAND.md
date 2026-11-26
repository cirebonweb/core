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
> ditimpa (ditandai `!`, dihitung "dilewati"). Detail blokir & anti-timpa:
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

> **`--all` tidak berarti "semua aset ikut."** `public/plugin/` dan
> `public/vendor/` tetap diblokir (aset rawatan) dan harus disalin manual —
> lihat §Publish public.

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
    ├── AuthGroups.php
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

> Grup `controllers` mem-publish `src/Publish/Controllers` **kecuali** yang diblokir
> (`Admin/Dashboard.php`, `Klien/Dashboard.php` — lihat `Publish.php::$blocked`).
>
> | File | Status |
> |---|---|
> | `BaseController.php` | ✅ berasal dari skeleton APP |
> | `Home.php` | ✅ dipublish sebagai contoh halaman awal APP |
> | `Admin/Dashboard.php`, `Klien/Dashboard.php` | ⛔ dikecualikan dari publish |

### 📁 controllers files to be published
```ruby
app/
└── Controllers/
    └── Home.php
```

> `Admin/Dashboard.php` dan `Klien/Dashboard.php` tetap ada di
> `src/Publish/Controllers/` tetapi dikecualikan dari publish. `Home.php` ikut
> dipublish sebagai contoh yang dapat disesuaikan APP.

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

> Grup `public` mem-publish `src/Publish/Public` **kecuali** yang diblokir
> (`plugin/`, `vendor/` — aset rawatan, dikelola manual pemilik core;
> lihat `Publish.php::$blocked` dan `ARSITEKTUR.md` §4c).
> Yang ikut ter-publish hanya `.htaccess` + `upload/`.

```ruby
public/
├── .htaccess    # rewrite CI4 + kompresi/cache (sekali, butuh --force)
└── upload/      # logo & gambar default milik app baru
    ├── logo/
    └── profil/
```

### ⚠️ Aset rawatan tidak ikut publish — salin manual

Setelah `--public` (atau `--all`) selesai, `public/` **belum** punya CSS/JS
AdminLTE maupun plugin. `plugin/` dan `vendor/` memang diblokir dari publish
(`Publish.php::$blocked['public']`), jadi project wajib menyalinnya sendiri dari
paket:

```powershell
Copy-Item vendor/cirebonweb/core/src/Publish/Public/plugin public/plugin -Recurse
Copy-Item vendor/cirebonweb/core/src/Publish/Public/vendor public/vendor -Recurse
```

Kalau terlewat, halaman tetap 200 tapi **tanpa styling** — `adminlte.min.css`,
`core.min.js`, dan `plugin/*` akan 404. Itulah alasan blokir ini ada: aset adalah
milik core dan berubah sepihak, jadi core tidak boleh menimpa versinya sendiri
di project (`ARSITEKTUR.md` §4c).

## 🚀 Publish views
```bash
php spark cirebonweb:publish --views
```

> Grup `views` mem-publish `src/Publish/Views` **kecuali** yang diblokir
> (`profil.php`, `dashboard.php`, `admin/dashboard.php`, `klien/dashboard.php`,
> `layout/template_old.php` — lihat `Publish.php::$blocked` dan
> `ARSITEKTUR.md` §7).

### 📁 View files to be published
```ruby
app/
└── Views/
    ├── layout/
    │   ├── 404.php
    │   └── template.php
    └── plugin/
        ├── tabel_css.php
        └── validasi_js.php
```

> `profil.php`, `dashboard.php`, `admin/`, `klien/`, `template_old.php` **tetap ada**
> di `src/Publish/Views/` tetapi dikecualikan dari publish sesuai kebijakan saat ini.

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