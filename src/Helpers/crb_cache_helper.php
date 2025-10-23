<?php

/**
 * helper('crb_cache')
 * Berfungsi untuk konfigurasi durasi cache.
 * Perubahan ini dapat dilakukan pada halaman setting cache.
 */

if (! function_exists('getLokasiCache')) {
    /**
     * Mengembalikan lokasi file JSON untuk konfigurasi cache.
     *
     * @return string
     */
    function getLokasiCache(): string
    {
        if (!is_dir(WRITEPATH . 'json')) {
            mkdir(WRITEPATH . 'json', 0755, true);
        }
        return WRITEPATH . 'json/crb_cache.json';
    }
}

if (! function_exists('loadDurasiCache')) {
    /**
     * Membaca file writable/json/crb_cache.json
     * dan mengembalikan array durasi cache.
     *
     * @return array
     */
    function loadDurasiCache(): array
    {
        $lokasiJson = getLokasiCache();

        // Default value jika file belum ada 3600 = 1 jam
        $defaults = [
            'cache_geoip' => 900,
            'cache_device' => 900,
            'statistik_user_list' => 3600,
            'statistik_user_login' => 3600,
            'statistik_log_email' => 3600
        ];

        if (is_file($lokasiJson)) {
            $json = json_decode(file_get_contents($lokasiJson), true);
            if (is_array($json)) {
                // Merge supaya default tetap ada kalau key baru belum diset
                return array_merge($defaults, $json);
            }
        }
        return $defaults;
    }
}

if (! function_exists('getDurasiCache')) {
    /**
     * Ambil durasi cache untuk key tertentu
     *
     * @param string $key
     * @param int    $default
     * @return int
     */
    function getDurasiCache(string $key, int $default = 0): int
    {
        $all = loadDurasiCache();
        return isset($all[$key]) ? (int) $all[$key] : $default;
    }
}