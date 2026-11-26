<?php

namespace Cirebonweb\Controllers\Traits;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\ResponseInterface;
use Cirebonweb\Libraries\TabelLibrari;
use RuntimeException;

/**
 * CrudTrait - Trait untuk operasi CRUD standar (DataTables, Get ID, Simpan, Hapus)
 *
 * STRUKTUR & ALUR KERJA:
 * ----------------------
 * 1. tabel()      : Mengembalikan data untuk DataTables (server-side processing)
 *    - Validasi AJAX request
 *    - Bangun query builder via crudBuilder()
 *    - Set searchable/orderable columns
 *    - Apply row callback dataTabel() wajib diimplementasikan controller
 *    - Return JSON DataTables format
 *
 * 2. getId()      : Ambil single record by ID
 *    - Validasi ID numerik
 *    - Coba model->getIdCustom($id) jika exists, else model->find($id)
 *    - Return JSON {success, data}
 *
 * 3. simpan()     : Insert ATAU Update (otomatis deteksi via $data['id'])
 *    - Validasi AJAX request
 *    - Ambil data dari dataSimpan() method controller atau $_POST
 *    - Convert empty string '' -> null (semua field)
 *    - Deteksi update: !empty($data['id']) -> ID '0' dianggap INSERT (design choice)
 *    - Model->save($data) handles both insert/update
 *    - Return JSON {success, messages, data}
 *
 * 4. hapus()      : Delete by ID
 *    - Validasi ID numerik
 *    - Model->delete($id) langsung (tanpa find() dulu)
 *    - Handle FK constraint error dengan custom message via $fkErrorMessage property
 *
 * DEPENDENCIES (wajib di Controller yang use trait ini):
 * ------------------------------------------------------
 * - $modelClass          : Class Model (diset di CrbBaseController)
 * - dataTabel($row)      : Method untuk format row DataTables (callback)
 * - Opsional: searchable[]  : Kolom pencarian
 * - Opsional: orderable[]   : Kolom sorting
 * - Opsional: defaultOrder() : Default sorting
 * - Opsional: dataSimpan()   : Custom data sebelum simpan
 * - Opsional: dataSimpanResponse() : Custom response setelah simpan
 * - Opsional: builderTabel() / filterTabel() : Custom query builder
 * - Opsional: fkErrorMessage  : Custom FK error message
 *
 * DESIGN DECISIONS (BUKAN BUG, SENGAAJA):
 * ---------------------------------------
 * 1. ID '0' (string) dianggap INSERT, bukan UPDATE
 *    Alasan: Controller bisa punya method insert() & update() terpisah
 *
 * 2. Semua empty string '' dikonversi ke NULL
 *    Alasan: Normalisasi data, DB schema pakai NULL bukan ''
 *
 * 3. Tidak validasi field wajib di trait (delegasi ke Model validation)
 *
 * KNOWN LIMITATIONS / CATATAN:
 * ----------------------------
 * - Trait bergantung pada CrbBaseController untuk: $model, crudModel(), ajax(), json()
 * - Model HARUS punya method tabel() atau builder() untuk crudRawBuilder()
 * - DataTables library: Cirebonweb\Libraries\TabelLibrari
 * - PHP 8.1+: Menggunakan arrow function (Closure::fromCallable deprecated)
 *
 * @package Cirebonweb\Controllers\Traits
 */
trait CrudTrait
{
    public function tabel(): ResponseInterface
    {
        if (($res = $this->ajax()) !== null) return $res;

        $builder = $this->crudBuilder();
        $dataTable = new TabelLibrari($builder, $this->request);
        $dataTable->setSearchable($this->crudSearchable());
        $dataTable->setOrderable($this->crudOrderable());

        if (method_exists($this, 'defaultOrder')) $dataTable->setDefaultOrder($this->defaultOrder());
        if (! method_exists($this, 'dataTabel')) throw new RuntimeException(static::class . ' harus mengimplementasi dataTabel()');

        $dataTable->setRowCallback(fn($row) => $this->dataTabel($row));

        return $this->response->setJSON($dataTable->getResult());
    }

    public function getId(): ResponseInterface
    {
        if (($res = $this->ajax()) !== null) return $res;

        $id = $this->request->getPost('id');
        if (! $id || ! is_numeric($id)) return $this->json(false, 'ID tidak valid', null, 400);

        $model = $this->crudModel();
        $data  = is_callable([$model, 'getIdCustom']) ? $model->getIdCustom($id) : $model->find($id);

        if (! $data) return $this->json(false, 'Data tidak ditemukan', null, 404);
        return $this->json(true, null, $data);
    }

    public function simpan(): ResponseInterface
    {
        if (($res = $this->ajax()) !== null) return $res;

        $data = method_exists($this, 'dataSimpan') ? $this->dataSimpan() : $this->request->getPost();

        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        $isUpdate = ! empty($data['id']);
        $model = $this->crudModel();

        try {
            if (! $model->save($data)) return $this->json(false, $model->errors());

            $pesan = $isUpdate ? lang('App.update-success') : lang('App.insert-success');
            $responseData = method_exists($this, 'dataSimpanResponse') ? $this->dataSimpanResponse($isUpdate, $data) : null;

            return $this->json(true, $pesan, $responseData);
        } catch (\Throwable $e) {
            log_message('critical', __METHOD__ . ': ' . $e->getMessage());
            return $this->json(false, 'Critical: ' . $e->getMessage());
        }
    }

    public function hapus(): ResponseInterface
    {
        if (($res = $this->ajax()) !== null) return $res;

        $id = $this->request->getPost('id');
        if (! $id || ! is_numeric($id)) return $this->json(false, 'ID tidak valid');

        $model = $this->crudModel();

        try {
            if ($model->delete($id)) return $this->json(true, lang('App.delete-success'));
            return $this->json(false, lang('App.delete-error'));
        } catch (\Throwable $e) {
            log_message('critical', __METHOD__ . ': ' . $e->getMessage());

            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                $pesan = property_exists($this, 'fkErrorMessage')
                    ? $this->fkErrorMessage
                    : 'Data tidak dapat dihapus karena masih digunakan pada data/menu lain.';

                return $this->json(false, $pesan);
            }

            return $this->json(false, 'Critical: ' . $e->getMessage());
        }
    }

    protected function crudBuilder(): BaseBuilder
    {
        if (method_exists($this, 'builderTabel')) return $this->builderTabel();
        if (method_exists($this, 'filterTabel')) return $this->filterTabel($this->crudRawBuilder());
        return $this->crudRawBuilder();
    }

    protected function crudRawBuilder(): BaseBuilder
    {
        $model = $this->crudModel();
        return method_exists($model, 'tabel') ? $model->tabel() : $model->builder();
    }
}
