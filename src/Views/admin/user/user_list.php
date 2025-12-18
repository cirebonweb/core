<?= $this->extend('layout/template') ?>

<?= $this->section('css') ?>
<?= $this->include('plugin/tabel_css') ?>
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<section class="content">
    <div class="container-fluid">

        <div class="statContainer row">
            <div class="col-12 col-md-4 col-lg col-5">
                <div class="small-box bg-dark">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="user_total">0</h3>
                        <p>Total User</p>
                    </div>
                    <div class="icon"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg col-5">
                <div class="small-box bg-primary">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="user_aktif">0</h3>
                        <p>User Aktif</p>
                    </div>
                    <div class="icon"><i class="bi bi-person-fill-check"></i></div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg col-5">
                <div class="small-box bg-danger">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="user_nonaktif">0</h3>
                        <p>User Nonaktif</p>
                    </div>
                    <div class="icon"><i class="bi bi-person-fill-slash"></i></div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg col-5">
                <div class="small-box bg-success">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="user_admin">0</h3>
                        <p>User Admin</p>
                    </div>
                    <div class="icon"><i class="bi bi-person-fill"></i></div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-lg col-5">
                <div class="small-box bg-success">
                    <div class="overlay dark">
                        <span class="spinner-border wh-3rem" role="status"></span>
                    </div>
                    <div class="inner">
                        <h3 class="stat-value" data-key="user_klien">0</h3>
                        <p>User Klien</p>
                    </div>
                    <div class="icon"><i class="bi bi-person-fill"></i></div>
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
                        <select id="filter_group" class="form-control form-control-sm d-inline-block w-auto mx-1">
                            <option value=""># Hak Akses</option>
                            <?php foreach ($listGroup as $groupName): ?>
                                <option value="<?= esc($groupName) ?>"><?= ucfirst($groupName) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <table id="tabelData" class="table table-bordered table-hover dataTable dtr-inline">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama User</th>
                                    <th>Alamat Email</th>
                                    <th>Status</th>
                                    <th>Hak Akses</th>
                                    <th class="desktop">Terakhir Login</th>
                                    <th class="desktop">Waktu Dibuat</th>
                                    <th class="desktop">Waktu Dirubah</th>
                                    <th>Aksi</th>
                                    <th class="none">Status</th>
                                    <th class="none">Hak Akses</th>
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
    <div class="modal-dialog modal modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary d-flex justify-content-center">
                <h5 class="modal-title"></h5>
            </div>

            <form id="formData" class="pl-3 pr-3" data-cek="true">
                <div class="modal-body">
                    <input type="hidden" id="iduser" name="iduser">

                    <div class="row">
                        <!-- akun -->
                        <div class="col-12">
                            <div class="my-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Nama Lengkap" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Alamat Email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span onclick="cekpwd()" class="input-group-text ikon" style="cursor:pointer"><i class="bi bi-eye-fill"></i></span>
                                    </div>
                                    <input type="password" id="password" name="password" class="form-control passwd" placeholder="Password">
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span onclick="cekpwd()" class="input-group-text ikon" style="cursor:pointer"><i class="bi bi-eye-fill"></i></span>
                                    </div>
                                    <input type="password" id="password_confirm" name="password_confirm" class="form-control passwd" placeholder="Ulangi Password">
                                </div>
                            </div>

                            <div class="row">
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

                                <div class="col-md-6">
                                    <label for="akses"> Hak Akses <span class="text-danger">*</span></label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text"><i class="bi bi-person-fill-gear"></i></label>
                                        </div>
                                        <select id="akses" name="akses" class="form-control">
                                            <option value="klien">Klien</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="btnSubmit" class="btn btn-primary mr-1 float-right">Simpan</button>
                        <!-- <button type="button" id="btn-loading" class="btn btn-primary" disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Loading...
                        </button> -->
                        <button type="button" id="btnClose" class="btn btn-danger float-right" data-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?= $this->include('plugin/validasi_js') ?>
<script src="<?= base_url('plugin/datatables/datatables.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/helper_statistik.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/helper_form.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/helper_format.min.js') ?>" defer></script>
<script src="<?= base_url('vendor/js/page_user_list.min.js') ?>" defer></script>
<?= $this->endSection() ?>