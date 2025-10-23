<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use Cirebonweb\Models\User\UserProfilModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Dapatkan provider user Shield (default UserModel)
        $users = auth()->getProvider();

        // Membuat user baru
        $user = new User([
            'username' => 'admin satu',
            'active'   => '1',
            'email'    => 'admin@mail.com',
            'password' => 'cirebon321',
        ]);

        // Simpan user ke database
        $users->save($user);
        $userId = $users->getInsertID();
        $user = $users->findById($userId);
        $user->addGroup('admin');
        $userProfilModel = new UserProfilModel();
        $userProfilModel->insert(['user_id' => $userId]);
    }
}
// php spark db:seed UserSeeder