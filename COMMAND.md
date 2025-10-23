# 📦 Publish Commands Overview

This document provides an overview of the available `php spark cirebonweb:publish` commands used to publish essential resources for the Cirebonweb CI4+ Admin Dashboard.

Each command installs specific components—such as configuration files, database migrations, language packs, frontend assets, view templates, and writable data—into your project structure. These resources are designed to be customizable, override-friendly, and ready for production use.

Refer to the sections below for details on each publish option, including the resulting file structure and purpose.

#### Command for all publish
```
php spark cirebonweb:publish --all
```

#### Default configuration for starter kit
```
php spark cirebonweb:publish --config
```
```ruby
app/
└── Config/
    ├── App.php
    ├── Auth.php
    ├── AuthGroups.php
    ├── Filters.php
    ├── Pager.php
    └── Session.php
```

#### Migrations and Seeds for starter kit
```
 php spark cirebonweb:publish --database
```
```ruby
app/
└── Database/
    ├── Migrations/
    │   ├── 2025-08-28-030557_BuatUserProfil.php
    │   ├── 2025-08-29-071825_BuatUserLogin.php
    │   ├── 2025-09-21-023508_BuatAuthSesi.php
    │   └── 2025-10-09-160314_BuatLogEmail.php
    └── Seeds/
        ├── SettingsSeeder.php
        └── UserSeeder.php
```

#### Custom language
```
php spark cirebonweb:publish --language
```
```ruby
app/
└── Language/
    ├── en/
    └── id/
```

#### Asset files required for frontend
```bash
php spark cirebonweb:publish --public
```
```ruby
public/
├── dist/      # AdminLTE
├── page/      # Custom jQuery
├── plugin/    # AdminLTE plugins
└── upload/    # Default image
```

#### Customizable view templates
```bash
php spark cirebonweb:publish --view
```
```ruby
app/
└── Views/
    ├── admin/
    │   └── dashboard.php
    ├── layout/
    │   ├── 404.php
    │   ├── navbar.php
    │   ├── sidebar.php
    │   └── template.php
    ├── plugin/
    │   ├── tabel_css.php
    │   ├── tabel_js.php
    │   └── validasi_js.php
    ├── dashboard.php
    └── profil.php
```

#### Files cache duration and GeoLite2-City.mmdb location
To use GeoIP features, please download GeoLite2-City.mmdb from MaxMind after creating a free account.
```bash
php spark cirebonweb:publish --writable
```
```ruby
writable/
├── flag/
├── json/
│   └── crb_cache.json
└── uploads/
    └── GeoLite2-City.mmdb # place the GeoLite2-City.mmdb file here
```