# Arsitektur — cirebonweb/core

> Tujuan paket core, batas antara **fitur kunci** (diurus core) dan **fitur fleksibel** (boleh diganti app),
> serta kebijakan publish. Peta dokumen:
>
> | Dokumen | Isi |
> |---|---|
> | `ARSITEKTUR.md` (file ini) | Aturan desain & kebijakan publish — **acuan utama**. |
> | `COMMAND.md` | Tabel publish per grup + struktur file hasil publish. |
> | `src/Dokumen/Konsep.md` | Indeks modul CORE dan sumber ringkasan konsep. |
> | `src/Dokumen/modul-*.md` | Rincian konsep modul yang sudah dibahas. |
> | `README.md` | Penjelasan fitur & cara pakai paket (untuk konsumen, terpisah dari dokumen internal). |

---

## 1. Dua kebutuhan pemilik paket

Pemilik core memakai paket ini untuk dua hal berbeda, dan keduanya tidak boleh saling menimpa:

| # | Kebutuhan | Frekuensi | Konsekuensi desain |
|---|---|---|---|
| **1** | **Bootstrap proyek baru** — proyek baru tidak perlu setup `app/Config/*`, template AdminLTE, aset, filter satu per satu. | Sekali seumur proyek | File bootstrap tertentu di-publish ke `app/` dan `public/`. Setelah itu **tidak boleh** di-publish lagi. |
| **2** | **Update core terjadwal** — fitur `setting umum`, `optimasi`, `log email`, `user login` dsb. diperbarui berkala dan harus sampai ke semua proyek **tanpa menimpa pekerjaan app**. | Rutin, seumur paket | Fitur core **tidak boleh** hidup di `app/`. Harus dibaca langsung dari paket. |

Kebutuhan 1 dan 2 dulunya saling bertabrakan karena file yang sama diletakkan di dua tempat (`src/Views/` **dan** `src/Publish/Views/`). Penyelesaiannya adalah **satu file, satu tempat** — lihat §3.

---

## 2. Prinsip: pisahkan "kunci" dan "fleksibel"

Klasifikasi konten paket, dasar untuk semua keputusan berikutnya:

| Kategori | Definisi | Lokasi | Bisa diubah app? |
|---|---|---|---|
| **Fitur kunci** (core) | Persis sama di semua proyek, dan jelas miliknya core: auth, setting, cache, optimasi, log email, user & user-login, statistik, profil, layout, email template, helper, library, filter. | `src/` — dibaca via namespace `Cirebonweb\` | Tidak. Update core otomatis sampai ke semua proyek. |
| **Fleksibel** (app) | Tergantung proyek: dashboard, halaman CRUD produk, konfigurasi `.env`, nama database, daftar menu. | `app/` — milik app | Ya. Ini memang tugas app. |
| **Sambungan** (bootstrap) | Boilerplate yang app butuh agar core bisa hidup, tapi setelah di-publish menjadi milik app: `Config/Autoload.php`, `Config/App.php`, `Config/Auth*.php`, `Config/Filters.php`, `Config/Session.php`, aset `public/`. | `src/Publish/` → disalin sekali ke `app/` | Ya — itu sebabnya di-publish. |

### Kenapa `*_vendor` masih dipakai

`*_vendor` bukan sekadar penanda visual, tapi **mekanisme anti-timpa**:

- `src/Views/layout/navbar_vendor.php` — konten core, nama diberi `_vendor` karena **bukan** untuk di-publish, dan karena iaInject di dalam wrapper yang milik app.
- `public/vendor/js/…` — aset helper JS milik core, dipisah dari aset app supaya jelas siapa pemiliknya dan aman saat publish ulang.

Dispatcher `layout/navbar.php` dan `layout/sidebar.php` menggabungkan keduanya:

```
app/Views/layout/navbar_menu.php     (milik app, opsional)   ← ditulis lebih dulu
src/Views/layout/navbar_vendor.php   (milik core, selalu ada)  ← ditulis setelahnya
```

---

## 3. Aturan namespace view —WAJIB dibaca

Dua hal ini sering tertukar dan keduanya sudah terbukti secara empiris:

1. **Memanggil view core wajib pakai prefix `Cirebonweb\`.**
   `$this->include('layout/navbar')` → error `Berkas tidak valid: "layout/navbar.php"`.
   Yang benar: `$this->include('Cirebonweb\layout\navbar')`.

2. **`locator->search()` justru TIDAK memakai prefix.**
   Path-nya relatif terhadap tiap namespace, dan namespace `Cirebonweb` dipetakan ke `src/`, jadi path-nya berawalan `Views/`.
   Benar: `$locator->search('Views/layout/navbar_vendor.php')`.
   Salah: `search('layout/navbar_vendor.php')` dan `search('Cirebonweb/Views/…')`.

3. **Di dalam dispatcher, hasil `include()` harus di-`echo`.**
   `$this->include(...)` mengembalikan string; tanpa `echo` output-nya hilang diam-diam.

Dispatcher final:

```php
$locator = service('locator');

