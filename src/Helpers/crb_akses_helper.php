<?php

if (! function_exists('aksesModul')) {
    /**
     * Periksa akses modul berdasarkan kebijakan APP.
     */
    function aksesModul(string $module): bool
    {
        $modules = config('CrbAkses')->modules;

        if (! is_array($modules) || ! array_key_exists($module, $modules)) {
            throw new UnexpectedValueException(sprintf(
                'Konfigurasi CrbAkses::$modules harus mendefinisikan modul "%s".',
                $module
            ));
        }

        $groups = $modules[$module];

        if ($groups === 'session') {
            return auth()->loggedIn();
        }

        if (! is_array($groups) || ! array_is_list($groups)) {
            throw new UnexpectedValueException(sprintf(
                'Kebijakan akses untuk modul "%s" harus berupa "session" atau daftar group.',
                $module
            ));
        }

        foreach ($groups as $group) {
            if (! is_string($group) || trim($group) === '') {
                throw new UnexpectedValueException(sprintf(
                    'Kebijakan group untuk modul "%s" hanya boleh berisi nama group yang valid.',
                    $module
                ));
            }
        }

        if ($groups === []) {
            return true;
        }

        $user = auth()->user();

        return $user !== null && $user->inGroup(...$groups);
    }
}

if (! function_exists('crb_group_label')) {
    /**
     * Label tampil untuk group dari AuthGroups. Fallback ke key bila title tidak tersedia.
     */
    function crb_group_label(string $group): string
    {
        $groups = config('AuthGroups')->groups ?? [];
        $title  = is_array($groups) ? ($groups[$group]['title'] ?? null) : null;

        return is_string($title) && trim($title) !== '' ? $title : $group;
    }
}
