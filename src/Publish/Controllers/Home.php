<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $user = auth()->user();

        if ($user->inGroup('klien')) {
            return redirect()->to('klien');
        }

        return redirect()->to('admin');

        // -- options for multiple groups --
        // if ($user->inGroup('admin') || $user->inGroup('superadmin')) {
        //     return redirect()->to('admin');
        // }

        // if ($user->inGroup('klien')) {
        //     return redirect()->to('klien');
        // }

        // return redirect()->to('beta');
    }
}
