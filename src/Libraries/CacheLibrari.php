<?php

namespace Cirebonweb\Libraries;

/**
 * Sumber durasi TTL untuk cache core.
 *
 * Nilai runtime disimpan di `writable/json/crb_cache.json` supaya bisa diubah
 * dari halaman `vendor/setting-cache` tanpa menyentuh kode. Nilai awal diambil
 * dari `Cirebonweb\Config\Crb::$cache`, jadi project tetap bisa meng-override
 * lewat `Config\Crb`.
 *
 * Menggantikan helper `crb_cache` yang sebelumnya menyimpan konstanta TTL
 * langsung di dalam file global.
 */
class CacheLibrari
{
    /** TTL saat ini untuk setiap key, dibaca dari file JSON. */
    private ?array $durasi = null;

    /**
     * Lokasi file JSON. Folder dibuat bila belum ada.
     */
    public function lokasi(): string
    {
        $folder = WRITEPATH . 'json';

        if (! is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        return $folder . '/crb_cache.json';
    }

    /**
     * Semua durasi cache, digabung dengan default dari config.
     *
     * @return array<string, int>
     */
    public function durasi(): array
    {
        if ($this->durasi !== null) {
            return $this->durasi;
        }

        $config = config('Crb')->cache;

        $this->durasi = $config['items'] + ['default' => $config['default']];

        $file = $this->lokasi();

        if (is_file($file)) {
            $json = json_decode((string) file_get_contents($file), true);

            if (is_array($json)) {
                // Nilai di file menang, default config tetap tersedia sebagai cadangan.
                $this->durasi = $json + $this->durasi;
            }
        }

        return $this->durasi;
    }

    /**
     * TTL untuk satu key, dalam detik.
     *
     * @param string $key     Nama cache, mis. `statistik_user_list`
     * @param int    $default Dipakai bila key tidak dikenal
     */
    public function ttl(string $key, int $default = 0): int
    {
        $durasi = $this->durasi();

        return isset($durasi[$key]) ? (int) $durasi[$key] : $default;
    }

    /**
     * Simpan durasi baru. Hanya key yang dikirim yang berubah.
     *
     * @param array<string, int|string> $values
     */
    public function simpan(array $values): bool
    {
        $this->durasi = null; // bust cache in-memory

        $data = [];

        foreach ($values as $key => $value) {
            // Cegah key asing masuk ke file.
            if (in_array($key, $this->knownKeys(), true)) {
                $data[$key] = (int) $value;
            }
        }

        if ($data === []) {
            return false;
        }

        return file_put_contents(
            $this->lokasi(),
            json_encode($this->durasi() + $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        ) !== false;
    }

    /**
     * Key yang boleh disimpan dari form. Mencegah input tak terduga jadi key baru.
     *
     * @return list<string>
     */
    public function knownKeys(): array
    {
        return array_keys($this->durasi());
    }
}