$views = [
    'layout/navbar_menu'              => 'Views/layout/navbar_menu.php',
    'Cirebonweb\layout\navbar_vendor' => 'Views/layout/navbar_vendor.php',
];

foreach ($views as $view => $file) {
    if ($locator->search($file) !== []) {
        echo $this->include($view);
    }
}
```

> Konsekuensi penting: **file core di `src/Views/` tidak pernah masuk `app/Views/`.**
> App yang ingin menimpa sebuah view core cukup membuat file dengan nama yang sama di `app/Views/`
> — CI4 memprioritaskan `App\` di atas `Cirebonweb\`.

---

## 4. Kebijakan publish

Perintah: `php spark cirebonweb:publish [--all|--config|--controllers|--views|--public|--writable] [--force] [--dry-run]`

| Flag | Sumber → tujuan | Kapan dipakai |
|---|---|---|
| `--config` | `src/Publish/Config` → `app/Config` | Sekali, saat setup awal. |
| `--controllers` | `src/Publish/Controllers` → `app/Controllers` | Sekali, termasuk `BaseController` untuk controller CORE serta contoh `Home` dan `Profil`. |
| `--views` | `src/Publish/Views` → `app/Views` | Sekali, untuk view bootstrap dan titik penyesuaian navigasi APP. |
| `--public` | `src/Publish/Public` → `public/` | Sekali. Aset statis. |
| `--writable` | `src/Publish/Writable` → `writable/` | Sekali. Runtime `json/crb_cache.json`. |
| `--force` | modifier | Menimpa file yang isinya sudah diedit app. Tanpa ini, file yang berubah **dilewati**. |

Migrasi CORE berada di `src/Database/Migrations` dan dijalankan langsung dari
paket dengan `php spark migrate --all`; migrasi tidak dipublish ke APP.

> **Khusus `.htaccess`:** `src/Publish/Public/.htaccess` ikut ter-publish lewat `--public`, tapi
> skeleton CodeIgniter sudah membuat `public/.htaccess` sehingga file dari core **dilewati** kalau
> isinya berbeda. Pakai `--force` sekali saat setup. Isinya aturan rewrite CI4 + blok `mod_deflate`
> & `mod_expires` untuk kompresi respons dan cache aset statis.

### Aturan keras

- `app/Config/Routes.php` disediakan sebagai contoh bootstrap APP melalui publish.
  File tersebut menunjukkan pendaftaran route dan contoh filter APP. APP
  bertanggung jawab menyesuaikan policy-nya; route CORE tanpa filter tetap publik.
  Routes bawaan skeleton CI4 diganti secara otomatis; route APP yang telah
  dikustomisasi tidak ditimpa tanpa `--force`.
- Grup publish menyalin seluruh isi folder sumber secara rekursif. Untuk `--public`, ini mencakup `plugin/`, `vendor/`, `upload/`, dan `.htaccess`; untuk `--views` dan `--controllers`, seluruh file sumber ikut disalin.
- Tanpa `--force`, file tujuan yang berbeda dilewati; `--force` menimpa salinan APP yang telah disesuaikan.
- Publish **tidak** pernah menyalin `src/Views/` (isi `Cirebonweb\`) ke `app/Views/`. Itu batas anti-timpa yang utama.

### Perilaku anti-timpa

Tanpa `--force`, file yang isinya sudah berbeda **tidak** ditimpa; laporan menandai `!` dan menghitungnya "dilewati".
Perbandingan memakai `filesize` + `hash_file('xxh128')` — bukan `filemtime`, karena hasil publish mempertahankan timestamp sumber sehingga perbandingan waktu selalu dianggap sama.
Pengecualian saat bootstrap: `Routes.php` dan `Home.php` bawaan skeleton CI4
dikenali lalu diganti dengan contoh CORE. Jika kedua file tersebut sudah
dikustomisasi, aturan anti-timpa biasa tetap berlaku.

---

### 4b. Dua aturan yang ditemukan lewat pengujian

#### Helper core wajib ber-namespace penuh

`helper()` di CI4 hanya mencari nama polos di `APPPATH/Helpers`. Helper core tinggal di paket, jadi harus ditulis dengan namespace:

```php
// app/Config/Autoload.php
public $helpers = [
    'auth',
    'setting',
    'Cirebonweb\crb_asset',   // bukan 'crb_asset'
    'Cirebonweb\crb_format',
];
```

Gejalanya kalau lupa: `Call to undefined function versi()` → HTTP 500 di **semua** halaman.

#### Seeder CORE tidak dipublish atau berjalan otomatis

Seeder CORE berada di `src/Database/Seeds/`, bukan di sumber daya publish.
Jalankan secara eksplisit menggunakan nama kelas penuh bila APP membutuhkannya.

Isinya:
- `SettingsSeeder` → mengisi tabel `settings` (nama situs, SMTP, logo, sosial).
- `UserSeeder` → **membuat** user login contoh `admin@mail.com` / `cirebon321`
  (group `superadmin`) dan `user@mail.com` / `cirebon321` (group `user`), serta
  baris `user_profil` untuk keduanya.

`UserSeeder` membuat akun contoh dengan kredensial tetap; tinjau dan ubah atau
hapus data tersebut sebelum menjalankan seeder di lingkungan produksi.

> Lokasi migrasi dan seeder serta cara pemanggilannya dicatat pada
> [`src/Dokumen/Konsep.md`](src/Dokumen/Konsep.md) bagian Publikasi dan Database.

---

## 4c. Aset publik

Perintah `--public` dan `--all` menyalin seluruh isi `src/Publish/Public/` ke
`public/`, termasuk aset AdminLTE, plugin, helper CORE, logo, dan `.htaccess`.
Tidak ada folder aset yang dikecualikan dari publish.

Pada instalasi awal, file sumber disalin otomatis. Jika file tujuan sudah ada
dengan isi berbeda, publisher melewatinya tanpa `--force`; gunakan `--force`
hanya bila memang ingin mengganti salinan APP yang sudah disesuaikan. Sumber
aset tetap dirawat di paket CORE, sementara salinan hasil publish berada di APP.

### Struktur `public/vendor/`

```
public/vendor/
├── css/
│   ├── adminlte.min.css
│   ├── custom_adminlte.min.css   # Custom milik core
│   ├── custom_sidebar.min.css
│   └── custom_tabel.min.css
├── js/
│   ├── adminlte.min.js           # AdminLte, dari pihak ketiga
│   ├── core.min.js               # Helper JS milik core (Lokasi, Beranda, Sidebar)
│   ├── helper_form.min.js        # Helper JS milik core
│   ├── helper_format.min.js
│   ├── helper_statistik.min.js
│   ├── helper_upload_single.min.js
│   ├── helper_upload_multi.min.js
│   └── page_profil.min.js
└── ... (aset vendor dan helper CORE lainnya)
```

> Semua referensi di template menggunakan `vendor/css/…` dan `vendor/js/…`.

### Struktur `public/plugin/` (hasil akhir)

```
public/plugin/
├── bootstrap/
│   ├── bootstrap.bundle.min.js      # dipakai template & shield/layout
│   ├── bootstrap.min.css            # dipakai template & shield/layout
│   └── icheck-bootstrap.min.css     # dipakai shield/layout (gaya checkbox icheck)
├── datatables/
│   ├── datatables.min.css           # dipakai Views/plugin/css_tabel.php
│   └── datatables.min.js            # tabel server-side: user_list, user_login, log_email, cache, optimasi
├── jquery/
│   ├── jquery.min.js                # jQuery 3.7.1 — template & shield/layout
│   ├── jquery.validate.min.js       # validasi form (shield login & magic-link)
│   ├── jquery.validate_id.min.js    # pesan validasi bahasa Indonesia
│   ├── additional-methods.min.js    # aturan validasi tambahan
│   ├── jquery-ui.min.css            # ⚠️ belum dipakai
│   ├── jquery-ui.min.js             # ⚠️ belum dipakai
│   └── images/                      # ⚠️ ikon tema jQuery UI (ikut jQuery UI)
├── pica/pica.min.js                 # resize gambar sebelum upload (profil, setting/umum)
├── select2/
│   ├── select2.min.css
│   ├── select2.min.js
│   └── i18n/{id,en}.js              # ⚠️ belum dipakai — disediakan untuk kebutuhan app
└── sweetalert/
    ├── sweetalert2.min.css
    └── sweetalert2.min.js
