<?php

namespace Cirebonweb\Controllers;

use App\Controllers\BaseController;
use Cirebonweb\Models\User\UserModel;
use Cirebonweb\Models\User\UserProfilModel;
use Cirebonweb\Models\Auth\AuthSesiModel;
use CodeIgniter\I18n\Time;

class Profil extends BaseController
{
    protected $userModel;
    protected $userProfilModel;
    protected $authSesiModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userProfilModel = new UserProfilModel();
        $this->authSesiModel = new AuthSesiModel();
    }

    public function index()
    {
        $encrypter = service('encrypter');
        $encoded = $this->request->getGet('id');

        if ($encoded) {
            $decoded = base64_decode($encoded);
            $userId = $encrypter->decrypt($decoded);
        } else {
            $userId = auth()->id();
        }

        $user = $this->userModel->getProfil($userId);

        if (!$user->id_profil) {
            return redirect()->to('/')->with('error', 'Id user profil tidak ditemukan.');
        }

        $foto = is_null($user->foto) ? 'crb-profil.png' : $user->foto;

        if (auth()->id() === $userId) {
            $tabPane = 'tab-pane fade';
        } else {
            $tabPane = 'tab-pane fade show active';
        }

        $data = [
            'url' => 'profil',
            'pageTitle' => 'Profil',
            'navigasi' => '',
            'userId' => $userId,
            'user' => $user,
            'foto' => $foto,
            'tabPane' => $tabPane
        ];
        return view('profil', $data);
    }

    public function getPerangkat()
    {
        $userId = auth()->id();
        $sesi = $this->authSesiModel->getProfil($userId, 8);
        $sesiId = session_name() . ':' . session_id();
        $now = Time::now()->getTimestamp();
        $sesiExpire = config('Session')->expiration;

        foreach ($sesi as &$s) {
            $s->ip_publik = ($s->ip_address === '::1') ? '127.0.0.1' : $s->ip_address;

            if ($s->perangkat === 'Robot') {
                $s->device = 'bi-robot mr-2';
            } elseif ($s->perangkat === 'Tablet') {
                $s->device = 'bi-tablet mb-1';
            } elseif ($s->perangkat === 'Mobile') {
                $s->device = 'bi-phone ml-n2 mb-1';
            } else {
                $s->device = 'bi-laptop mr-2';
            }

            $s->remember = is_null($s->remember_id) ? 'bi-toggle-off' : 'bi-toggle-on';
            $lastActivity = strtotime($s->dibuat);
            $isExpired = ($lastActivity + $sesiExpire) <= $now;

            if ($s->id === $sesiId && !$isExpired) {
                $s->warna = 'text-success';
                $s->ikon = 'bi-caret-right-fill';
                $s->status = '<span class="lencana bg-success">Perangkat aktif</span>';
            } elseif (!$isExpired) {
                $timeAgo = Time::createFromTimestamp($lastActivity)->humanize();
                $timeAgo = str_replace('"', '', $timeAgo);
                $s->warna = 'text-secondary';
                $s->ikon = 'bi-caret-right';
                $s->status = '<span class="lencana bg-secondary">Aktif ' . $timeAgo . '</span>';
            } else {
                $timeAgo = Time::createFromTimestamp($lastActivity)->humanize();
                $timeAgo = str_replace('"', '', $timeAgo);
                $s->ikon = 'bi-caret-right';
                $s->warna = 'text-secondary';
                $s->status = '<span class="lencana bg-secondary">Berakhir ' . $timeAgo . '</span>';
            }
        }

        $data = [
            'sessions' => $sesi, // Data perangkat untuk halaman saat ini
            'pager'    => $this->authSesiModel->pager->links('default', 'bs4_full'), // HTML untuk link paginasi
            'total'    => $this->authSesiModel->pager->getTotal() // Total semua perangkat (untuk logika tombol)
        ];

        return $this->response->setJSON($data);
    }

    public function logoutPerangkat()
    {
        $userId = auth()->id();
        $sesiId = session_name() . ':' . session_id();

        if (auth()->id() !== (int) $userId) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'Hanya pemilik ID yang dapat merubah data.']);
        }

        $sesiNonAktif = $this->authSesiModel->getSesiNonAktif($userId, $sesiId);
        $authSesiModel = model('Auth/AuthSesiModel');

        try {
            foreach ($sesiNonAktif as $sesi) {
                $rememberId = $sesi->remember_id ?? null;

                if ($rememberId) {
                    $tokenModel = model('Auth/AuthRememberTokenModel');
                    $token = $tokenModel->find($rememberId);

                    if ($token) {
                        $tokenModel->delete($rememberId); // sesi ikut terhapus via CASCADE
                    } else {
                        $authSesiModel->hapusSesi($sesi->id); // fallback jika token sudah tidak ada
                    }
                } else {
                    $authSesiModel->hapusSesi($sesi->id);
                }
            }
            $response = ['success'  => true, 'messages' => 'Berhasil logout dari perangkat lain.'];
        } catch (\Throwable $th) {
            log_message('error', 'logoutPerangkatLain: ' . $th->getMessage());
            $response = ['success'  => false, 'messages' => 'Info: ' . $th->getMessage()];
        }
        return $this->response->setJSON($response);
    }

    public function updateFoto()
    {
        $idProfil = $this->request->getPost('id_profil');
        $file     = $this->request->getFile('foto_profil');

        // Validasi file
        if (!$this->validate([
            'foto_profil' => [
                'label' => 'File gambar',
                'rules' => 'uploaded[foto_profil]|is_image[foto_profil]|mime_in[foto_profil,image/jpg,image/jpeg,image/png]|max_size[foto_profil,2048]',
                'errors' => [
                    'uploaded' => 'Silakan pilih file gambar untuk diunggah.',
                    'is_image' => 'File yang diunggah bukan gambar yang valid.',
                    'mime_in'  => 'Format gambar harus .jpg, .jpeg atau .png',
                    'max_size' => 'Ukuran gambar maksimal 2 MB.'
                ]
            ],
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $lokasi = FCPATH . 'upload/profil/';
        if (!is_dir($lokasi)) {
            mkdir($lokasi, 0755, true);
        }

        $ext       = $file->getExtension();
        $fileBaru  = 'foto-profil_' . $idProfil . '_' . time() . '.' . $ext;
        $filePath  = $lokasi . $fileBaru;
        $fileLama  = $this->userProfilModel->getFoto($idProfil) ?? null;

        $this->db->transBegin();

        try {
            // 1. Pindahkan file baru dulu
            if (! $file->move($lokasi, $fileBaru, true)) {
                throw new \RuntimeException("Gagal memindahkan file ke {$filePath}");
            }

            // 2. Update database
            $sukses = $this->userProfilModel->update($idProfil, ['foto' => $fileBaru]);
            if (! $sukses) {
                throw new \RuntimeException('Gagal update data profil ke database.');
            }

            // 3. Cek status update db
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            // 4. Commit transaksi
            $this->db->transCommit();

            // 5. Setelah commit berhasil, baru hapus foto lama (jika ada)
            if ($fileLama && file_exists($lokasi . $fileLama)) {
                unlink($lokasi . $fileLama);
            }

            session()->setFlashdata('sukses', lang("App.update-success"));
        } catch (\Throwable $e) {
            $this->db->transRollback();

            // Hapus file baru yang sudah ter-upload
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            log_message('error', 'Gagal update foto profil: ' . $e->getMessage());
            session()->setFlashdata('error', lang("App.update-error"));
        }

        return redirect()->back();
    }

    public function updateAkun()
    {
        $response = [];
        $idUser = $this->request->getPost('id_user');

        $users = auth()->getProvider();
        $user = $users->findById($idUser);

        if (!$user) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'ID User tidak ditemukan.']);
        }

        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => trim($this->request->getPost('email'))
        ];

        if ($this->request->getPost('password')) {
            $userData['password'] = $this->request->getPost('password');
        }

        $rules = [
            'username' => ['label' => 'Nama User', 'rules' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,' . $idUser . ']'],
            'email' => ['label' => 'Alamat Email', 'rules' => 'required|max_length[50]|valid_email|is_unique[auth_identities.secret,user_id,' . $idUser . ']'],
            'password' => ['label' => 'Password', 'rules' => 'permit_empty|min_length[8]|max_byte[72]|strong_password[]'],
            'password_confirm' => ['label' => 'Konfirmasi Password', 'rules' => 'matches[password]'],
        ];

        $data = $this->request->getPost(array_keys($rules));

        if (! $this->validateData($data, $rules)) {
            return $this->response->setJSON(['success'  => false, 'messages' => $this->validation->getErrors()]);
        }

        try {
            $user->fill($userData);

            if ($users->save($user)) {
                $response = ['success'  => true, 'messages' => lang("App.update-success")];
            } else {
                $response = ['success'  => false, 'messages' => lang("App.update-error")];
            }

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Gagal simpan profil/update-akun: ' . $e->getMessage());
            return $this->response->setJSON(['success'  => false, 'messages' => 'Terjadi kesalahan saat merubah data user.']);
        }
    }

    public function updateInfo()
    {
        // if (!auth()->user()->can('klien.edit')) {
        //     return $this->response->setJSON(['success'  => false, 'messages' => 'Anda tidak memiliki izin untuk mengedit user.']);
        // }

        $response = [];
        $idUser = $this->request->getPost('user_id');
        $idProfil = $this->request->getPost('profil_id');

        if (!$idUser || !$idProfil) {
            return $this->response->setJSON(['success'  => false, 'messages' => 'ID user profil tidak ditemukan.']);
        }

        $data = [
            'user_id' => $idUser,
            'perusahaan' => $this->request->getPost('perusahaan'),
            'whatsapp' => trim($this->request->getPost('whatsapp')),
            'telegram' => trim($this->request->getPost('telegram')),
            'alamat' => $this->request->getPost('alamat')
        ];

        $validasi = $this->userProfilModel->validationRules;
        $data = $this->request->getPost(array_keys($validasi));
        // array_keys($validasi) => ['user_id', 'perusahaan', 'whatsapp', 'telegram', 'alamat']

        if (! $this->validateData($data, $validasi)) {
            return $this->response->setJSON(['success'  => false, 'messages' => $this->validation->getErrors()]);
        }

        try {
            if ($this->userProfilModel->update($idProfil, $data)) {
                $response = ['success'  => true, 'messages' => lang("App.update-success")];
            } else {
                $response = ['success'  => false, 'messages' => lang("App.update-error")];
            }

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Gagal simpan profil/update-info: ' . $e->getMessage());
            return $this->response->setJSON(['success'  => false, 'messages' => 'Terjadi kesalahan saat merubah data.']);
        }
    }
}