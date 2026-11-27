# Modul Profil — Konsep dan Override APP

Dokumen konsep modul Profil CORE. Fokusnya bukan fitur per fitur, tetapi **batas
tanggung jawab CORE ↔ APP** dan cara APP menambah atau mengubah halaman Profil
tanpa menyalin controller.

Lihat juga: [`Konsep.md`](Konsep.md) (indeks modul) dan
[`modul-hak-akses.md`](modul-hak-akses.md) (policy route `profil`).

## Pembagian tanggung jawab

| Bagian | Pemilik | Lokasi |
| --- | --- | --- |
| Controller, model, route, aturan validasi dasar, hook | CORE | `src/Controllers/Profil.php`, `src/Models/UserModel.php`, `src/Models/UserProfilModel.php`, `src/Config/CrbRoutes.php::profil()` |
| Template, view, JS, aset | APP (hasil publish) | `app/Views/profil.php`, `public/vendor/js/page_profil.min.js`, `public/vendor/js/helper_upload_single.min.js` |
| Data tambahan, perilaku tambahan, endpoint tambahan | APP | `app/Controllers/Profil.php` |

Prinsip:

```text
CORE  = perilaku baku + titik sisip (hook) untuk APP
APP   = menentukan data dan kebijakan proyek
APP TIDAK menyalin isi controller CORE.
```

Alasan larangan menyalin: perbaikan bug di controller CORE (mis. penanganan
`id` profil yang tidak valid) tidak akan pernah sampai ke APP jika seluruh isi
controller dipindahkan ke APP.

## Konsekuensi namespace route CORE

`CrbRoutes::routes()` menambahkan `'namespace' => 'Cirebonweb\Controllers'`
secara default. Artinya route `profil` **selalu** menunjuk
`Cirebonweb\Controllers\Profil`, walaupun APP membuat class dengan nama yang
sama. Class `App\Controllers\Profil` tidak akan terpanggil kecuali namespace
grup route diubah oleh APP.

Opsi `namespace` sudah diteruskan `routes(RouteCollection $routes, array $options)`
ke `RouteCollection::group()`, sehingga APP cukup menggantinya:

```php
// app/Config/Routes.php
$crb->profil()->routes($routes, [
    'namespace' => 'App\\Controllers',
    'filter'    => 'crbAkses:profil',
]);
```

Semua endpoint grup (`profil`, `profil/update-foto`, `profil/update-akun`,
`profil/update-info`, `profil/get-perangkat`, `profil/logout-perangkat`)
langsung diarahkan ke controller APP. Tidak ada route yang perlu ditulis ulang,
sehingga tidak ada risiko satu endpoint tertinggal di CORE.

Jika APP tidak membutuhkan controller sendiri, gunakan bentuk bawaan:

```php
$crb->profil()->routes($routes, ['filter' => 'crbAkses:profil']);
```

## Kontrak yang harus dijaga CORE

1. **Method aksi harus `public`.** CI4 menolak method non-public sebagai target
   route (`CodeIgniter::startController()` memakai
   `is_callable([$controller, $method], false)`). Karena itu
   `index()`, `getPerangkat()`, `logoutPerangkat()`, `updateFoto()`,
   `updateAkun()`, dan `updateInfo()` di controller CORE tidak boleh diubah
   menjadi `protected`. Method `public` pada induk tetap dapat di-override oleh
   subclass APP dengan visibilitas yang sama.
2. **Hook tersedia dan boleh kosong.** Hook hanya memberi titik sisip; perilaku
   bawaan tidak bergantung padanya.
3. **Nama view dan variabel view baku dipertahankan** agar view hasil publish
   tetap jalan tanpa controller APP: `url`, `pageTitle`, `navigasi`, `userId`,
   `user`, `foto`, `tabPane`.

## Hook yang disediakan CORE

| Hook | Dipanggil dari | Kegunaan |
| --- | --- | --- |
| `profilData($user, $userId): array` | `index()` sebelum render | Menambahkan variabel view baru (mis. data dari tabel lain) |
| `profilView(): string` | `index()` saat render | Mengganti nama view halaman profil |
| `infoValidationRules(): array` | `updateInfo()` | Menambah aturan validasi untuk kolom tambahan tab Info |

