<?php

namespace Cirebonweb\Controllers\Traits;

use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;

trait DropdownTrait
{
    public function getList($resource = null): ResponseInterface
    {
        if ($resource === null || $resource === '') {
            return $this->json(false, 'Dropdown tidak ditentukan.', null, 400);
        }

        try {
            return $this->response->setJSON(service('dropdown')->opsi($resource));
        } catch (InvalidArgumentException $e) {
            return $this->json(false, $e->getMessage(), null, 404);
        }
    }

}