# Modul Hak Akses — cirebonweb-core

Dokumen ini menjadi dokumentasi utama modul hak akses CORE, merangkum konsep
final, keputusan, dan panduan penerapannya.

## 1. Prinsip dan tanggung jawab

```text
CORE = menyediakan fitur/capability
APP  = menentukan policy + security
```

CORE menyediakan fitur, route, menu bawaan, group default, dan mekanisme umum.
APP memegang kendali atas kebijakan proyek dan keamanan route melalui
konfigurasi vendor `app/Config/CrbAkses.php` dan `app/Config/CrbMenu.php`.

APP menentukan aturan akses setiap modul dalam `app/Config/CrbAkses.php`.
Pendaftaran route dan visibilitas menu memakai key modul dan kebijakan yang
sama:

- `[]`: publik, tidak perlu login.
- `'session'`: semua user yang sudah login, tanpa pembatasan group.
- `['admin', 'developer']`: user harus termasuk salah satu group tersebut.

Saat kebijakan `'session'` menolak pengunjung anonim, filter `crbAkses`
mengarahkan user ke halaman login Shield.

## 2. Group dan user

CodeIgniter Shield menjadi fondasi authentication dan group. Konsep penggunaan
CORE adalah satu user memiliki satu group, dengan relasi Shield
`auth_groups_users`.

CORE menyediakan group default `superadmin`, `admin`, `user`, `developer`, dan
`beta`; group default registrasi adalah `user`. APP boleh menyesuaikan
konfigurasi group dan menambahkan group sesuai kebutuhan. Custom group adalah
kebutuhan APP dan tidak menjadikan CORE bergantung pada domain bisnis.

`src/Publish/Config/AuthGroups.php` adalah konfigurasi proyek yang memperluas
konfigurasi bawaan Shield. File ini diperlukan agar group default dan daftar
group CORE tersedia setelah dipublish ke APP; konfigurasi bawaan Shield tidak
otomatis menyediakan seluruh daftar group CORE. APP dapat menyesuaikan
konfigurasi tersebut sesuai kebutuhan proyek.

`src/Publish/Config/Auth.php` juga memperluas konfigurasi Shield. CORE
mempertahankannya karena pemetaan `$views` mengarahkan halaman autentikasi ke
tampilan milik CORE. Tanpa pemetaan itu, Shield menggunakan tampilan
bawaannya.

### Aturan pemberian group di User List CORE

Aturan ini adalah aturan internal fitur pengelolaan user CORE, bukan kebijakan
keamanan route:

- `superadmin` dapat memilih group yang terdaftar pada konfigurasi `AuthGroups`.
- `admin` dan `developer` hanya dapat menetapkan group `user`.
- `admin` dan `developer` tidak dapat mengubah group akun yang sudah memiliki
  group istimewa.
- Aturan harus divalidasi di server, tidak cukup hanya membatasi pilihan pada
  form.

APP menentukan group atau kebijakan session yang dapat membuka User List CORE
beserta endpoint terkait melalui `CrbAkses::$modules`.

## 3. Menu APP dan menu CORE

- Menu bisnis APP didefinisikan pada properti `$menuApp` di
  `app/Config/CrbMenu.php`.
- Menu bawaan CORE (Pengguna, Pengaturan, dan Log) disimpan pada konfigurasi
  internal CORE dan digunakan otomatis sebagai fallback.
- APP dapat menambahkan menu melalui `CrbMenu::$menuApp` atau mengganti label
  dan ikon menu CORE melalui `CrbMenu::$menuCore`. Key dan URL menu CORE tidak
  dapat diganti oleh APP.
- `sidebar_menu.php` merender menu APP dan `sidebar_vendor.php` merender menu
  CORE; dispatcher dapat menggabungkan keduanya dalam satu sidebar.
- Key setiap item menu yang memiliki URL adalah key modul yang dipakai dalam
  `CrbAkses::$modules`. Parent menu hanya mengelompokkan item anak.

Metadata menu memiliki key, label, URL, ikon, dan relasi parent-child. Parent
hanya merupakan container; parent ditampilkan jika memiliki anak yang dapat
diakses.

