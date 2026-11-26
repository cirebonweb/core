<?= $this->extend('layout/template.php') ?>

<?= $this->section('css') ?>
<?= $this->include('plugin/css_tabel') ?>
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<section class="content">
    <div class="container-fluid">
        <!-- <div class="row justify-content-md-center"> -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title text-white">Durasi Cache</h3>
                    </div>
                    <div class="card-body">

                        <form id="cacheForm" data-cek="true">
                            <!-- Cache Device Detector -->
                            <div class="form-group row">
                                <label for="cache_device" class="col-12 col-lg-4 col-form-label">User Device</label>
                                <div class="col-12 col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><label class="input-group-text"><i class="bi bi-alarm"></i></label></div>
                                        <input type="number" id="cache_device" name="cache_device" class="form-control" value="<?= esc($durasiCache['cache_device']) ?>" min="0" step="60">
                                        <div class="input-group-append"><div class="input-group-text menit"></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cache Statistik User List -->
                            <div class="form-group row">
                                <label for="statistik_user_list" class="col-12 col-lg-4 col-form-label">Stat User List</label>
                                <div class="col-12 col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><label class="input-group-text"><i class="bi bi-alarm"></i></label></div>
                                        <input type="number" id="statistik_user_list" name="statistik_user_list" class="form-control" value="<?= esc($durasiCache['statistik_user_list']) ?>" min="0" step="60">
                                        <div class="input-group-append"><div class="input-group-text menit"></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cache Statistik User Login -->
                            <div class="form-group row">
                                <label for="statistik_user_login" class="col-12 col-lg-4 col-form-label">Stat User Login</label>
                                <div class="col-12 col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-prepend"><label class="input-group-text"><i class="bi bi-alarm"></i></label></div>
                                        <input type="number" id="statistik_user_login" name="statistik_user_login" class="form-control" value="<?= esc($durasiCache['statistik_user_login']) ?>" min="0" step="60">
                                        <div class="input-group-append"><div class="input-group-text menit"></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cache Statistik Log Email -->
                            <div class="form-group row">
                                <label for="statistik_log_email" class="col-12 col-lg-4 col-form-label">Stat Log Email</label>
                                <div class="col-12 col-lg-8">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend"><label class="input-group-text"><i class="bi bi-alarm"></i></label></div>
                                        <input type="number" id="statistik_log_email" name="statistik_log_email" class="form-control" value="<?= esc($durasiCache['statistik_log_email']) ?>" min="0" step="60">
                                        <div class="input-group-append"><div class="input-group-text menit"></div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Submit -->
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button type="submit" id="cacheSubmit" class="btn btn-dark">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8 mb-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title text-white">File Cache</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabelData" class="table table-bordered table-hover dataTable">
                            <thead>
                                <tr>
                                    <th class="all">No.</th>
                                    <th class="all">Tipe</th>
                                    <th class="all">Durasi</th>
                                    <th class="min-tablet-l">Berakhir</th>
                                    <th class="desktop">Dibuat</th>
                                    <th class="none">Nama File</th>
                                    <th class="none">Cache TTL</th>
                                    <th class="min-tablet-l no-export">Aksi</th>
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
<?= $this->include('plugin/js_tabel_form') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?= file_get_contents(__DIR__ . '/cache.js') ?>
    });
</script>
<?= $this->endSection() ?>