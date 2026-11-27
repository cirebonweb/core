## Cirebonweb CI4+ All-in-One Admin Dashboard

![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)

## [Releases](https://github.com/cirebonweb/core/releases)

Versi CORE saat ini: **1.0.5**. APP baru dapat memasang rilis stabil terbaru dengan
`composer require cirebonweb/core`.

[![About](https://img.shields.io/badge/About-Project%20Story-blue)](ABOUT.md)
[![Contributing](https://img.shields.io/badge/Contributing-Guide-orange)](CONTRIBUTING.md)
[![Changelog](https://img.shields.io/badge/Changelog-Version%20History-yellow)](https://github.com/cirebonweb/core/tags)
[![Commands](https://img.shields.io/badge/Commands-Publish%20Options-purple)](COMMAND.md)

Cirebonweb CI4+ is an admin dashboard package built on CodeIgniter 4. Install
the package and publish its bootstrap resources with `php spark
cirebonweb:publish --all` to add the CORE configuration, starter routes, and
example home page to a new APP.

After publishing, set environment-specific APP values such as the database
connection and encryption key. The `--all` command publishes the bootstrap
configuration and public assets; CORE features themselves remain in the package
and are loaded directly by the APP.

Designed for maintainers, interns, and scalable systems. Built with clarity, auditability, and frontend control in mind.
A modular, production-ready admin dashboard built on **CodeIgniter 4+** and **AdminLTE 3.2**. Includes:

- Frontend configuration via **CI4 Settings**
- Login history with **device detection** (parsed from Shield `auth_logins.user_agent`
  at read time via `crb_device` helper + `device_<md5>` cache — no GeoIP / no location tracking)
- Email delivery with **HTML templates** + delivery log (`log_email`)
- Cache management with **custom helpers** (`crb_cache.json` runtime + `Config\Crb` default)
- Active session tracking (`auth_sesi`, inspired by Laravel Jetstream)

## ✨ Features

### 🖥️ Frontend Pages

- 📸 Profile photo upload with client-side compression using Pica (table `user_profil`)
- 🔐 Active device session tracking, inspired by Laravel Jetstream (table `auth_sesi`)
  - Session activity and expiry use the database `timestamp`; `dibuat` is for display in the APP timezone.
  - Session expiry is 2 hours. `regenerateDestroy = false` is retained for the production trial.
- 📊 Data statistics (AJAX `vendor/statistik/*`, cached):
  - User list (`statistik_user_list`)
  - Login activity (`statistik_user_login`, source: Shield `auth_logins`)
  - Email log (`statistik_log_email`)
- ⚙️ Settings panel:
  - General (system, site, logo, SMTP, Recaptcha)
  - Cache (duration, datatables cache, file deletion)
  - Optimization (database analysis and cleanup)
- 📄 Preconfigured pages:
  - Auth: login, magic link form, magic link message
  - User: profile, user list, login history
  - Setting: general, cache, optimization

### 🧩 Backend Helpers & Libraries

- 🧪 HTML minification (auto-enabled in production)
- 📧 Email library with HTML template support and delivery logging
- 🗂️ Cache helper for duration control and file management
- 🛡️ Shield authentication with login error tracking
- ⚙️ CI4 Settings module for dynamic configuration

### 🛠️ System Stack

- ✅ CodeIgniter Framework `^4.7`
- ✅ CodeIgniter Shield `^1.4`
- ✅ CodeIgniter Settings `^2.2`
- 🎨 AdminLTE 3.2 dashboard template
- 🐘 PHP `^8.2`

## 🚀 Getting Started Installation

This package requires the following core dependencies (see `composer.json`):

1. create a CodeIgniter APP
```bash
composer create-project codeigniter4/appstarter project-root
```

2. install CORE and its Composer dependencies
```bash
cd project-root
composer require cirebonweb/core
```

3. initialize Shield
```bash
php spark shield:setup
```

4. publish CORE resources
* please read [publish options](COMMAND.md) before you execute this command
```bash
php spark cirebonweb:publish --all
```
* Untuk APP baru yang belum dikustomisasi, gunakan
  `php spark cirebonweb:publish --all --force` agar CORE mengganti `BaseController`
  bawaan CI4 dan route bootstrap.

5. run database migrations
* ensure the APP environment already has valid database settings before migration
```bash
php spark migrate --all
```
* the package ships `HapusTabelUserLogin` only to clean up a leftover `user_login`
  table from older versions — it is harmless on a fresh install

6. run seeders if needed
* seeders are examples provided by CORE and are not published by the publish
  command. Run them explicitly by their fully qualified class name if needed:
```bash
php spark db:seed 'Cirebonweb\Database\Seeds\SettingsSeeder'
php spark db:seed 'Cirebonweb\Database\Seeds\UserSeeder'
```
* `UserSeeder` creates sample accounts. Review the seeder and change or remove
  sample credentials before using it in production.

7. run the project
```bash
php spark serve
```

> **Catatan login history:** riwayat login dibaca langsung dari tabel bawaan Shield
> `auth_logins`. Kolom perangkat / OS / browser diturunkan saat pembacaan dengan
> mem-parse `auth_logins.user_agent` (helper `crb_device`). Tabel `user_login`
> milik core sudah dibuang via migrasi `HapusTabelUserLogin` — tidak ada lagi
> pelacakan lokasi (negara / wilayah / distrik / zona waktu / ISP).

## 📄 License
This project is licensed under the MIT License.

## 🗂️ Documentation map

| Document | Audience | Contents |
|---|---|---|
| `README.md` (this file) | package consumer | Features, installation, usage |
| `ABOUT.md` | anyone | Project story and design philosophy |
| `COMMAND.md` | package consumer | Publish options per group + resulting file tree |
| `ARSITEKTUR.md` | core maintainer | Design rules and publish policy — **main reference** |
| `src/Dokumen/Konsep.md` | core maintainer | CORE module index and concept reference |
| `src/Dokumen/modul-hak-akses.md` | core maintainer | Access-control module decisions and behavior |

## 🤝 Contributing
Documentation within the source code (PHPDoc-style `/** ... */`) is written in Bahasa Indonesia to support local maintainers and interns. If you're interested in contributing—whether by improving documentation, translating, or extending features—feel free to join and collaborate.

## 📘 About
This package started as a personal tool to simplify CI4 project setup. To learn more about its origin, design choices, and purpose, see [ABOUT](ABOUT.md).