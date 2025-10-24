# 📦 Publish Commands Overview

This document provides an overview of the available `php spark cirebonweb:publish` commands used to publish essential resources for the Cirebonweb CI4+ Admin Dashboard.

Each command installs specific components—such as configuration files, database migrations, frontend assets, view templates, and writable data—into your project structure. These resources are designed to be customizable, override-friendly, and ready for production use.

Refer to the sections below for details on each publish option, including the resulting file structure and purpose.

## ⚠️ Attention
Please pay close attention, by running the command `cirebonweb/publish --all` will run all the commands below.
```bash
php spark cirebonweb:publish --config
php spark cirebonweb:publish --controllers
php spark cirebonweb:publish --database
php spark cirebonweb:publish --public
php spark cirebonweb:publish --view
php spark cirebonweb:publish --writable
```

## 🚀 Publish config
```bash
php spark cirebonweb:publish --config
```

### 📁 Config file to be published
```ruby
app/
└── Config/
    ├── App.php
    ├── Auth.php
    ├── AuthGroups.php
    ├── Autoload.php
    ├── Filters.php
    ├── Pager.php
    ├── Routes.php
    ├── Security.php
    └── Session.php
```

### 🛠️ Manual config for new or old project
app\Config\App.php
```ruby
public string $baseURL = '';
public string $indexPage = '';
public string $defaultLocale = 'id';
public array $supportedLocales = ['id', 'en'];
```

app\Config\Auth.php 
```ruby
public array $views = [
    'login'                       => '\Cirebonweb\shield\login',
    'register'                    => '\Cirebonweb\shield\register',
    'layout'                      => '\Cirebonweb\shield\layout',
    'action_email_2fa'            => '\Cirebonweb\shield\email_2fa_show',
    'action_email_2fa_verify'     => '\Cirebonweb\shield\email_2fa_verify',
    'action_email_2fa_email'      => '\Cirebonweb\shield\Email\email_2fa_email',
    'action_email_activate_show'  => '\Cirebonweb\shield\email_activate_show',
    'action_email_activate_email' => '\Cirebonweb\shield\Email\email_activate_email',
    'magic-link-login'            => '\Cirebonweb\shield\magic_link_form',
    'magic-link-message'          => '\Cirebonweb\shield\magic_link_message',
    'magic-link-email'            => '\Cirebonweb\shield\Email\magic_link_email',
];
```

app\Config\AuthGroups.php
```ruby
public string $defaultGroup = 'klien';
```
```ruby
public array $groups = [
    'superadmin' => [
        'title'       => 'Super Admin',
        'description' => 'Complete control of the site.',
    ],
    'admin' => [
        'title'       => 'Admin',
        'description' => 'Day to day administrators of the site.',
    ],
    'developer' => [
        'title'       => 'Developer',
        'description' => 'Site programmers.',
    ],
    'klien' => [
        'title'       => 'Klien',
        'description' => 'General users of the site. Often customers.',
    ],
    'beta' => [
        'title'       => 'Beta User',
        'description' => 'Has access to beta-level features.',
    ],
];
```
```ruby
public array $permissions = [
    'admin.access'        => 'Can access the sites admin area',
    'admin.settings'      => 'Can access the main site settings',
    'klien.manage-admins' => 'Can manage other admins',
    'klien.create'        => 'Can create new non-admin users',
    'klien.edit'          => 'Can edit existing non-admin users',
    'klien.delete'        => 'Can delete existing non-admin users',
    'beta.access'         => 'Can access beta-level features',
];
```
```ruby
public array $matrix = [
    'superadmin' => [
        'admin.*',
        'klien.*',
        'beta.*',
    ],
    'admin' => [
        'admin.access',
        'klien.create',
        'klien.edit',
        'klien.delete',
        'beta.access',
    ],
    'developer' => [
        'admin.access',
        'admin.settings',
        'klien.create',
        'klien.edit',
        'beta.access',
    ],
    'klien' => [],
    'beta' => [
        'beta.access',
    ],
];
```

app\Config\Autoload.php
```ruby
public $helpers = ['auth', 'setting'];
```

app\Config\Filters.php
```ruby
use Cirebonweb\Filters\HtmlMinify;

public array $aliases = [
    'minify'        => HtmlMinify::class,
];

public array $globals = [
    'before' => [],
    'after' => [
        'minify',
    ],
];
```

app\Config\Pager.php
```ruby
public array $templates = [
    'bs4_full'       => 'Cirebonweb\Views\Pager\bs4_pagination',
];
```

app\Config\Routes.php
```ruby
$routes->get('/', 'Home::index', ['filter' => 'session']);
$routes->get('admin', 'Admin\Dashboard::index', ['filter' => 'group:admin']);

service('cirebonweb')->profil()->routes($routes, ['filter' => 'session']);
service('cirebonweb')->userList()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->userLogin()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->statistik()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->settingUmum()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->settingCache()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->settingOptimasi()->routes($routes, ['filter' => 'group:admin']);
service('cirebonweb')->log()->routes($routes, ['filter' => 'group:admin']);

service('auth')->routes($routes, ['except' => ['login', 'magic-link']]);
service('cirebonweb')->auth()->routes($routes);
```

app\Config\Security.php
```ruby
public string $csrfProtection = 'session';
```

app\Config\Session.php
```ruby
use CodeIgniter\Session\Handlers\DatabaseHandler;
public string $driver = DatabaseHandler::class;
public string $cookieName = 'sesi';
public string $savePath = 'auth_sesi';
public int $timeToUpdate = 900;
public ?string $DBGroup = 'default';
```

## 🚀 Publish controllers
```bash
 php spark cirebonweb:publish --controllers
```

### 📁 controllers files to be published
```ruby
app/
└── Controllers/
    ├── Admin/
    │   └── Dashboard.php
    ├── Klien/
    │   └── Dashboard.php
    ├── BaseController.php
    └── Home.php
```

## 🚀 Publish database
```bash
 php spark cirebonweb:publish --database
```

### 📁 Database files to be published
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

## 🚀 Publish public
```bash
php spark cirebonweb:publish --public
```

### 📁 Asset files to be published
```ruby
public/
├── dist/      # AdminLTE
├── page/      # Custom jQuery
├── plugin/    # AdminLTE plugins
└── upload/    # Default image
```

## 🚀 Publish view
```bash
php spark cirebonweb:publish --view
```

### 📁 Asset files to be published
```ruby
app/
└── Views/
    ├── admin/
    │   └── dashboard.php
    ├── klien/
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

## 🚀 Publish writable
```bash
php spark cirebonweb:publish --writable
```

### 📁 File cache duration and GeoLite2-City.mmdb location
```ruby
writable/
├── flag/
├── json/
│   └── crb_cache.json
└── uploads/
    └── GeoLite2-City.mmdb # place the GeoLite2-City.mmdb file here
```