```

> Aset plugin dipublish bersama sumber CORE. Tinjau pemakaiannya sebelum
> menghapus atau menambahkannya.


---

## 4d. Perpindahan aset `dist/` → `vendor/`

Semua file `*.css` dan `*.js` yang sebelumnya di `dist/` sekarang sudah dipindahkan ke `vendor/`.
Tujuannya: **semua aset bersama di satu folder** (`public/vendor/`) sehingga jelas mana yang bawaan core dan mana yang milik pihak ketiga.

**Dampak:**
- Semua referensi di `template.php` sudah di-update: `dist/css/` → `vendor/css/`, `dist/js/` → `vendor/js/`.
- Helper `crb_asset_helper.php` (`versi()`) juga sudah di-update.
- Folder `dist/` di `src/Publish/Public/` **dihapus**.

---

## 4e. GeoIP & tabel `user_login` sudah dihapus sepenuhnya — [SELESAI]

Core tidak pernah memakai pelacakan lokasi. Paket `geoip2/maxmind` beserta fallback API
(`ipapi.co`, `geojs.io`) sudah dibuang, karena: menambah dependensi berat, rely ke layanan
pihak ketiga, dan lambat (HTTP timeout di setiap request login).

Yang dibuang bukan sekadar kolom, tapi **seluruh tabel `user_login`** — lihat migrasi
`2026-10-04-140000_HapusTabelUserLogin.php`. Riwayat login sekarang dibaca langsung
dari tabel bawaan Shield `auth_logins` (`ip_address`, `user_agent`, `date`, `success`,
`identifier`, `user_id`); kolom perangkat/OS/browser diturunkan saat pembacaan dengan
mem-parse `user_agent` via helper `crb_device` (di-cache per string UA memakai key
`device_<md5>` dengan TTL `cache_device`).

Yang ikut dibuang: `src/Helpers/crb_geoip_helper.php`, `src/Libraries/UserLoginLibrari.php`,
`src/Models/User/UserLoginModel.php`, migrasi lama `BuatUserLogin.php`, dan key
`cache_geoip` (dari `crb_cache.json` + halaman setting-cache).

Project yang tabelnya sudah ada perlu menjalankan migration `HapusTabelUserLogin`
(sekaligus membersihkan sisa `BuatUserLogin` lama yang sudah tidak diterbitkan).

> `composer.json` `require`: `php ^8.2`, `codeigniter4/framework ^4.7`,
> `codeigniter4/settings ^2.2`, dan `codeigniter4/shield ^1.4`.

## 4f. Saat mengedit `*.min.js` hati-hati

File JS core Though bernama `.min.js` tetap berupa kode terbaca. Dua jebakan yang sudah pernah terjadi:

1. **Penggantian string berulang** bisa menghasilkan `Lokasi.vendor()+Lokasi.vendor()+"…"`. Selalu cek hasilnya.
2. **String di dalam JS yang sudah di-escape** bisa menghasilkan `"path"",` — dengan kutip ganda menggantung.

