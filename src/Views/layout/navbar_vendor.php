<!-- Kerangka navbar bawaan core. Menu project disuntik lewat layout/navbar_menu.php -->

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link list" data-widget="pushmenu" href="#" role="button">
        <i class="bi bi-arrows-collapse-vertical"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= url_to('/') ?>" class="nav-link">Home</a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">
        Hai, <?= auth()->user()->username ?? 'User' ?>
        <i class="bi bi-caret-down ml-1"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
        <a href="<?= url_to('/') ?>" class="dropdown-item">
          <i class="bi bi-person mr-3"></i> Profil
        </a>
        <div class="dropdown-divider"></div>
        <a href="<?= url_to('logout') ?>" class="dropdown-item">
          <i class="bi bi-box-arrow-right mr-3"></i> Logout
        </a>
      </div>
    </li>
  </ul>
</nav>