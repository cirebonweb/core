## Cirebonweb CI4+ All-in-One Admin Dashboard

![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)
[![About](https://img.shields.io/badge/About-Project%20Story-blue)](ABOUT.md)
[![Contributing](https://img.shields.io/badge/Contributing-Guide-orange)](CONTRIBUTING.md)
[![Changelog](https://img.shields.io/badge/Changelog-Version%20History-yellow)](https://github.com/cirebonweb/core/tags)
[![Commands](https://img.shields.io/badge/Commands-Publish%20Options-purple)](COMMAND.md)

Cirebonweb CI4+ is a fully integrated admin dashboard built on the latest version of CodeIgniter 4. It is ready to use out of the box — simply install via Composer, publish the resources, and all core features are instantly available, including login tracking, email logging, cache control, and system configuration.

No manual setup required. No need to install modules one by one. Every page, helper, and configuration is pre-integrated and immediately operational, streamlining development and deployment for maintainers and teams.

Designed for maintainers, interns, and scalable systems. Built with clarity, auditability, and frontend control in mind.
A modular, production-ready admin dashboard built on **CodeIgniter 4+** and **AdminLTE 3.2**. Includes:

- Frontend configuration via **CI4 Settings**
- Login tracking with **Device and location detection**
- Email delivery with **HTML templates**
- Cache management with **Custom helpers**

## ✨ Features

### 🖥️ Frontend Pages

- 📸 Profile photo upload with client-side compression using Pica
- 🔐 Active device session tracking (inspired by Laravel Jetstream)
- 📊 Data statistics:
  - User list
  - Login activity
  - Email log
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
- 🌍 GeoIP2 integration with location detection (not include GeoLite2-City)
- 🛡️ Shield authentication with login error tracking
- ⚙️ CI4 Settings module for dynamic configuration

### 🛠️ System Stack

- ✅ CodeIgniter 4.x (latest version)
- 🎨 AdminLTE 3.2 dashboard template
- 🐘 PHP 8.1+ compatible

## 🛠️ Requirements & Installation

This package requires the following core dependencies:

```markdown
* codeigniter4/framework
* codeigniter4/settings
* codeigniter4/shield
```

### ⚠️ Manual Installation Requirement

Due to license restrictions and to prevent accidental uninstallation, the `geoip2/geoip2` package is **not** included in this package's `composer.json`.
Requirement: This package requires `geoip2/geoip2` version **`^3.2` or later** to function correctly.
You MUST install it separately before running any GeoIP-related features:

```bash
composer require geoip2/geoip2
```

To enable location detection, you must manually download and configure the GeoLite2 database:

- Download: Create a free account at MaxMind and download the `GeoLite2-City.mmdb` file.
- Placement: Place the downloaded file in a secure, non-public directory within your CodeIgniter project (e.g., writable/uploads/).
- Configuration: Adjust your CodeIgniter configuration file to point to the correct database path.

Note: The command above installs the latest compatible stable version. If you require a specific version, ensure your project's composer.json file explicitly requires geoip2/geoip2:^3.2. Failure to complete both steps will result in fatal errors when using GeoIP features.

## 🚀 Getting Started

1. Installation with composer.
```bash
composer require cirebonweb/core
```

2. publish root project:
```bash
php spark cirebonweb:publish --all
```
See [COMMAND](COMMAND.md) for detailed information on changes to your root project.

## 📄 License
This project is licensed under the MIT License.

## 🤝 Contributing
Documentation within the source code (PHPDoc-style `/** ... */`) is written in Bahasa Indonesia to support local maintainers and interns. If you're interested in contributing—whether by improving documentation, translating, or extending features—feel free to join and collaborate.

## 📘 About
This package started as a personal tool to simplify CI4 project setup. To learn more about its origin, design choices, and purpose, see [ABOUT](ABOUT.md).