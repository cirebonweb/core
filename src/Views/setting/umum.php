<?= $this->extend('layout/template.php') ?>

<?= $this->section('css') ?>
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-md-center">
            
            <div class="col-md-7 mb-4">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#sistem" data-toggle="tab">Sistem</a></li>
                            <li class="nav-item"><a class="nav-link" href="#situs" data-toggle="tab">Situs</a></li>
                            <li class="nav-item"><a class="nav-link" href="#logo" data-toggle="tab">Logo</a></li>
                            <li class="nav-item"><a class="nav-link" href="#smtp" data-toggle="tab">Smtp</a></li>
                        </ul>
                    </div><!-- /.card-header -->

                    <div class="card-body">
                        <div class="tab-content">

                            <!-- sistem -->
                            <div class="tab-pane active fade show" id="sistem">
                                <?= $this->include('Cirebonweb\setting\umum\sistem') ?>
                            </div>
                            <!-- /.tab-pane situs -->

                            <!-- situs -->
                            <div class="tab-pane fade" id="situs">
                                <?= $this->include('Cirebonweb\setting\umum\situs') ?>
                            </div>
                            <!-- /.tab-pane situs -->

                            <!-- logo -->
                            <div class="tab-pane fade" id="logo">
                                <?= $this->include('Cirebonweb\setting\umum\logo') ?>
                            </div>
                            <!-- /.tab-pane logo -->

                            <!-- smtp -->
                            <div class="tab-pane fade" id="smtp">
                                <?= $this->include('Cirebonweb\setting\umum\smtp') ?>
                            </div>
                            <!-- /.tab-pane smtp -->

                        </div> <!-- /.tab-content -->
                    </div> <!-- /.card-body -->
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="<?= versi('plugin/jquery/jquery.validate.min.js') ?>" defer></script>
<script src="<?= versi('plugin/jquery/jquery.validate_id.min.js') ?>" defer></script>
<script src="<?= versi('plugin/jquery/additional-methods.min.js') ?>" defer></script>
<script src="<?= versi('vendor/js/helper_form.min.js') ?>" defer></script>
<script src="<?= versi('plugin/pica/pica.min.js') ?>" defer></script>
<script src="<?= versi('vendor/js/helper_upload_multi.min.js') ?>" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?= file_get_contents(__DIR__ . '/umum.js') ?>
    });
</script>
<?= $this->endSection() ?>