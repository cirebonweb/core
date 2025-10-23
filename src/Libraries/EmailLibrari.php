<?php

namespace Cirebonweb\Libraries;

use CodeIgniter\Config\Services;
use CodeIgniter\Email\Email;
use Exception;

class EmailLibrari
{
    // Mengubah $cacheTime menjadi public agar bisa di-reset dari luar (Controller/Setting)
    public static int $cacheTime = 0;
    protected static int $ttl = 600; // auto-refresh setiap 10 menit

    protected static array $cache = [
        'config_hash' => null, // Hash penuh dari semua konfigurasi kunci
        'pass'        => null, // Password terdekripsi
        'email'       => null, // Instance objek Email
    ];

    /**
     * Menginisialisasi Service Email dengan konfigurasi dinamis dari database.
     * Menggunakan Static Cache untuk menghindari dekripsi berulang dan inisialisasi objek.
     *
     * @return Email instance
     */
    protected function configEmail(): Email
    {
        // 1. Ambil data konfigurasi kunci dari setting()
        $encryptedPass  = setting('App.smtpPass') ?? '';
        $smtpProtocol   = setting('App.smtpProtocol') ?? '';
        $smtpHost       = setting('App.smtpHost') ?? '';
        $smtpUser       = setting('App.smtpUser') ?? '';

        // Gabungkan semua konfigurasi penting ke dalam satu string untuk di-hash
        $configString = $encryptedPass . $smtpProtocol . $smtpHost . $smtpUser;
        $currentHash  = md5($configString);

        // --- Mekanisme Refresh Cache ---
        $isExpired = time() - self::$cacheTime > self::$ttl;
        $isConfigChanged = self::$cache['config_hash'] !== $currentHash;

        // Jika TTL habis ATAU konfigurasi kunci (termasuk password) jika berubah
        if ($isExpired || $isConfigChanged) {
            // Reset cache
            self::$cache['email'] = null;
            self::$cacheTime = time();

            // HANYA DEKRIPSI jika password berubah atau cache expired
            $smtpPassAsli = '';
            if (! empty($encryptedPass)) {
                try {
                    $encrypter = Services::encrypter();
                    $smtpPassAsli = $encrypter->decrypt(base64_decode($encryptedPass));
                } catch (\Exception $e) {
                    log_message('error', 'Gagal mendekripsi SMTP Password: ' . $e->getMessage());
                }
            }

            // Simpan hash dan password baru
            self::$cache['config_hash'] = $currentHash;
            self::$cache['pass'] = $smtpPassAsli;
        }

        // --- Inisialisasi Objek Email ---
        if (! self::$cache['email'] instanceof Email) {
            
            // Konfigurasi Lengkap dari Database
            $config = [
                'protocol'   => $smtpProtocol,
                'SMTPHost'   => $smtpHost,
                'SMTPUser'   => $smtpUser,
                'SMTPPass'   => self::$cache['pass'], // Menggunakan password yang sudah terdekripsi dari cache
                'SMTPPort'   => (int) setting('App.smtpPort'),
                'SMTPCrypto' => setting('App.smtpCrypto') ?? '',
                'mailType'   => 'html',
                'charset'    => 'UTF-8',
                'CRLF'       => "\r\n",
                'newline'    => "\r\n",
            ];

            // Konfigurasi PaperCut/Development
            $paperCut = [
                'protocol'   => 'smtp',
                'SMTPHost'   => '127.0.0.1',
                'SMTPUser'   => '',
                'SMTPPass'   => '',
                'SMTPPort'   => 25,
                'SMTPCrypto' => '',
                'mailType'   => 'html',
                'charset'    => 'UTF-8',
                'CRLF'       => "\r\n",
                'newline'    => "\r\n",
            ];
            
            // Pilihan konfigurasi (Switch berdasarkan Dev dan Prod)
            // Untuk menentukan kapan menggunakan $config atau $paperCut di sini
            $isDevelopment = CI_ENVIRONMENT === 'development';

            $email = Services::email($isDevelopment ? $paperCut : $config);
            $email->setFrom(setting('App.smtpEmail') ?? '', setting('App.smtpNama') ?? '');

            self::$cache['email'] = $email;
        }

        return self::$cache['email'];
    }

