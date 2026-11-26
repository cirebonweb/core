<?php

use CodeIgniter\HTTP\UserAgent;

if (!function_exists('getDeviceData')) {
    /**
     * Parse data perangkat dari sebuah string User-Agent.
     *
     * Dipanggil tanpa argumen untuk memakai User-Agent request berjalan,
     * atau dengan argumen untuk membaca User-Agent tersimpan
     * (mis. `auth_logins.user_agent`). Tidak ada tulisan data saat login —
     * seluruh pemrosesan terjadi saat pembacaan.
     *
     * Hasil di-cache per string UA (key `device_<md5>`), sehingga UA yang sama
     * tidak diparsing ulang walau diminta berkali-kali.
     *
     * @return array{device: string, bit: string, os: string, browser: string,
     *               browserv: string, brand: string, model: string}
     */
    function getDeviceData(?string $uaString = null): array
    {
        $raw = trim($uaString ?? (string) service('superglobals')->server('HTTP_USER_AGENT'));

        $cacheKey = 'device_' . md5($raw);
        $cached   = cache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $data = parseDeviceData($raw);
        cache()->save($cacheKey, $data, service('cacheTtl')->ttl('cache_device'));

        return $data;
    }
}

if (!function_exists('parseDeviceData')) {
    /**
     * Parsing mentah tanpa cache. Satu instance UserAgent dipakai ulang untuk
     * seluruh baris, sehingga biaya per baris tetap kecil.
     */
    function parseDeviceData(string $uaString): array
    {
        static $ua = null;
        if ($ua === null) {
            $ua = new UserAgent();
        }
        // parse() me-reset browser/robot/mobile lalu meng-compile ulang,
        // jadi aman dipakai ulang untuk string yang berbeda-beda.
        $ua->parse($uaString);

        $s = strtolower($uaString);

        // Deteksi bit OS
        $bit = 'Null';
        if (stripos($s, 'x64') !== false || stripos($s, 'win64') !== false || stripos($s, 'amd64') !== false) {
            $bit = '64-bit';
        } elseif (stripos($s, 'x86') !== false || stripos($s, 'i686') !== false) {
            $bit = '32-bit';
        }

        // Deteksi tipe perangkat
        if ($ua->isRobot()) {
            $device = 'Robot';
        } elseif (isTablet($s)) {
            $device = 'Tablet';
        } elseif ($ua->isMobile()) {
            $device = 'Mobile';
        } else {
            $device = 'Desktop';
        }

        // OS & browser diambil dari string yang diberikan, bukan dari request berjalan
        $os      = $uaString === '' ? 'Tidak Diketahui' : getOperatingSystem($uaString, (string) $ua->getPlatform());
        $browser = getBrowserName($ua);

        if ($ua->isMobile()) {
            $brand = getMobileBrand($s);
            $model = getMobileModel($s);
        } else {
            $brand = 'Umum';
            $model = 'Umum';
        }

        return [
            'device'   => $device,
            'bit'      => $bit,
            'os'       => $os,
            'browser'  => $browser,
            'browserv' => trim((string) $ua->getVersion()),
            'brand'    => $brand,
            'model'    => $model,
        ];
    }
}

if (!function_exists('getBrowserName')) {
    /**
     * Nama browser beserta versinya, contoh: "Firefox 143", "Chrome 140".
     *
     * CI4 jatuh ke token generik "Mozilla" untuk User-Agent milik klien non-browser
     * (PowerShell, curl, dsb). Token itu diangkat ke nama klien sebenarnya agar
     * riwayat login tetap informatif.
     */
    function getBrowserName(UserAgent $ua): string
    {
        if ($ua->isRobot()) {
            $robot = trim((string) $ua->getRobot());

            return $robot !== '' ? $robot : 'Tidak Diketahui';
        }

        $name = trim((string) $ua->getBrowser());
        $ver  = trim((string) $ua->getVersion());

        if ($name === '' || $name === 'Mozilla') {
            $spesifik = detectNonBrowserClient($ua->getAgentString());
            if ($spesifik !== null) {
                return $spesifik;
            }
        }

        if ($name === '') {
            return 'Tidak Diketahui';
        }

        return $ver !== '' ? $name . ' ' . $ver : $name;
    }
}

if (!function_exists('detectNonBrowserClient')) {
    /**
     * Deteksi klien non-browser (script, API client, automation) dari string UA.
     * Mengembalikan null bila bukan klien yang dikenal.
     */
    function detectNonBrowserClient(string $ua): ?string
    {
        $pola = [
            'WindowsPowerShell/' => 'PowerShell',
            'PowerShell/'        => 'PowerShell',
            'PostmanRuntime/'    => 'Postman',
            'python-requests/'   => 'Python Requests',
            'Python-urllib/'     => 'Python urllib',
            'Go-http-client/'    => 'Go HTTP',
            'libwww-perl/'       => 'libwww-perl',
            'axios/'             => 'Axios',
            'node-fetch'         => 'node-fetch',
            'okhttp/'            => 'OkHttp',
            'Dalvik/'            => 'Android HTTP',
            'WinHttp'            => 'WinHTTP',
            'Google-HTTP-Java'   => 'Google HTTP',
            'Guzzle'             => 'Guzzle',
            'Java/'              => 'Java',
            'Dart/'              => 'Dart',
            'curl/'              => 'curl',
            'Wget/'              => 'Wget',
        ];

        foreach ($pola as $token => $nama) {
            $pos = stripos($ua, $token);
            if ($pos === false) {
                continue;
            }

            $sisa = substr($ua, $pos + strlen($token));
            $ver  = preg_match('/^([0-9][0-9.]*)/', $sisa, $m) === 1 ? $m[1] : '';

            return $ver !== '' ? $nama . ' ' . $ver : $nama;
        }

        return null;
    }
}