File `src/Publish/Config/CrbMenu.php` dipublish menjadi
`app/Config/CrbMenu.php`.
Daftar menu APP berada pada `$menuApp`; override label dan ikon menu CORE
berada pada `$menuCore`. Pengaturan akses menu tidak didefinisikan di
`CrbMenu.php`.

### Konfigurasi kebijakan modul

`src/Publish/Config/CrbAkses.php` dipublish menjadi `app/Config/CrbAkses.php`.
Gunakan key modul sebagai kunci. Nilai dapat berupa daftar group, `[]` untuk
publik, atau string `'session'` untuk semua user yang sudah login:

```php
public array $modules = [
    'home.dashboard'       => ['admin', 'developer'],
    'profil'                  => 'session',
    'dropdown'                => 'session',
    'vendor.user-list'        => ['superadmin'],
    'vendor.user-login'       => ['admin', 'developer'],
    'vendor.statistik'        => ['admin', 'developer'],
    'vendor.setting-umum'     => ['admin'],
    'vendor.setting-cache'    => ['admin'],
    'vendor.setting-optimasi' => ['admin'],
    'vendor.log-email'        => ['admin'],
    'transaksi.nota'          => ['admin', 'user'],
    'transaksi.pembayaran'    => ['admin'],
];
```

Daftar kosong (`[]`) berarti publik, termasuk pengunjung anonim. Nilai
`'session'` mengizinkan seluruh user yang sudah login. Daftar group mengizinkan
user yang menjadi anggota salah satu group; `superadmin` tidak mendapat bypass
otomatis. Setiap key menu dan route harus memiliki entri kebijakan. Key yang
belum dikonfigurasi menyebabkan kesalahan konfigurasi. Saat mengubah daftar
bawaan, pertahankan entri yang masih digunakan route atau menu lain.

Untuk memberikan akses pada Dashboard hanya kepada `admin` dan `developer`,
key menu dan route harus sama dengan key pada konfigurasi:

```php
// app/Config/CrbMenu.php
public array $menuApp = [
    [
        'key'   => 'home.dashboard',
        'label' => 'Dashboard',
        'url'   => '/',
        'icon'  => 'bi bi-speedometer2',
    ],
];

// Pada app/Config/CrbAkses.php, ubah nilai entri yang sudah ada:
public array $modules = [
    'home.dashboard' => ['admin', 'developer'],
    'profil'         => 'session',
];

// app/Config/Routes.php
$routes->get('/', 'Home::index', ['filter' => 'crbAkses:home.dashboard']);
```

Contoh tersebut mengatur satu kebijakan untuk visibilitas menu dan keamanan
route. `visibleGroups` tidak digunakan. Gunakan `'session'` agar semua user
yang sudah login dapat mengakses modul tanpa dibatasi group.

### Contoh override menu CORE

Contoh berikut ditempatkan pada `app/Config/CrbMenu.php`. Kebijakan akses
untuk item tetap diatur dengan key yang sama di `app/Config/CrbAkses.php`:

```php
public array $menuCore = [
    'vendor.user-list' => [
        'label' => 'Manajemen Pengguna',
        'icon'  => 'bi bi-people',
    ],
];
```

Properti `key` dan `url` tidak boleh ditambahkan ke override. Tanpa override
label/ikon, nilai bawaan CORE digunakan.

### Contoh penerapan menu APP

