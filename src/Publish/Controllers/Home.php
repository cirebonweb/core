<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home', ['navigasi' => '']);

        /**
         * Opsi untuk dua grup pengguna yang berbeda, misalnya 'admin' dan 'user'.
         */
        // $user = auth()->user();

        // if ($user->inGroup('user')) {
        //     return redirect()->to('user');
        // }

        // return redirect()->to('admin');

        /**
         * Opsi untuk beberapa grup pengguna.
         */
        // if ($user->inGroup('admin') || $user->inGroup('superadmin')) {
        //     return redirect()->to('admin');
        // }

        // if ($user->inGroup('user')) {
        //     return redirect()->to('user');
        // }

        // return redirect()->to('beta');
    }
}
