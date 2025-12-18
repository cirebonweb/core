<?= $this->extend('layout/template.php') ?>

<?= $this->section("css") ?>
<?= $this->include('plugin/tabel_css') ?>
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<section class="content">
    <div class="container-fluid">

        <div class="statContainer row">
            <div class="col-6 col-lg-3">
                <div class="small-box bg-dark">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="log_total">0</h3>
                        <p>Total Log</p>
                    </div>
                    <div class="icon"><i class="bi bi-envelope-at-fill"></i></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="small-box bg-success">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="log_berhasil">0</h3>
                        <p>Berhasil Proses</p>
                    </div>
                    <div class="icon"><i class="bi bi-envelope-check-fill"></i></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="small-box bg-danger">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="log_gagal">0</h3>
                        <p>Gagal Proses</p>
                    </div>
                    <div class="icon"><i class="bi bi-envelope-x-fill"></i></div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="small-box bg-danger">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="log_avg">0</h3>
                        <p>Rata<sup>2</sup> Proses</p>
                    </div>
                    <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>            
        </div><!-- row statistik-->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tabelData" class="table table-bordered table-hover dataTable dtr-inline">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>Admin</th>
                                    <th>Tipe</th>
                                    <th>Template</th>
                                    <th>Email</th>
                                    <th class="desktop">Judul</th>
                                    <th>Proses</th>
                                    <th>Status</th>
                                    <th class="none">Keterangan</th>
                                    <th class="none">Dibuat</th>
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

<?= $this->section("js") ?>
<script>
    const langText = {
        removeTitle: '<?= lang('App.remove-title') ?>',
        removeReset: '<?= lang('App.remove-reset') ?>',
        removeText: '<?= lang('App.remove-text') ?>'
    };
</script>
<script src="<?= base_url('plugin/datatables/datatables.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/helper_statistik.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/helper_form.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/helper_format.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/page_log_email.min.js') ?>" defer></script>
<?= $this->endSection() ?>