<?php

namespace Cirebonweb\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class HtmlMinify implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // no-op
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (ENVIRONMENT === 'development') {
            return $response;
        }

        if (stripos($response->getHeaderLine('Content-Type'), 'text/html') === false) {
            return $response;
        }

        $buffer = $response->getBody();
        if ($buffer === '' || $buffer === null) {
            return $response;
        }

        // ── 1. Proteksi blok yang tidak boleh diminify ──────────────
        $blocks  = [];
        $patterns = [
            '#<script\b[^>]*>.*?</script>#is',       // inline JS
            '#<style\b[^>]*>.*?</style>#is',         // inline CSS
            '#<pre\b[^>]*>.*?</pre>#is',             // preformatted
            '#<textarea\b[^>]*>.*?</textarea>#is',   // textarea
        ];

        foreach ($patterns as $pattern) {
            $buffer = preg_replace_callback($pattern, function ($m) use (&$blocks) {
                $key = '%%MINIFY_BLOCK_' . count($blocks) . '%%';
                $blocks[$key] = $m[0];
                return $key;
            }, $buffer);
        }

        // ── 2. Minify HTML (aman, JS/CSS sudah diproteksi) ──────────
        $search = [
            '/<!--(?!\[if).*?-->/s',   // hapus komentar HTML (kecuali conditional)
            '/\>[^\S ]+/s',            // whitespace setelah tag
            '/[^\S ]+\</s',            // whitespace sebelum tag
            '/(\s)+/s',                // multi-whitespace → satu spasi
        ];
        $replace = ['', '>', '<', '\\1'];

        $buffer = preg_replace($search, $replace, $buffer);

        // ── 3. Kembalikan blok yang diproteksi ──────────────────────
        $buffer = strtr($buffer, $blocks);

        $response->setBody($buffer);
        return $response;
    }
}