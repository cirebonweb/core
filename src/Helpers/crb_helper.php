<?php

if (!function_exists('crb_asset')) {
    /**
     * Generate public asset URL dari folder public/
     * Contoh: crb_asset('page/beranda.min.js')
     * Output: http://localhost:8080/page/beranda.min.js
     */
    function crb_asset(string $path): string
    {
        return base_url(ltrim($path, '/'));
    }
}

if (!function_exists('crb_view')) {
    /**
     * Memanggil view dari package cirebonweb, dengan fallback ke app/Views.
     * Contoh: crb_view('plugin/validasi_js')
     */
    function crb_view(string $view, array $data = [], array $options = []): string
    {
        $paths = [
            APPPATH . 'Views/' . $view . '.php',
            ROOTPATH . 'vendor/cirebonweb/core/src/Views/' . $view . '.php',
        ];

        foreach ($paths as $file) {
            if (is_file($file)) {
                return view(str_replace([APPPATH . 'Views/', ROOTPATH . 'vendor/cirebonweb/core/src/Views/'], '', $file), $data, $options);
            }
        }

        return '<!-- View ' . esc($view) . ' tidak ditemukan -->';
    }
}

if (!function_exists('crb_view_path')) {
    /**
     * Mengembalikan path view yang valid, dengan fallback ke vendor.
     * Contoh: crb_view_path('\cirebonweb\Shield\login') → 'cirebonweb/Shield/login'
     */
    function crb_view_path(string $view): string
    {
        $view = ltrim($view, '\\'); // buang leading backslash
        $normalized = str_replace('\\', '/', $view);

        $paths = [
            APPPATH . 'Views/' . $normalized . '.php',
            ROOTPATH . 'vendor/cirebonweb/core/src/Views/' . $normalized . '.php',
        ];

        foreach ($paths as $file) {
            if (is_file($file)) {
                return $normalized;
            }
        }

        return ''; // atau bisa return view fallback seperti 'errors/view_not_found'
    }
}

