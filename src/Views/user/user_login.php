<?= $this->extend('layout/template.php') ?>

<?= $this->section("css") ?>
<?= $this->include('plugin/css_tabel') ?>
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<section class="content">
    <div class="container-fluid">

        <div class="statContainer d-flex justify-content-center row">

            <div class="col-md-3 col-lg-2">
                <div class="small-box border border-primary bg-white">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="login_total">0</h3>
                        <p>Total Login</p>
                    </div>
                    <div class="icon"><i class="bi bi-people-fill text-primary"></i></div>
                </div>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <div class="small-box border border-success bg-white">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="login_berhasil">0</h3>
                        <p>Berhasil Login</p>
                    </div>
                    <div class="icon"><i class="bi bi-person-fill-check text-success"></i></div>
                </div>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <div class="small-box border border-danger bg-white">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="login_gagal">0</h3>
                        <p>Gagal Login</p>
                    </div>
                    <div class="icon"><i class="bi bi-person-fill-slash text-danger"></i></div>
                </div>
            </div>

        </div>

        <div class="statContainer d-flex justify-content-center row">

            <div class="col-6 col-md-3 col-lg-2">
                <div class="info-box">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <span class="info-box-icon"><i class="bi bi-laptop text-success"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Desktop</span>
                        <span class="stat-value info-box-number" data-key="login_desktop">0</span>
                    </div>
                </div>
            </div>

             <div class="col-6 col-md-3 col-lg-2">
                <div class="info-box">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <span class="info-box-icon"><i class="bi bi-tablet text-success"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tablet</span>
                        <span class="stat-value info-box-number" data-key="login_tablet">0</span>
                    </div>
                </div>
            </div>

             <div class="col-6 col-md-3 col-lg-2">
                <div class="info-box">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <span class="info-box-icon"><i class="bi bi-phone text-success"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mobile</span>
                        <span class="stat-value info-box-number" data-key="login_mobile">0</span>
                    </div>
                </div>
            </div>

             <div class="col-6 col-md-3 col-lg-2">
                <div class="info-box">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <span class="info-box-icon"><i class="bi bi-robot text-danger"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Robot</span>
                        <span class="stat-value info-box-number" data-key="login_robot">0</span>
                    </div>
                </div>
            </div>
        </div><!-- row statistik-->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <select id="filter_status" class="form-control form-control-sm d-inline-block w-auto mx-1">
                            <option value=""># Status</option>
                            <option value="1">Berhasil</option>
                            <option value="0">Gagal</option>
                        </select>
                        <table id="tabelData" class="table table-bordered table-hover dataTable dtr-inline">
                            <thead>
                                <tr>
                                    <th class="no-export"><input type="checkbox" id="checkAll"></th>
                                    <th>ID</th>
                                    <th>Status</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Perangkat</th>
                                    <th>Browser</th>
                                    <th>OS</th>
                                    <th>Waktu Login</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="<?= versi('plugin/datatables/datatables.min.js') ?>" defer></script>
<script src="<?= versi('vendor/js/helper_statistik.min.js') ?>" defer></script>
<script src="<?= versi('vendor/js/helper_form.min.js') ?>" defer></script>
<script src="<?= versi('vendor/js/helper_format.min.js') ?>" defer></script>
<script>
    const langText = {
        removeTitle: '<?= lang('App.remove-title') ?>',
        removeReset: '<?= lang('App.remove-reset') ?>',
        removeText: '<?= lang('App.remove-text') ?>'
    };
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?= file_get_contents(__DIR__ . '/user_login.js') ?>
    });
</script>
<?= $this->endSection() ?>