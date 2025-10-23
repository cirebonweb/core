<?php

namespace Cirebonweb\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Publish extends BaseCommand
{
    protected $group       = 'Cirebonweb';
    protected $name        = 'cirebonweb:publish';
    protected $description = 'Publish resources (views, public, config, database) from Cirebonweb package.';

    protected $usage   = 'php spark cirebonweb:publish [--all|--config|--database|--view|--language|--public|--writable]';
    protected $options = [
        '--all'      => 'Publish all resources',
        '--config'   => 'Publish app config files',
        '--database' => 'Publish app migrations/seeds',
        '--view'     => 'Publish app views files',
        '--language' => 'Publish language files',
        '--public'   => 'Publish public assets',
        '--writable' => 'Publish root writable',
    ];

    public function run(array $params)
    {
        // Baca opsi dengan CLI::getOption()
        $option = null;
        if (CLI::getOption('all')) {
            $option = 'all';
        } elseif (CLI::getOption('config')) {
            $option = 'config';
        } elseif (CLI::getOption('database')) {
            $option = 'database';
        } elseif (CLI::getOption('view')) {
            $option = 'view';
        } elseif (CLI::getOption('language')) {
            $option = 'language';
        } elseif (CLI::getOption('public')) {
            $option = 'public';
        } elseif (CLI::getOption('writable')) {
            $option = 'writable';
        }

        // Jika tidak ada opsi yang cocok
        if ($option === null) {
            CLI::error('Invalid or missing option.');
            CLI::write('Usage: php spark cirebonweb:publish [--all|--view|--language|--public|--database|--appconfig]');
            return;
        }

        // Jalankan publish sesuai opsi
        switch ($option) {
            case 'all':
                $this->publishConfig();
                $this->publishDatabase();
                $this->publishView();
                $this->publishLanguage();
                $this->publishPublic();
                $this->publishWritable();
                break;
            case 'config':
                $this->publishConfig();
                break;
            case 'database':
                $this->publishDatabase();
                break;
            case 'view':
                $this->publishView();
                break;
            case 'language':
                $this->publishLanguage();
                break;
            case 'public':
                $this->publishPublic();
                break;
            case 'writable':
                $this->publishWritable();
                break;
        }
    }

    private function publishConfig(): void
    {
        $from = realpath(__DIR__ . '/../../src/Publish/Config');
        $to   = APPPATH . 'Config';
        $this->copyDirectory($from, $to, 'App config');
    }

    private function publishDatabase(): void
    {
        $from = realpath(__DIR__ . '/../../src/Publish/Database');
        $to   = APPPATH . 'Database';
        $this->copyDirectory($from, $to, 'App Database (migrations & seeds)');
    }

    private function publishView(): void
    {
        $from = realpath(__DIR__ . '/../../src/Publish/Views');
        $to   = APPPATH . 'Views';
        $this->copyDirectory($from, $to, 'App Views');
    }

    private function publishLanguage(): void
    {
        $from = realpath(__DIR__ . '/../../src/Publish/Language');
        $to   = APPPATH . 'Language';
        $this->copyDirectory($from, $to, 'Language files');
    }

    private function publishPublic(): void
    {
        $from = realpath(__DIR__ . '/../../src/Publish/Public');
        $to   = FCPATH;
        $this->copyDirectory($from, $to, 'Public assets');
    }

    private function publishWritable(): void
    {
        $from = realpath(__DIR__ . '/../../src/Publish/Writable');
        $to   = ROOTPATH . 'writable';
        $this->copyDirectory($from, $to, 'Root writable');
    }

    private function copyDirectory(string $from, string $to, string $label): void
    {
        if (!is_dir($from)) {
            CLI::error("[✗] Source folder for {$label} not found: {$from}");
            return;
        }

        helper('filesystem');
        directory_mirror($from, $to, true);

        if (is_dir($to)) {
            CLI::write("[✓] {$label} published to {$to}", 'green');
        } else {
            CLI::error("[✗] Failed to publish {$label}");
        }
    }
}
