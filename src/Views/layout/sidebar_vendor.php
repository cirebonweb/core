<?php

$menus = crb_menu_items('core');
$currentPath = trim((string) parse_url(current_url(), PHP_URL_PATH), '/');

$menuPath = static function (array $item): string {
    return trim((string) parse_url(site_url($item['url']), PHP_URL_PATH), '/');
};

$renderMenu = static function (array $items) use (&$renderMenu, $menuPath, $currentPath): void {
    foreach ($items as $item) {
        if (isset($item['children'])) {
            $children = array_values(array_filter(
                $item['children'],
                static fn (array $child): bool => isset($child['key']) && aksesMenu($child['key'], 'core')
            ));

            if ($children === []) {
                continue;
            }

            $active = false;
            foreach ($children as $child) {
                if (isset($child['url']) && $menuPath($child) === $currentPath) {
                    $active = true;
                    break;
                }
            }
            ?>
            <li class="nav-item<?= $active ? ' menu-open' : '' ?>">
                <a href="#" class="nav-link<?= $active ? ' active' : '' ?>">
                    <i class="nav-icon <?= esc($item['icon'] ?? 'bi bi-folder') ?>"></i>
                    <p><?= esc($item['label']) ?><i class="right bi bi-caret-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                    <?php $renderMenu($children); ?>
                </ul>
            </li>
            <?php
            continue;
        }

        if (! isset($item['key'], $item['url']) || ! aksesMenu($item['key'], 'core')) {
            continue;
        }

        $active = $menuPath($item) === $currentPath;
        ?>
        <li class="nav-item">
            <a href="<?= esc(site_url($item['url'])) ?>" class="nav-link<?= $active ? ' active' : '' ?>">
                <i class="nav-icon <?= esc($item['icon'] ?? 'bi bi-circle') ?>"></i>
                <p><?= esc($item['label']) ?></p>
            </a>
        </li>
        <?php
    }
};

$renderMenu($menus);
