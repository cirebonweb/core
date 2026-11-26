<?php

namespace Cirebonweb\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Publish resource core ke project.
 *
 * Dua kebutuhan berbeda, baca `ARSITEKTUR.md` §4:
 *
 *  - Sekali seumur proyek  → bootstrap `app/Config`, `app/Views`, dan `public/`.
 *  - Rutin (update core)   → core dibaca langsung dari paket, TIDAK ada yang perlu di-publish ulang.
 *
 * Karena itu perintah ini menolak menimpa file yang sudah berubah kecuali diberi `--force`.
 *
 * @see \Cirebonweb\Config\CrbRoutes  Route core didaftarkan app, bukan hasil publish.
 */
class Publish extends BaseCommand
{
    protected $group       = 'Cirebonweb';
    protected $name        = 'cirebonweb:publish';
    protected $description = 'Publish resource (config, views, controllers, public) dari paket core.';

    protected $usage = 'php spark cirebonweb:publish [--all|--config|--controllers|--views|--public|--writable] [--force] [--dry-run]';

    protected $options = [
        '--all'         => 'Publish semua grup dan semua berkas sumbernya.',
        '--config'      => 'Publish app/Config.',
        '--controllers' => 'Publish app/Controllers.',
        '--views'       => 'Publish app/Views.',
        '--public'      => 'Publish aset ke public/.',
        '--writable'    => 'Publish writable/root.',
        '--force'       => 'Timpa file yang isinya sudah berbeda dari sumber.',
        '--dry-run'     => 'Tampilkan rencana tanpa menulis file.',
    ];

    /**
     * Grup yang tersedia => [label, sumber relatif ke `src/`, tujuan relatif ke app].
     *
     * Tujuan ditulis sebagai path relatif, bukan `APPPATH . Config`:
     * titik di dalam string literal adalah karakter biasa, bukan operator concat,
     * sehingga akan membuat folder bernama ` . Config`.
     *
     * @return array<string, array{label:string, from:string, to:string}>
     */
    protected function groups(): array
    {
        return [
            'config'      => ['label' => 'Config',      'from' => 'Publish/Config',            'to' => 'app/Config'],
            'controllers' => ['label' => 'Controllers', 'from' => 'Publish/Controllers',       'to' => 'app/Controllers'],
            'views'       => ['label' => 'Views',       'from' => 'Publish/Views',              'to' => 'app/Views'],
            // `to` WAJIB 'public', bukan ''. Tujuan dihitung `ROOTPATH . $to`, jadi '' akan
            // menyalin ke ROOT proyek (dulu: .htaccess & upload/ jatuh ke luar public/).
            'public'      => ['label' => 'Public',      'from' => 'Publish/Public',             'to' => 'public'],
            'writable'    => ['label' => 'Writable',    'from' => 'Publish/Writable',           'to' => 'writable'],
        ];
    }

    public function run(array $params)
    {
        $force  = (bool) CLI::getOption('force');
        $dryRun = (bool) CLI::getOption('dry-run');
        $groups = $this->selectedGroups();

        if ($groups === []) {
            CLI::error('Tidak ada opsi publish yang dipilih.');
            CLI::write('Usage: ' . $this->usage);
            return 1;
        }

        $total = ['copy' => 0, 'same' => 0, 'skip' => 0];

        foreach ($groups as $key) {
            CLI::newLine();
            $this->publishGroup($key, $force, $dryRun, $total);
        }

        CLI::newLine();
        CLI::write(sprintf(
            'Ringkasan: %d ditulis, %d identik, %d dilewati (ubah atau pakai --force)',
            $total['copy'],
            $total['same'],
            $total['skip']
        ));

        if ($dryRun) {
            CLI::write('Mode dry-run: tidak ada file yang ditulis.', 'yellow');
        }

        return 0;
    }

    /**
     * @return list<string> Kunci grup yang dipilih CLI.
     */
    protected function selectedGroups(): array
    {
        if (CLI::getOption('all')) {
            return array_keys($this->groups());
        }

        $selected = [];
        foreach (array_keys($this->groups()) as $key) {
            if (CLI::getOption($key)) {
                $selected[] = $key;
            }
        }

        return $selected;
    }

