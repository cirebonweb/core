<?php

namespace Cirebonweb\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CrbAkses implements FilterInterface
{
    /**
     * Terapkan kebijakan akses APP untuk modul route.
     *
     * @param list<string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! is_array($arguments) || count($arguments) !== 1 || ! is_string($arguments[0]) || trim($arguments[0]) === '') {
            throw new \InvalidArgumentException('Filter crbAkses memerlukan satu key modul.');
        }

        $module = $arguments[0];

        if (aksesModul($module)) {
            return null;
        }

        $modules = config('CrbAkses')->modules;
        if (($modules[$module] ?? null) === 'session') {
            return redirect()->route('login');
        }

        return service('response')
            ->setStatusCode(403)
            ->setBody('Forbidden');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
