<?php $home_link = (auth()->user()->inGroup('klien')) ? 'klien' : 'admin'; ?>

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item"><a class="nav-link list" data-widget="pushmenu" href="#" role="button"><i class="bi bi-arrows-collapse-vertical"></i></a></li>
    <li class="nav-item d-none d-sm-inline-block"><a href="<?= url_to($home_link) ?>" class="nav-link">Home</a></li>

    <!-- # Admin Menu -->
    <?php if (auth()->user()->inGroup('admin')) : ?>
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
    <?php endif ?>

  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">Hai, <?= auth()->user()->username ?> <i class="bi bi-caret-down ml-1"></i></a>
      <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
        <a href="<?= url_to('profil') ?>" class="dropdown-item"><i class="bi bi-person mr-3"></i> Profil</a>
        <div class="dropdown-divider"></div>
        <a href="<?= url_to('logout') ?>" class="dropdown-item"><i class="bi bi-box-arrow-right mr-3"></i>Logout</a>
      </div>
    </li>
  </ul>
</nav>