if (!function_exists('getOperatingSystem')) {
    /**
     * Sistem operasi beserta versinya hanya bila pasti.
     *
     * Catatan: Windows 10 dan Windows 11 sama-sama melaporkan `Windows NT 10.0`,
     * jadi versinya sengaja TIDAK dikarang — cukup ditampilkan "Windows".
     */
    function getOperatingSystem(string $uaString, string $platform = ''): string
    {
        $s = strtolower($uaString);

        if (preg_match('/android\s+([0-9]+)/i', $uaString, $m) === 1) {
            return 'Android ' . $m[1];
        }
        if (preg_match('/(?:iphone|ipad|ipod).*?os\s+([0-9_]+)/i', $uaString, $m) === 1) {
            return 'iOS ' . str_replace('_', '.', $m[1]);
        }
        if (strpos($s, 'windows phone') !== false) {
            return 'Windows Phone';
        }
        if (strpos($s, 'windows') !== false) {
            if (preg_match('/windows nt\s+([0-9.]+)/i', $uaString, $m) === 1) {
                $versi = [
                    '5.1' => 'Windows XP',
                    '6.0' => 'Windows Vista',
                    '6.1' => 'Windows 7',
                    '6.2' => 'Windows 8',
                    '6.3' => 'Windows 8.1',
                ];

                return $versi[$m[1]] ?? 'Windows';
            }

            return 'Windows';
        }
        if (preg_match('/mac os x\s+([0-9_]+)/i', $uaString, $m) === 1) {
            return 'macOS ' . str_replace('_', '.', $m[1]);
        }
        if (strpos($s, 'cros') !== false) {
            return 'Chrome OS';
        }
        if (strpos($s, 'linux') !== false || strpos($s, 'x11') !== false) {
            return 'Linux';
        }

        return $platform !== '' && $platform !== 'Unknown Platform'
            ? $platform
            : 'Tidak Diketahui';
    }
}

if (!function_exists('isTablet')) {
    function isTablet(string $uaString): bool
    {
        return (
            strpos($uaString, 'ipad') !== false ||
            strpos($uaString, 'tablet') !== false ||
            strpos($uaString, 'nexus 7') !== false ||
            strpos($uaString, 'galaxy tab') !== false ||
            strpos($uaString, 'kindle') !== false
        );
    }
}

if (!function_exists('getMobileOS')) {
    function getMobileOS(string $ua): string
    {
        if (strpos($ua, 'android') !== false) return 'Android';
        if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'ios') !== false) return 'iOS';
        if (strpos($ua, 'symbian') !== false) return 'Symbian';
        if (strpos($ua, 'windows phone') !== false) return 'Windows Phone';
        return 'Tidak Diketahui';
    }
}

if (!function_exists('getMobileBrowser')) {
    function getMobileBrowser(string $ua): string
    {
        if (strpos($ua, 'chrome') !== false) return 'Chrome Mobile';
        if (strpos($ua, 'safari') !== false && strpos($ua, 'chrome') === false) return 'Safari Mobile';
        if (strpos($ua, 'firefox') !== false) return 'Firefox Mobile';
        if (strpos($ua, 'opera') !== false || strpos($ua, 'opr') !== false) return 'Opera Mobile';
        return 'Tidak Diketahui';
    }
}

if (!function_exists('getMobileBrand')) {
    function getMobileBrand(string $ua): string
    {
        $ua = strtolower($ua);

        $brands = [
            'Advan'    => ['advan'],
            'Apple'    => ['iphone', 'ipad', 'macintosh'],
            'Asus'     => ['asus'],
            'Evercoss' => ['evercoss'],
            'Google'   => ['pixel', 'google'],
            'Honor'    => ['honor'],
            'Huawei'   => ['huawei'],
            'Infinix'  => ['infinix'],
            'Itel'     => ['itel'],
            'Lenovo'   => ['lenovo'],
            'Meizu'    => ['meizu'],
            'Motorola' => ['motorola', 'moto'],
            'Nokia'    => ['nokia'],
            'OnePlus'  => ['oneplus'],
            'Oppo'     => ['oppo'],
            'Realme'   => ['realme'],
            'Samsung'  => ['samsung', 'sm-', 'gt-'],
            'Sony'     => ['sony', 'xperia'],
            'Tecno'    => ['tecno'],
            'Vivo'     => ['vivo'],
            'Xiaomi'   => ['xiaomi', 'mi ', 'redmi', 'poco'],
            'ZTE'      => ['zte'],
        ];

        foreach ($brands as $brand => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($ua, $pattern) !== false) {
                    return ucfirst($brand);
                }
            }
        }

        return 'Tidak Diketahui';
    }
}

if (!function_exists('getMobileModel')) {
    function getMobileModel(string $ua): string
    {
        // Contoh: cari pola SM-G991B atau iPhone13,3
        if (preg_match('/(sm-[a-z0-9]+|iphone[\d,]+|ipad[\d,]+|mi [a-z0-9]+|redmi [a-z0-9]+|infinix[-\s]?[a-z0-9]+|tecno[-\s]?[a-z0-9]+|itel[-\s]?[a-z0-9]+|advan[-\s]?[a-z0-9]+|evercoss[-\s]?[a-z0-9]+)/i', $ua, $match)) {
            return strtoupper($match[1]);
        }

        return 'Tidak Diketahui';
    }
}