    /**
     * Fungsi utama untuk pengiriman email generik (master).
     * Digunakan untuk semua jenis email (manual, otomatis (auto reminder), sistem Shield, dll).
     *
     * @param string $admin    = Nama admin atau sistem yang mengirim email
     * @param string $tipe     = Tipe email (manual, otomatis, notifikasi)
     * @param string $template = Nama template email yang digunakan (standar, invoice)
     * @param string $email    = Tujuan email
     * @param string $judul    = Subjek email
     * @param string $body     = Konten pesan dan template email (default HTML)
     * @param ?array $lampiran = Daftar file path (opsional)
     * @return bool True jika terkirim, false jika gagal
     */
    public function kirimEmail(string $admin, string $tipe, string $template, string $email, string $judul, string $body, ?array $lampiran = null): bool
    {
        $awalRender = microtime(true);
        $status     = 0; // gagal
        $pesanError = null;
        $result     = false;

        $emailConfig = $this->configEmail();

        try {
            $emailConfig->setTo($email);
            $emailConfig->setSubject('[' . setting('App.siteNama') . '] ' . $judul);
            $emailConfig->setMessage($body);

            // === Proses lampiran kalau ada ===
            if (! empty($lampiran)) {
                foreach ($lampiran as $file) {
                    if (is_file($file)) {
                        $emailConfig->attach($file);
                    } else {
                        log_message('warning', "Lampiran tidak ditemukan: {$file}");
                    }
                }
            }

            // false = Jangan tampilkan debug info secara otomatis -> $emailConfig->printDebugger()
            $result = $emailConfig->send(false);

            if ($result) {
                $status = 1; //'berhasil';
            } else {
                $pesanError = $emailConfig->printDebugger(['headers']);
                log_message('error', "Gagal mengirim email ke {$email}: {$pesanError}");
            }
        } catch (Exception $e) {
            $pesanError = $e->getMessage();
            log_message('critical', 'Kesalahan Kritis kirimEmail: ' . $pesanError);
        } finally {
            $render = microtime(true) - $awalRender;
            $this->logEmail($admin, $tipe, $template, $email, $judul, $render, $status, $pesanError);
        }

        $emailConfig->clear();
        return $result;
    }

    /**
     * Menyimpan status pengiriman email ke tabel log_email.
     *
     * Fungsi ini tidak mempengaruhi hasil pengiriman email utama.
     * Jika proses insert log gagal, error akan dicatat ke file log CodeIgniter,
     * tetapi tidak menghentikan alur program.
     *
     * @param string  $admin       = Nama admin atau sistem yang mengirim email
     * @param string  $tipe        = Tipe email (manual, otomatis, notifikasi)
     * @param string  $template    = Nama template email yang digunakan (standar, invoice)
     * @param string  $emailUser   = Alamat email penerima
     * @param string  $judulEmail  = Judul atau subject email
     * @param float   $waktuRender = Lama waktu proses pengiriman (dalam detik, misal 1.23)
     * @param int     $statusKirim = Status pengiriman email (0 = gagal, 1 = berhasil)
     * @param ?string $pesanError  = Pesan error jika terjadi kegagalan (opsional)
     *
     * @return bool True jika log berhasil disimpan, False jika gagal insert ke database
     */
    protected function logEmail(string $admin, string $tipe, string $template, string $emailUser, string $judulEmail, float $waktuRender, int $statusKirim, ?string $pesanError = null): bool
    {
        $data = [
            'admin'    => $admin,
            'tipe'     => $tipe,
            'template' => $template,
            'email'    => $emailUser,
            'judul'    => $judulEmail,
            'render'   => number_format($waktuRender, 2),
            'status'   => $statusKirim,
            'error'    => $pesanError ?: null,
            'dibuat'   => date('Y-m-d H:i:s')
        ];

        try {
            model('Log/LogEmailModel')->insert($data);
            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Gagal insert LogEmailModel: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pengiriman email standar dengan template tetap dan lampiran opsional.
     *
     * @param string $admin    = Nama admin atau sistem yang mengirim email
     * @param string $tipe     = Tipe email (manual, otomatis, notifikasi)
     * @param string $email    = Penerima email
     * @param string $judul    = Judul email
     * @param string $pesan    = Isi pesan email (konten sederhana)
     * @param ?array $lampiran = Daftar path file untuk dilampirkan
     * @return bool True jika email berhasil dikirim, False jika gagal
     */
    public function emailStandar(string $admin, string $tipe, string $email, string $judul, string $pesan, ?array $lampiran = null): bool
    {
        $body = view('Cirebonweb\email\standar', [
            'judul' => $judul,
            'pesan' => $pesan,
        ], ['saveData' => true]);

        return $this->kirimEmail($admin, $tipe, 'standar', $email, $judul, $body, $lampiran);
    }

    /**
     * Pengiriman email khusus untuk invoice.
     *
     * @param string $admin      = Nama admin atau sistem yang mengirim email
     * @param string $tipe       = Tipe email (standar, invoice, dll)
     * @param string $email      = Penerima email
     * @param string $judul      = Judul email (Invoice #INV-00123")
     * @param array $dataInvoice = Data yang digunakan untuk template email (nama, total, dll)
     * @param ?array $lampiran   = Path ke file PDF invoice (jika ada)
     * @return bool True jika terkirim, false jika gagal
     */
    public function emailInvoice(string $admin, string $tipe, string $email, string $judul, array $dataInvoice, ?array $lampiran = null): bool
    {
        $body = view('Cirebonweb\email\invoice', [
            'judul'   => $judul,
            'nama'    => $dataInvoice['nama'] ?? '-',
            'nomor'   => $dataInvoice['nomor'] ?? '-',
            'tanggal' => $dataInvoice['tanggal'] ?? date('d-m-Y'),
            'total'   => number_format($dataInvoice['total'] ?? 0, 0, ',', '.'),
        ], ['saveData' => true]);

        $lampiranList = $lampiran ? [$lampiran] : [];

        return $this->kirimEmail($admin, $tipe, 'invoice', $email, $judul, $body, $lampiranList);
    }
}