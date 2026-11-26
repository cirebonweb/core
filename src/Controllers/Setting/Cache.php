<?php

namespace Cirebonweb\Controllers\Setting;

use CodeIgniter\Controller;
use Cirebonweb\Libraries\CacheLibrari;

class Cache extends Controller
{
    public function index()
    {
        $dataCache = [];

        foreach (glob(WRITEPATH . 'cache/*') as $file) {
            if (! is_file($file)) {
                continue;
            }

            $data = @unserialize((string) file_get_contents($file));

            if (! is_array($data) || ! isset($data['time'], $data['ttl'])) {
                continue;
            }

            $dataCache[] = [
                'name'    => basename($file),
                'created' => date('Y-m-d H:i:s', $data['time']),
                'expires' => $data['ttl'] === 0 ? 'lifetime' : date('Y-m-d H:i:s', $data['time'] + $data['ttl']),
                'ttl'     => $data['ttl'],
            ];
        }

        $data = [
            'url' => 'vendor/setting-cache',
            'navigasi' => '<a href="/vendor/setting">Setting</a> &nbsp;',
            'durasiCache' => service('cacheTtl')->durasi(),
            'cache' => $dataCache
        ];
        return view('Cirebonweb\setting\cache', $data);
    }

    public function tabel()
    {
        $result = ['data' => []];

        $cachePath = WRITEPATH . 'cache/';
        $files = glob($cachePath . '*');
        $no = 1;

        foreach ($files as $file) {
            if (is_file($file)) {
                $raw = file_get_contents($file);
                $cacheData = @unserialize($raw);

                if (is_array($cacheData) && isset($cacheData['time'], $cacheData['ttl'])) {
                    $timeCreated = date('d.m.Y → H:i:s', $cacheData['time']);
                    $ttl = $cacheData['ttl'];

                    if ($ttl === 0) {
                        $timeExpire = 'lifetime';
                    } else {
                        $expireTimestamp = $cacheData['time'] + $ttl;
                        $now = time();

                        // Hitung sisa waktu dari sekarang
                        $remaining = $expireTimestamp - $now;

                        if ($remaining < 0) {
                            $timeExpire = '<span class="text-danger">berakhir</span>';
                            // $timeExpire = '<span class="lencana bg-danger">expired</span>';
                        } else {
                            $days = floor($remaining / 86400); // 1 hari = 86400 detik

                            if ($days > 0) {
                                // Format normal: Hari & Jam
                                $hours = floor(($remaining % 86400) / 3600);
                                $timeExpire = sprintf('%02d Hari %02d Jam', $days, $hours);
                            } else {
                                // Kalau kurang dari 1 hari → Jam & Menit
                                $hours = floor($remaining / 3600);
                                $minutes = floor(($remaining % 3600) / 60);
                                $timeExpire = sprintf('%02d Jam %02d Menit', $hours, $minutes);
                            }
                        }
                    }

                    // Ambil tipe cache dari nama file
                    $baseName = basename($file);
                    $parts = explode('_', $baseName, 2); // pecah jadi max 2 bagian
                    $tipeCache = $parts[0]; // ambil prefix sebelum "_"

                    // Konversi TTL ke nama durasi
                    if ($ttl === 0) {
                        $durasiTtl = 'Lifetime';
                    } else if ($ttl >= 3600) {
                        $hours = floor($ttl / 3600);
                        $minutes = floor(($ttl % 3600) / 60);
                        $durasiTtl = sprintf('%02d Jam %02d Menit', $hours, $minutes);
                    } else {
                        $minutes = floor($ttl / 60);
                        $durasiTtl = sprintf('%02d Menit', $minutes);
                    }

                    $btn = '<button class="btn btn-sm btn-danger btn-delete" data-filename="' . basename($file) . '">hapus</button>';

                    $result['data'][] = [
                        $no++,
                        $tipeCache,
                        $durasiTtl,
                        $timeExpire,
                        $timeCreated,
                        basename($file),
                        $ttl,
                        $btn
                    ];
                }
            }
        }
        return $this->response->setJSON($result);
    }

    public function simpan()
    {
        $values = $this->request->getPost();

        if (service('cacheTtl')->simpan($values)) {
            return $this->response->setJSON(['success' => true, 'messages' => 'Durasi cache berhasil diperbarui']);
        }

        return $this->response->setJSON(['success' => false, 'messages' => 'Tidak ada durasi cache yang valid untuk disimpan']);
    }

    public function hapus()
    {
        $filename = $this->request->getPost('filename');
        $cacheKey = basename($filename);
        $path     = WRITEPATH . 'cache/' . $cacheKey;

        if (!is_file($path)) {
            return $this->response->setJSON(['success' => false, 'messages' => lang("App.delete-error")]);
        }

        if (is_file($path)) {
            unlink($path);
        }

        return $this->response->setJSON(['success' => true, 'messages' => lang("App.delete-success")]);
    }

    public function hapusExpired()
    {
        $cachePath = WRITEPATH . 'cache/';
        $files     = glob($cachePath . '*');
        $deleted   = 0;

        foreach ($files as $file) {
            if (!is_file($file)) continue;

            $raw = file_get_contents($file);
            $cacheData = @unserialize($raw);

            if (is_array($cacheData) && isset($cacheData['time'], $cacheData['ttl'])) {
                $ttl = $cacheData['ttl'];
                $expireTimestamp = $cacheData['time'] + $ttl;

                if ($ttl !== 0 && $expireTimestamp < time()) {
                    unlink($file);
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            return $this->response->setJSON([
                'success'  => true,
                'messages' => "$deleted file cache expired berhasil dihapus."
            ]);
        }

        return $this->response->setJSON([
            'success'  => false,
            'messages' => "Tidak ada file cache expired yang ditemukan."
        ]);
    }
}