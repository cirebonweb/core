<?php
$user_link = [
  'admin/user/user-list',
  'admin/user/user-login'
];
$user_aktif = in_array(str_replace(base_url(), '', current_url()), $user_link);

$setting_link = [
  'admin/setting/umum',
  'admin/setting/cache',
  'admin/setting/optimasi'
];
$setting_aktif = in_array(str_replace(base_url(), '', current_url()), $setting_link);

$log_link = [
  'admin/log/email'
];
$log_aktif = in_array(str_replace(base_url(), '', current_url()), $log_link);
?>

<!-- User -->
<li class="nav-item<?= $user_aktif ? ' menu-open' : ''  ?>">
    <a href="#" class="nav-link<?= $user_aktif ? ' active' : ''  ?>">
        <i class="nav-icon bi bi-person-circle"></i>
        <p>User <i class="right bi bi-caret-left"></i></p>
    </a>
    <ul class="nav nav-treeview">

        <li class="nav-item">
            <a href="<?= url_to('admin/user/user-list') ?>" class="nav-link<?= (current_url() == base_url('admin/user/user-list')) ? ' active' : '' ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>User List</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= url_to('admin/user/user-login') ?>" class="nav-link<?= (current_url() == base_url('admin/user/user-login')) ? ' active' : '' ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>User Login</p>
            </a>
        </li>

    </ul>
</li>

<!-- Setting -->
<li class="nav-item<?= $setting_aktif ? ' menu-open' : ''  ?>">
    <a href="#" class="nav-link<?= $setting_aktif ? ' active' : ''  ?>">
        <i class="nav-icon bi bi-gear"></i>
        <p>Setting <i class="right bi bi-caret-left"></i></p>
    </a>
    <ul class="nav nav-treeview">

        <li class="nav-item">
            <a href="<?= url_to('admin/setting/umum') ?>" class="nav-link<?= (current_url() == base_url('admin/setting/umum')) ? ' active' : '' ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>Umum</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= url_to('admin/setting/cache') ?>" class="nav-link<?= (current_url() == base_url('admin/setting/cache')) ? ' active' : '' ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>Cache</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= url_to('admin/setting/optimasi') ?>" class="nav-link<?= (current_url() == base_url('admin/setting/optimasi')) ? ' active' : '' ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>Optimasi</p>
            </a>
        </li>

    </ul>
</li>

<!-- Log -->
<li class="nav-item<?= $log_aktif ? ' menu-open' : ''  ?>">
    <a href="#" class="nav-link<?= $log_aktif ? ' active' : ''  ?>">
        <i class="nav-icon bi bi-gear"></i>
        <p>Log <i class="right bi bi-caret-left"></i></p>
    </a>
    <ul class="nav nav-treeview">

        <li class="nav-item">
            <a href="<?= url_to('admin/log/email') ?>" class="nav-link<?= (current_url() == base_url('admin/log/email')) ? ' active' : '' ?>">
                <i class="nav-icon bi bi-circle"></i>
                <p>Log Email</p>
            </a>
        </li>

    </ul>
</li>