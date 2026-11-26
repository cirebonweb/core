<?= $this->extend('layout/template') ?>

<?= $this->section('css') ?>
<?= $this->include('plugin/css_tabel') ?>
<link rel="stylesheet" href="<?= versi('plugin/select2/select2.min.css') ?>">
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
                        <h3 class="stat-value" data-key="user_total">0</h3>
                        <p>Total User</p>
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
                        <h3 class="stat-value" data-key="user_aktif">0</h3>
                        <p>User Aktif</p>
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
                        <h3 class="stat-value" data-key="user_nonaktif">0</h3>
                        <p>User Nonaktif</p>
                    </div>
                    <div class="icon"><i class="bi bi-person-fill-slash text-danger"></i></div>
                </div>
            </div>
        </div><!-- row -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <select id="filter_active" class="form-control form-control-sm d-inline-block w-auto mx-1">
                            <option value=""># Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non Aktif</option>
                        </select>

                        <table id="tabelData" class="table table-bordered table-hover dataTable dtr-inline">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama User</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Hak Akses</th>
                                    <th class="desktop">Waktu Login</th>
                                    <th class="none">Waktu Buat</th>
                                    <th class="none">Waktu Ubah</th>
                                    <th class="no-export">Aksi</th>
                                    <th class="none no-export">Hak Akses</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="modal-div" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="width: 370px;">
        <div class="modal-content">
            <div class="modal-header bg-primary d-flex justify-content-center">
                <h5 class="modal-title"></h5>
            </div>

            <form id="formData" class="pl-3 pr-3" data-cek="true">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-4">
                                <label for="username">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="email">Akun Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span onclick="cekpwd()" class="input-group-text ikon" style="cursor:pointer"><i class="bi bi-eye-fill"></i></span>
                                    </div>
                                    <input type="password" id="password" name="password" class="form-control passwd">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm">Ulangi Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span onclick="cekpwd()" class="input-group-text ikon" style="cursor:pointer"><i class="bi bi-eye-fill"></i></span>
                                    </div>
                                    <input type="password" id="password_confirm" name="password_confirm" class="form-control passwd">
                                </div>
                            </div>

                            <div class="mb-3" id="groupField">
                                <label for="password_confirm">Hak Akses <span class="text-danger">*</span></label>
                                <div class="form-group">
                                    <?php
                                    $rawGroupOptions = $groupOptions ?? [];
                                    $groupLabelMap   = $groupOptionLabels ?? [];
                                    $optionMap       = [];
                                    if (is_array($groupLabelMap) && $groupLabelMap !== []) {
                                        foreach ($groupLabelMap as $k => $v) {
                                            $key = is_int($k) ? (string) $v : (string) $k;
                                            $optionMap[$key] = is_string($v) ? $v : $key;
                                        }
                                    } elseif (is_array($rawGroupOptions)) {
                                        foreach ($rawGroupOptions as $k => $v) {
                                            $key = is_int($k) ? (string) $v : (string) $k;
                                            $optionMap[$key] = is_string($v) ? $v : $key;
                                        }
                                    }
                                    ?>
                                    <select id="group" name="group[]" class="select2" multiple="multiple" style="width: 100%;" <?= empty($canManageGroup) ? 'disabled' : '' ?> required>
                                        <?php foreach ($optionMap as $groupKey => $groupLabel): ?>
                                            <option value="<?= esc($groupKey) ?>"><?= esc($groupLabel) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small id="groupHelp" class="form-text text-muted"><?= empty($canManageGroup) ? 'Hak akses tidak dapat diubah dengan akun ini.' : 'Pilih satu atau lebih hak akses pengguna.' ?></small>
                                </div>
                            </div>

                            <!-- <div class="row">
                                <div class="col-md-6">
                                    <label for="aktivasi"> Status <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text"><i class="bi bi-person-fill-check"></i></label>
                                        </div>
                                        <select id="aktivasi" name="aktivasi" class="form-control">
                                            <option value="1">Aktif</option>
                                            <option value="0">Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div> -->

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="btnSubmit" class="btn btn-primary mr-1 float-right">Simpan</button>
                        <button type="button" id="btnClose" class="btn btn-danger float-right" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?= $this->include('plugin/js_tabel_form') ?>
<script src="<?= versi('vendor/js/helper_statistik.min.js') ?>" defer></script>
<script src="<?= versi('plugin/select2/select2.min.js') ?>" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?= file_get_contents(__DIR__ . '/user_list.js') ?>
    });
</script>
<?= $this->endSection() ?>