    /**
     * @param array{copy:int, same:int, skip:int} $total
     */
    protected function publishGroup(string $key, bool $force, bool $dryRun, array &$total): void
    {
        $groups = $this->groups();
        $group  = $groups[$key];
        CLI::write('== ' . $group['label'] . ' ==', 'cyan');
        $from   = ROOTPATH . 'vendor/cirebonweb/core/src/' . $group['from'];
        $to     = ROOTPATH . $group['to'];

        if (! is_dir($from)) {
            CLI::error('Sumber tidak ditemukan: ' . $from);
            return;
        }

        $this->copyTree(rtrim($from, '/\\'), rtrim($to, '/\\'), $force, $dryRun, $total);
    }

    /**
     * Salin semua berkas pada sumber grup secara rekursif.
     *
     * @param array{copy:int, same:int, skip:int} $total
     */
    protected function copyTree(
        string $from,
        string $to,
        bool $force,
        bool $dryRun,
        array &$total
    ): void {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $prefix = strlen(rtrim($from, '/\\')) + 1;

        foreach ($items as $item) {
            $relative = str_replace('\\', '/', substr($item->getPathname(), $prefix));

            $target = $to . '/' . $relative;

            if ($item->isDir()) {
                if (! is_dir($target) && ! $dryRun) {
                    mkdir($target, 0775, true);
                }
                continue;
            }

            $replaceDefault = match (true) {
                basename(str_replace('\\', '/', $from)) === 'Config' && $relative === 'Routes.php' => 'routes',
                basename(str_replace('\\', '/', $from)) === 'Controllers' && $relative === 'Home.php' => 'home',
                default => null,
            };
            $this->copyFile($item->getPathname(), $target, $force, $dryRun, $total, $replaceDefault);
        }
    }

    /**
     * @param array{copy:int, same:int, skip:int} $total
     */
    protected function copyFile(
        string $source,
        string $target,
        bool $force,
        bool $dryRun,
        array &$total,
        ?string $replaceDefault = null
    ): void {
        if (! is_file($target)) {
            if (! $dryRun) {
                if (! is_dir(dirname($target))) {
                    mkdir(dirname($target), 0775, true);
                }
                copy($source, $target);
            }
            CLI::write('  + ' . $this->relativeLabel($target), 'green');
            $total['copy']++;
            return;
        }

        if (filesize($source) === filesize($target)
            && hash_file('xxh128', $source) === hash_file('xxh128', $target)
        ) {
            $total['same']++;
            return;
        }

        if (! $force) {
            if ($replaceDefault !== null && $this->isDefaultBootstrapFile($target, $replaceDefault)) {
                if (! $dryRun) {
                    copy($source, $target);
                }
                CLI::write('  > ' . $this->relativeLabel($target) . '  (berkas bawaan CI4 diganti dengan contoh CORE)', 'yellow');
                $total['copy']++;
                return;
            }

            CLI::write('  ! ' . $this->relativeLabel($target) . '  (berbeda - pakai --force)', 'yellow');
            $total['skip']++;
            return;
        }

        if (! $dryRun) {
            copy($source, $target);
        }
        CLI::write('  > ' . $this->relativeLabel($target) . '  (ditimpa)', 'yellow');
        $total['copy']++;
    }

    private function isDefaultBootstrapFile(string $path, string $type): bool
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return false;
        }

        $defaultFile = match ($type) {
            'routes' => <<<'PHP'
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
PHP,
            'home' => <<<'PHP'
<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }
}
PHP,
            default => '',
        };

        $significantTokens = static function (string $php): array {
            return array_values(array_map(
                static fn (array|string $token): string => is_array($token) ? $token[1] : $token,
                array_filter(
                    token_get_all($php),
                    static fn (array|string $token): bool => is_string($token)
                        || ! in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)
                )
            ));
        };

        return $defaultFile !== ''
            && $significantTokens($content) === $significantTokens($defaultFile);
    }

    /**
     * Judul ringkas untuk log: path relatif terhadap root project.
     */
    protected function relativeLabel(string $path): string
    {
        $root = rtrim(ROOTPATH, '/\\') . '/';

        return str_starts_with($path, $root)
            ? substr($path, strlen($root))
            : basename($path);
    }
}