`profilData()` digabung dengan `array_merge` ke data bawaan, sehingga variabel
CORE tidak hilang bila APP tidak menimpanya. `infoValidationRules()` digabung
dengan aturan model sebelum data POST diambil, jadi field tambahan otomatis
ikut tervalidasi **dan** ikut terkirim ke `UserProfilModel::update()`.

## Tiga tingkat perubahan di APP

### 1. Hanya menambah data — tanpa menulis logika controller

Cukup isi hook pada subclass:

```php
// app/Controllers/Profil.php
namespace App\Controllers;

use Cirebonweb\Controllers\Profil as ProfilCore;

class Profil extends ProfilCore
{
    protected function profilData($user, $userId): array
    {
        return ['kota' => 'Cirebon'];
    }
}
```

Variabel `$kota` tersedia di `app/Views/profil.php`. Route APP diaktifkan
lewat `namespace` seperti bagian sebelumnya.

Prasyarat bila data disimpan ke tabel: kolom tersedia (migrasi APP) dan kolom
terdaftar di `$allowedFields` model APP yang memakai tabel `user_profil`.

### 2. Mengubah sebagian perilaku — override method

```php
public function updateInfo()
{
    $kota = $this->request->getPost('kota');
    if ($kota !== null && mb_strlen(trim($kota)) > 50) {
        return $this->response->setJSON([
            'success'  => false,
            'messages' => ['kota' => 'Kota maksimal 50 karakter.'],
        ]);
    }

    return parent::updateInfo();
}
```

Gunakan `parent::` bila hanya menambah pemeriksaan atau mengubah respons.
Tulis ulang body method hanya bila perilaku CORE memang harus diganti
sepenuhnya, dan catat alasan penggantian pada APP.

### 3. Menambah endpoint atau halaman

Tambahkan method `public` baru di `App\Controllers\Profil` dan daftarkan route
sendiri di `app/Config/Routes.php` dengan filter kebijakan yang sesuai
(`crbAkses:profil` atau key modul baru):

```php
$routes->post('profil/simpan-kota', 'Profil::simpanKota', ['filter' => 'crbAkses:profil']);
```

## Pengaturan profil user lain

Pengguna dapat melihat dan mengubah profilnya sendiri. Untuk melihat atau
mengubah profil pengguna lain, ia harus lolos policy `vendor.user-list` pada
`CrbAkses::$modules`. Pemeriksaan ini berlaku pada controller untuk tampilan
profil serta endpoint ubah foto, akun, dan informasi; menyembunyikan elemen UI
saja tidak dianggap sebagai kontrol akses.

`index()` menerima parameter `?id=` berupa ID terenkripsi untuk menentukan
profil yang diminta. Enkripsi hanya mengaburkan ID dan bukan pengganti
otorisasi. Bila parameter gagal didekripsi atau bukan angka, pengguna yang
login dipakai sebagai gantinya. Bila data profil tidak ditemukan, request
dialihkan ke `/` dengan flashdata `error`. View menampilkan tab Sesi hanya bagi
pemilik profil.

Endpoint perubahan juga memastikan `profil_id` terkait dengan `user_id` yang
dituju; ID yang dikirim klien tidak dipercaya sebagai bukti kepemilikan.

Override controller tidak boleh melemahkan pemeriksaan ini tanpa keputusan
kebijakan APP yang eksplisit.

## Sinkronisasi APP

- Controller, model, route, dan hook dipakai langsung dari paket
  (`vendor/cirebonweb/core`); pembaruan CORE tidak perlu disalin ke APP.
- View `app/Views/profil.php` dan aset di `public/vendor/js/` adalah hasil
  publish/penyaluran manual. Perubahan CORE pada file itu perlu diselaraskan
  manual dan **tidak** ditimpa otomatis oleh `cirebonweb:publish` bila APP sudah
  mengubahnya.
- `App\Controllers\Profil.php` (sumber: `src/Publish/Controllers/Profil.php`)
  hanya disalin sekali saat instalasi. Setelah itu milik APP.

## Batasan yang diketahui

- Hook `profilData()` mengirim data ke view saja; penyimpanan data tambahan
  sepenuhnya tanggung jawab APP (model dan migrasinya).
