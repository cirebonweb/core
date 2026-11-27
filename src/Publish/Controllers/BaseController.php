<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

abstract class BaseController extends Controller
{
    protected SessionInterface $session;
    /**
     * @var mixed $request
     */
    protected $request;
    protected ValidationInterface $validation;
    protected ConnectionInterface $db;

    protected $helpers = ['url'];

    /**
     * Kontrak CORE: jangan hapus atau ubah nama/properti ini.
     * CrudTrait bergantung pada $modelClass untuk inisialisasi model dan
     * $model untuk seluruh operasi CRUD.
     */
    protected $modelClass;
    protected $model;

    /**
     * Kontrak CORE: pertahankan inisialisasi properti di sini agar controller
     * CORE yang memakai CrudTrait memperoleh model, session, validation, dan DB.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session    = service('session');
        $this->validation = service('validation');
        $this->db         = \Config\Database::connect();

        if ($this->modelClass !== null && ! isset($this->model)) {
            $this->model = model($this->modelClass);
        }
    }

    /**
     * Kontrak CORE CrudTrait: jangan hapus atau ubah nama/signature method ini.
     */
    protected function crudModel()
    {
        if (! isset($this->model)) {
            throw new RuntimeException(static::class . ' belum mengisi properti $model');
        }

        return $this->model;
    }

    /**
     * Kontrak CORE CrudTrait: pertahankan nama dan signature method ini.
     */
    protected function crudSearchable(): array
    {
        return property_exists($this, 'searchable') ? (array) $this->searchable : [];
    }

    /**
     * Kontrak CORE CrudTrait: pertahankan nama dan signature method ini.
     */
    protected function crudOrderable(): array
    {
        return property_exists($this, 'orderable') ? (array) $this->orderable : [];
    }

    /**
     * Kontrak CORE CrudTrait: jangan hapus atau ubah nama/signature method ini.
     */
    protected function ajax(): ?ResponseInterface
    {
        if ($this->request->isAJAX()) {
            return null;
        }

        return $this->json(false, 'Permintaan tidak valid.', null, 400);
    }

    /**
     * Kontrak CORE CrudTrait: jangan hapus atau ubah nama/signature method ini.
     */
    protected function json(bool $success, array|string|null $message = null, $data = null, int $status = 200): ResponseInterface
    {
        $payload = ['success' => $success];

        if ($message !== null) {
            $payload['messages'] = $message;
        }
        if ($data !== null) {
            $payload['data'] = $data;
        }

        return $this->response->setStatusCode($status)->setJSON($payload);
    }
}