Mandatory setelah mengubah JS mana pun: `node --check <file>`. Core tidak punya lint di CI, jadi ini satu-satunya
penjaga yangandal.

## 4g. Nama service core tidak boleh bentrok dengan CI4

`Config\Services` milik core me-override `Config\Services`. Menambahkan method bernama `cache()` akan
menghapus `service('cache')` bawaan CI4 (CacheInterface), dan ResponseCache langsung TypeError.

Aturannya: nama method core memakai prefix yang pasti tidak dipakai framework — contoh `cirebonweb()`,
`dropdown()`, `cacheTtl()`.

## 4h. Config dipakai sebagai sumber default, file JSON sebagai runtime

Durasi cache punya dua sumber:

```
Config\Crb::$cache['items']   ->  default (bisa di-override tiap project)
writable/json/crb_cache.json  ->  nilai runtime (menang, diisi dari halaman setting)
```

`Libraries\CacheLibrari` menggabungkan keduanya dan hanya menerima key yang sudah dikenal,
sehingga form yang disalin/diubah bebas tidak bisa menyuntik key baru ke file.

## 4i. Sesi perangkat (trial tahap satu)

Sesi memakai `DatabaseHandler` pada tabel `auth_sesi`. Kolom `timestamp` dikelola
oleh handler CI4 dan menjadi sumber pemeriksaan aktivitas/kedaluwarsa (2 jam)
menggunakan waktu database. Kolom `dibuat` disimpan dengan timezone APP untuk
tampilan pengguna; kolom ini tidak menentukan apakah sesi masih aktif.

