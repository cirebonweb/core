<?php

namespace Cirebonweb\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class HtmlMinify implements FilterInterface
{
    /**
     * Do nothing on request.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // No action needed before controller
    }

    /**
     * Minify HTML output on response.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Pastikan kita tidak di lingkungan 'development' dan response-nya adalah HTML
        if (ENVIRONMENT === 'development' || strpos($response->getHeaderLine('Content-Type'), 'text/html') === false) {
            return $response;
        }

        $buffer = $response->getBody();

        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '//' // Remove HTML comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        // Optimasi: Gunakan Regex yang lebih sederhana dan fokus pada penghapusan spasi yang tidak perlu.
        $buffer = preg_replace($search, $replace, $buffer);

        // Tambahkan lagi minifikasi yang spesifik dari kode lama Anda untuk stripping
        $search_specific = [
            '/\>\s+\</', // strip whitespaces between tags
            '/(\"|\')\s+\>/', // strip whitespaces between quotation ("') and end tags
            '/=\s+(\"|\')/' // strip whitespaces between = "'
        ];

        $replace_specific = [
            '><',
            '$1>',
            '=$1'
        ];

        $buffer = preg_replace($search_specific, $replace_specific, $buffer);

        // Set ulang body response
        $response->setBody($buffer);

        return $response;
    }
}