Contoh berikut menambahkan menu APP datar dan bertingkat di
`app/Config/CrbMenu.php`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CrbMenu extends BaseConfig
{
    public array $menuApp = [
        [
            'key'   => 'home.dashboard',
            'label' => 'Beranda',
            'url'   => '/',
            'icon'  => 'bi bi-house',
        ],
        [
            'key'      => 'transaksi',
            'label'    => 'Transaksi',
            'icon'     => 'bi bi-receipt',
            'children' => [
                [
                    'key'   => 'transaksi.nota',
                    'label' => 'Daftar Nota',
                    'url'   => 'nota',
                    'icon'  => 'bi bi-file-text',
                ],
                [
                    'key'   => 'transaksi.pembayaran',
                    'label' => 'Pembayaran',
                    'url'   => 'pembayaran',
                    'icon'  => 'bi bi-credit-card',
                ],
            ],
        ],
    ];
}
```

Tambahkan key menu tersebut ke daftar `$modules` di `app/Config/CrbAkses.php`.
Pertahankan seluruh entri modul lain yang digunakan APP/CORE:

```php
public array $modules = [
    'home.dashboard'       => 'session',
    'transaksi.nota'       => ['admin', 'user'],
    'transaksi.pembayaran' => ['admin'],
    // Pertahankan juga key modul bawaan CORE dari CrbAkses.php.
];
```

Sidebar APP membaca `CrbMenu::$menuApp` dan memeriksa visibilitas item berdasarkan
`key` melalui `aksesMenu()`. Contoh pemanggilan helper untuk memeriksa satu
item dari view APP:

```php
if (aksesMenu('transaksi.nota')) {
    // Tampilkan elemen navigasi untuk Daftar Nota.
}
```

Helper menggunakan kebijakan yang sama dengan filter route. Ia mengatur
visibilitas tautan saja; otorisasi request tetap dilakukan filter. Contoh
pendaftaran route APP:

```php
$routes->get('nota', 'Nota::index', ['filter' => 'crbAkses:transaksi.nota']);
```

`crbAkses` didaftarkan sebagai alias filter pada `app/Config/Filters.php`
hasil publish. Filter memeriksa `CrbAkses::$modules` menggunakan key yang
diberikan. Untuk nilai `'session'`, user anonim diarahkan ke login Shield;
setelah login, semua group lolos kebijakan tersebut.

## 4. Helper `aksesMenu()`

`aksesMenu()` menentukan apakah item menu ditampilkan dengan memeriksa aturan
modul yang sama dengan filter route.

- Pemanggilan default membaca metadata APP dari `CrbMenu::$menuApp`.
- Untuk metadata menu internal CORE, helper dipanggil dengan sumber `core`.
- Key yang tidak ditemukan tidak ditampilkan.
- Kebijakan key modul dibaca dari `CrbAkses::$modules`.
- Daftar group kosong (`[]`) berarti item terlihat untuk semua pengunjung.
- Kebijakan `'session'` berarti item terlihat untuk semua user yang sudah login.
- Daftar group berisi nama group berarti item hanya terlihat bagi user dari
  salah satu group tersebut.
- Key kebijakan yang tidak terdaftar merupakan kesalahan konfigurasi.

Contoh penggunaan:

```php
aksesMenu('vendor.user-list', 'core'); // mengikuti kebijakan di CrbAkses::$modules
aksesMenu('transaksi.nota');           // menu APP; key harus ada di CrbMenu::$menuApp
```

Gunakan argumen sumber `'core'` untuk memeriksa item dari konfigurasi internal
`CoreMenu`. Kedua contoh memakai aturan masing-masing pada `CrbAkses::$modules`.

`aksesMenu()` hanya mengatur tampilan. URL dilindungi oleh filter
`crbAkses`, yang memakai key dan kebijakan yang sama.

### Judul halaman dan URL endpoint dari CrbMenu

Layout mencari item menu yang URL-nya cocok dengan halaman yang sedang dibuka.
Label item digunakan sebagai judul halaman dan judul tab browser. Controller
tidak perlu mengirim `pageTitle` jika URL halaman tersedia pada `CrbMenu`.
`pageTitle` tetap dapat menjadi fallback untuk halaman yang tidak mempunyai
item menu, seperti profil.

Layout juga menyediakan `CrbMenu.url(key, endpoint)` di JavaScript untuk
mengambil URL item dan menambahkan path endpoint:

```javascript
CirebonwebForm.Fetch(CrbMenu.url("vendor.user-list", "get-id"), data, "POST", {});
```

Key harus menunjuk item dengan URL. Endpoint tetap harus didaftarkan di
`Routes.php` dan memakai filter akses yang sesuai.

## 5. Route dan keamanan

CORE menyediakan route melalui `CrbRoutes`. Routes hasil publish mendaftarkan
filter `crbAkses` untuk setiap grup route CORE. Filter dan menu membaca
key yang sama dari `CrbAkses::$modules`.

```php
$routes->get('/', 'Home::index', ['filter' => 'crbAkses:home.dashboard']);
$crb->userList()->routes($routes, ['filter' => 'crbAkses:vendor.user-list']);
```

Saat APP mendaftarkan route tambahan, gunakan alias filter yang sama dengan
key modul sesuai `CrbAkses::$modules`. Grup route CORE menerapkan filter pada
semua endpoint dalam grup tersebut, termasuk endpoint AJAX/API. Pastikan semua
route nonpublik memakai filter `crbAkses` atau filter keamanan APP lain
yang sesuai.

## 6. Cakupan hak akses

Hak akses tahap ini berfokus pada route/module-level. Tidak ada sistem
permission CRUD atau permission per aksi. APP mengatur group modul melalui
`CrbAkses::$modules`.

CORE tidak memaksakan bypass global `superadmin`. APP dapat mencantumkan
`superadmin` pada daftar group tiap modul bila akses diperlukan. Aturan
superadmin pada User List CORE hanya berlaku untuk pemberian group dan bukan
bypass keamanan route.

## 7. Data dan sinkronisasi

Tidak ada database atau relasi untuk role → menu maupun permission → menu.
Tidak diperlukan tabel, migration, seeder, proses sinkronisasi menu, atau
tombol sinkronisasi. APP mendefinisikan group per modul pada
`app/Config/CrbAkses.php`; helper menu dan filter route membaca konfigurasi ini.

## 8. Ringkasan keputusan

| Area | Keputusan |
| --- | --- |
| Tanggung jawab | CORE menyediakan capability; APP menentukan policy dan security |
| Menu APP dan override menu CORE | `app/Config/CrbMenu.php` |
| Kebijakan akses modul | `app/Config/CrbAkses.php` |
| Menu bawaan CORE | Konfigurasi internal CORE; otomatis digunakan |
| Override menu CORE | APP dapat mengubah label dan ikon; key dan URL tetap |
| Menu internal CORE tambahan | Konfigurasi internal CORE; dirender oleh `sidebar_vendor.php` |
| Dispatcher | Dapat menggabungkan menu APP dan CORE |
| Visibilitas dan filter route | Memakai key modul dan `CrbAkses::$modules` yang sama |
| Daftar group kosong (`[]`) | Modul publik |
| Kebijakan `session` | Semua group dapat mengakses setelah login Shield |
| Key kebijakan tidak ada | Kesalahan konfigurasi; tidak ada akses yang diberikan |
| Route nonpublik | Dilindungi `crbAkses` atau filter keamanan APP lain, termasuk endpoint AJAX/API |
| Group user | Satu group per user |
| Group default | `superadmin`, `admin`, `user`, `developer`, `beta`; registrasi `user` |
| User List CORE | `superadmin` memilih group `AuthGroups`; admin/developer hanya `user` dan tidak mengubah target istimewa |
| Role → menu | Tidak disimpan di database |
| Sinkronisasi menu | Tidak ada |
| Permission CRUD/action | Tidak termasuk tahap ini |
| Bypass global `superadmin` | Tidak dipaksakan CORE |

## 9. Kriteria kesesuaian implementasi

- CORE menyediakan mekanisme filter; policy modul ditentukan APP pada `CrbAkses.php`.
- `aksesMenu()` dan filter route membaca sumber kebijakan yang sama.
- `CrbMenu` tidak menjadi sumber mapping group → menu/security.
- Tidak ada database, migration, seeder, atau sinkronisasi role/menu.
- Tidak ada permission CRUD/action maupun bypass global `superadmin`.
- Validasi pemberian group di User List tetap dilakukan server-side.
- Menu APP dan menu internal CORE tetap dapat ditampilkan melalui dispatcher sidebar.

## 10. Keputusan: label role dari AuthGroups (manual)

Status: disepakati. Tidak ada modul UI `vendor/user-role` dan tidak ada
override label role via settings/database.

- `app/Config/AuthGroups.php` dan `app/Config/CrbAkses.php` adalah konfigurasi
  milik APP dan dikelola manual.
- **Key role adalah identitas fungsi**: dipakai di `auth_groups_users`,
  `CrbAkses::$modules`, filter route, `inGroup()`, dan logika lain. Key tidak
  di-rename.
- **Title adalah label tampilan**: User List menampilkan `title` dari
  `AuthGroups` (mis. key `superadmin` dapat diberi title `Owner`), tetapi value
  yang dikirim/disimpan tetap key role.
- Penambahan/penyesuaian role custom dilakukan manual di `AuthGroups.php` dan,
  bila perlu akses modul, dipetakan manual di `CrbAkses.php`. Karena perubahan
  role jarang, alur manual ini diterima.