`regenerateDestroy = false` dipertahankan untuk trial. Baris sesi lama tidak
langsung dihapus saat ID sesi diregenerasi; pembersihan dan dampaknya terhadap
hosting akan dievaluasi setelah perilaku di production teramati. Daftar profil
hanya menampilkan sesi yang masih berada dalam jendela kedaluwarsa.

---

## 5. Prefiks URL: `/vendor/`

Semua halaman bawaan core berada di bawah `/vendor/…`; halaman app berada langsung di root.
Path-nya **datar** (tidak bersarang) supaya ringkas dan mudah dibaca:

| Core | App |
|---|---|
| `/vendor/setting-umum` | `/produk`, `/transaksi`, `/laporan` |
| `/vendor/setting-cache` | — |
| `/vendor/setting-optimasi` | — |
| `/vendor/log-email` | — |
| `/vendor/user-list` | — |
| `/vendor/user-login` | — |
| `/vendor/statistik/*` (AJAX) | — |

Endpoint AJAX memakai bentuk `<halaman>/<aksi>`:
`vendor/setting-umum/simpan-situs`, `vendor/user-list/tabel`, `vendor/log-email/hapus`.

Tujuannya: dari URL saja sudah jelas mana core mana project.

### Jangan menulis path core secara hardcode

Di view PHP pakai `url_to()` / `site_url()`. Di JS, jangan hardcode path — pakai `Lokasi.vendor()`:

```php
// template.php menyuntik basis URL ke JS
const urlVendor = '<?= rtrim(site_url('vendor'), '/') ?>/';
```

```js
// core.min.js
vendor: function () { return typeof urlVendor === 'string' ? urlVendor : '/vendor/'; }

// page_*.min.js — WAJIB lewat Lokasi.vendor(), endpoint TIDAK boleh path relatif
CirebonwebStat({ endpoint: Lokasi.vendor() + 'statistik/user-list' })
CirebonwebForm.Fetch(Lokasi.vendor() + 'log-email/hapus', { ids: ids }, 'POST', {})
```

> Path relatif seperti `"log-email/hapus"` kelihatan benar, tapi resolve ke root bukan ke `/vendor/`.
> Gejalanya: endpoint kena 404 padahal halamannya sendiri 200. Sudah pernah terjadi di halaman ini.

---

## 6. Config: pola extendable

**CI4 mengabaikan namespace file config** dan membaca class aslinya. Karena itu file config core
selalu berada di namespace `Cirebonweb\Config\`, sehingga app bisa meng-extend-nya (pola Shield):

```php
namespace Config;

use CodeIgniter\Config\Services as BaseServices;
use Cirebonweb\Config\Crb as BaseCrb;

class Crb extends BaseCrb
{
    // override seperlunya
}
```

`config('Crb')` memuat: `cache` + method `cacheTtl()`, `crud` (`length`, `lengthMenu`, `searchDelay`),
`permission` (`defaultDeny`, `superGroup`, `superAll`).

---

## 7. Yang sengaja **tidak** dipublish (dan alasannya)

| File | Alasan |
|---|---|
| `src/Controllers/**` | Sudah dibaca via namespace `Cirebonweb\`. Menyalinnya berarti duplikasi dan versi basi. |
| `src/Views/**` | Sama — termasuk `layout/navbar.php`, `sidebar.php`, `*_vendor.php`. |
| `src/Models/**`, `src/Libraries/**`, `src/Helpers/**` | Semua dipanggil lewat autoload PSR-4 `Cirebonweb\` atau autoload helper. |
| `src/Database/Seeds/*` | Seeder tidak dipublish dan dijalankan secara eksplisit oleh APP. |

> `src/Publish/Controllers/Home.php` adalah contoh bootstrap yang ikut
> dipublish. File di `src/Controllers/` dan `src/Views/` tetap dimuat langsung
> dari paket melalui namespace `Cirebonweb\`.