<!-- User -->
<li class="nav-item d-none d-sm-inline-block dropdown">
    <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">User <i class="bi bi-caret-down ml-1"></i></a>
    <div class="dropdown-menu dropdown-menu-sm dropdown-menu">
        <a href="<?= url_to('admin/user/user-list') ?>" class="dropdown-item"><i class="bi bi-caret-right mr-1"></i>User List</a>
        <div class="dropdown-divider"></div>
        <a href="<?= url_to('admin/user/user-login') ?>" class="dropdown-item"><i class="bi bi-caret-right mr-1"></i>User Login</a>
    </div>
</li>

<!-- Setting -->
<li class="nav-item d-none d-sm-inline-block dropdown">
    <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">Setting <i class="bi bi-caret-down ml-1"></i></a>
    <div class="dropdown-menu dropdown-menu-sm dropdown-menu">
        <a href="<?= url_to('admin/setting/umum') ?>" class="dropdown-item"><i class="bi bi-caret-right mr-1"></i>Umum</a>
        <div class="dropdown-divider"></div>
        <a href="<?= url_to('admin/setting/cache') ?>" class="dropdown-item"><i class="bi bi-caret-right mr-1"></i>Cache</a>
        <div class="dropdown-divider"></div>
        <a href="<?= url_to('admin/setting/optimasi') ?>" class="dropdown-item"><i class="bi bi-caret-right mr-1"></i>Optimasi</a>
    </div>
</li>

<!-- Log -->
<li class="nav-item d-none d-sm-inline-block dropdown">
    <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">Log <i class="bi bi-caret-down ml-1"></i></a>
    <div class="dropdown-menu dropdown-menu-sm dropdown-menu">
        <a href="<?= url_to('admin/log/email') ?>" class="dropdown-item"><i class="bi bi-caret-right mr-1"></i>Email</a>
    </div>
</li>