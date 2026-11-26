<?php

/**
 * Dispatcher sidebar.
 *
 *   layout/sidebar_menu.php              -> milik project (opsional, tidak ditimpa core)
 *  (layout/sidebar_vendor.php)->core     -> milik core
 *
 * Keduanya dimuat berurutan: menu project lebih dulu, baru bawaan core.
 *
 * CATATAN penting soal namespace:
 * - `locator->search()` mencari path RELATIF terhadap tiap namespace, dan
 *   karena namespace `Cirebonweb` di-mapping ke `src/`, path-nya berawalan
 *   `Views/` (contoh: `Views/layout/sidebar_vendor.php`).
 * - Sebaliknya `$this->include()` untuk view core WAJIB memakai prefix
 *   `Cirebonweb\`. Tanpa prefix itu FileLocator hanya mencarinya di app/Views
 *   lalu melempar "Berkas tidak valid".
 */

$locator = service('locator');

// [nama view saat dipanggil, path relatif terhadap namespace]
$views = [
    'layout/sidebar_menu'              => 'Views/layout/sidebar_menu.php',
    'Cirebonweb\layout\sidebar_vendor' => 'Views/layout/sidebar_vendor.php',
];

foreach ($views as $view => $file) {
    if ($locator->search($file) !== []) {
        echo $this->include($view);
    }
}
