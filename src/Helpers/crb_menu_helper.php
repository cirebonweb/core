<?php

if (! function_exists('aksesMenu')) {
    /**
     * Ambil metadata menu APP atau menu CORE setelah menerapkan override APP.
     *
     * @return list<array<string, mixed>>
     */
    function crb_menu_items(string $source = 'app'): array
    {
        if ($source === 'app') {
            $menus = config('CrbMenu')->menuApp;
        } elseif ($source === 'core') {
            $menus = config('CoreMenu')->menus;
            $overrides = config('CrbMenu')->menuCore;

            if (! is_array($overrides)) {
                throw new UnexpectedValueException('Override menu CORE harus berupa array.');
            }

            $knownKeys = [];
            $collectKeys = static function (array $items) use (&$collectKeys, &$knownKeys): void {
                foreach ($items as $item) {
                    if (! is_array($item) || ! isset($item['key']) || ! is_string($item['key']) || trim($item['key']) === '') {
                        throw new UnexpectedValueException('Setiap item menu CORE harus memiliki key berupa string yang valid.');
                    }

                    $knownKeys[$item['key']] = true;
                    if (isset($item['children'])) {
                        if (! is_array($item['children']) || ! array_is_list($item['children'])) {
                            throw new UnexpectedValueException(sprintf(
                                'Daftar submenu CORE untuk "%s" harus berupa daftar.',
                                $item['key']
                            ));
                        }

                        $collectKeys($item['children']);
                    }
                }
            };
            $collectKeys($menus);

            foreach ($overrides as $key => $override) {
                if (! is_string($key) || ! isset($knownKeys[$key])) {
                    throw new UnexpectedValueException(sprintf(
                        'Override menu CORE mengacu pada key yang tidak dikenal: "%s".',
                        (string) $key
                    ));
                }

                if (! is_array($override)) {
                    throw new UnexpectedValueException(sprintf(
                        'Override menu CORE "%s" harus berupa array.',
                        $key
                    ));
                }

                foreach ($override as $property => $value) {
                    if (! in_array($property, ['label', 'icon'], true)) {
                        throw new UnexpectedValueException(sprintf(
                            'Properti "%s" tidak dapat dioverride pada menu CORE "%s"; key dan url ditetapkan CORE, sedangkan akses diatur pada CrbAkses.php.',
                            (string) $property,
                            $key
                        ));
                    }

                    if (in_array($property, ['label', 'icon'], true) && ! is_string($value)) {
                        throw new UnexpectedValueException(sprintf(
                            'Override "%s" pada menu CORE "%s" harus berupa string.',
                            $property,
                            $key
                        ));
                    }
                }
            }

            $applyOverrides = static function (array $items) use (&$applyOverrides, $overrides): array {
                foreach ($items as &$item) {
                    if (isset($overrides[$item['key']])) {
                        $item = array_replace($item, $overrides[$item['key']]);
                    }

                    if (isset($item['children'])) {
                        $item['children'] = $applyOverrides($item['children']);
                    }
                }
                unset($item);

                return $items;
            };

            $menus = $applyOverrides($menus);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Sumber menu "%s" tidak valid; gunakan "app" atau "core".',
                $source
            ));
        }

        if (! is_array($menus) || ! array_is_list($menus)) {
            throw new UnexpectedValueException(sprintf(
                'Daftar menu untuk sumber "%s" harus berupa daftar.',
                $source
            ));
        }

        return $menus;
    }

    /**
     * Resolve a menu item by its configured URL.
     *
     * @return array<string, mixed>|null
     */
    function crb_menu_item_by_url(string $url): ?array
    {
        $targetPath = '/' . trim((string) parse_url($url, PHP_URL_PATH), '/');

        foreach (['app', 'core'] as $source) {
            $findByUrl = static function (array $items) use (&$findByUrl, $targetPath): ?array {
                foreach ($items as $item) {
                    if (isset($item['url'])) {
                        $itemPath = '/' . trim((string) parse_url(site_url($item['url']), PHP_URL_PATH), '/');
                        if ($itemPath === $targetPath) {
                            return $item;
                        }
                    }

                    if (isset($item['children'])) {
                        $child = $findByUrl($item['children']);
                        if ($child !== null) {
                            return $child;
                        }
                    }
                }

                return null;
            };

            $item = $findByUrl(crb_menu_items($source));
            if ($item !== null) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Build absolute URLs for menu items, indexed by module key.
     *
     * @return array<string, string>
     */
    function crb_menu_urls(): array
    {
        $urls = [];
        $collectUrls = static function (array $items) use (&$collectUrls, &$urls): void {
            foreach ($items as $item) {
                if (isset($item['key'], $item['url'])) {
                    $urls[$item['key']] = site_url($item['url']);
                }

                if (isset($item['children'])) {
                    $collectUrls($item['children']);
                }
            }
        };

        foreach (['app', 'core'] as $source) {
            $collectUrls(crb_menu_items($source));
        }

        return $urls;
    }

    /**
     * Periksa visibilitas menu dari kebijakan akses modul APP.
     */
    function aksesMenu(string $menu, string $source = 'app'): bool
    {
        $menus = crb_menu_items($source);

        $findMenu = static function (array $items) use (&$findMenu, $menu): ?array {
            foreach ($items as $item) {
                if (! is_array($item) || ! isset($item['key']) || ! is_string($item['key']) || trim($item['key']) === '') {
                    throw new UnexpectedValueException('Setiap item menu harus memiliki key berupa string yang valid.');
                }

                if ($item['key'] === $menu) {
                    return $item;
                }

                if (isset($item['children'])) {
                    if (! is_array($item['children']) || ! array_is_list($item['children'])) {
                        throw new UnexpectedValueException(sprintf(
                            'Daftar submenu untuk "%s" harus berupa daftar.',
                            $item['key']
                        ));
                    }

                    $child = $findMenu($item['children']);
                    if ($child !== null) {
                        return $child;
                    }
                }
            }

            return null;
        };

        $item = $findMenu($menus);
        if ($item === null) {
            return false;
        }

        return aksesModul($menu);
    }
}
