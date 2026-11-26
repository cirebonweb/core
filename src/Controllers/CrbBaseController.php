<?php

namespace Cirebonweb\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\SessionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

abstract class CrbBaseController extends Controller
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
     * @var mixed $modelClass
     */
    protected $modelClass;

    /**
     * @var mixed $model
     */
    protected $model;

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

    protected function crudModel()
    {
        if (! isset($this->model)) throw new RuntimeException(static::class . ' belum mengisi properti $model');
        return $this->model;
    }

    protected function crudSearchable(): array
    {
        return property_exists($this, 'searchable') ? (array) $this->searchable : [];
    }

    protected function crudOrderable(): array
    {
        return property_exists($this, 'orderable') ? (array) $this->orderable : [];
    }

    protected function ajax(): ?ResponseInterface
    {
        if ($this->request->isAJAX()) return null;
        return $this->json(false, 'Permintaan tidak valid.', null, 400);
    }

    protected function json(bool $success, array|string|null $message = null, $data = null, int $status = 200): ResponseInterface
    {
        $payload = ['success' => $success];

        if ($message !== null) $payload['messages'] = $message;
        if ($data !== null) $payload['data'] = $data;

        return $this->response->setStatusCode($status)->setJSON($payload);
    }
}
