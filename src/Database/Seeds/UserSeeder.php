<?php

namespace Cirebonweb\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use Cirebonweb\Models\UserProfilModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Dapatkan provider user Shield (default UserModel)
        $users = auth()->getProvider();
        $userProfilModel = new UserProfilModel();

        // 1. Membuat User Admin
        $admin = new User([
            'username' => 'admin satu',
            'active'   => '1',
            'email'    => 'admin@mail.com',
            'password' => 'cirebon321',
        ]);

        $users->save($admin);
        $adminId = $users->getInsertID();

        $adminUser = $users->findById($adminId);
        $adminUser->addGroup('admin');
        $userProfilModel->insert(['user_id' => $adminId]);

        // 2. Membuat User Regular
        $user = new User([
            'username' => 'user satu',
            'active'   => '1',
            'email'    => 'user@mail.com',
            'password' => 'cirebon321',
        ]);

        $users->save($user);
        $userId = $users->getInsertID();

        $regularUser = $users->findById($userId);
        $regularUser->addGroup('user');
        $userProfilModel->insert(['user_id' => $userId]);
    }
}
// php spark db:seed 'Cirebonweb\Database\Seeds\UserSeeder'