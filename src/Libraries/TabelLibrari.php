<?php

namespace Cirebonweb\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\IncomingRequest;
use Closure;

class TabelLibrari
{
    protected BaseBuilder $builder;
    protected IncomingRequest $request;
    protected array $searchable = [];
    protected array $orderable = [];
    protected ?array $defaultOrder = null;
    protected ?Closure $rowCallback = null;

    public function __construct(BaseBuilder $builder, IncomingRequest $request)
    {
        $this->builder = $builder;
        $this->request = $request;
    }

    public function setSearchable(array $cols): self
    {
        $this->searchable = $cols;
        return $this;
    }

    /**
     * Lists map sequentially to request columns marked orderable, or may use explicit DataTables indexes.
     */
    public function setOrderable(array $cols): self
    {
        $this->orderable = $cols;
        return $this;
    }

    public function setDefaultOrder(?array $order): self
    {
        $this->defaultOrder = $order;
        return $this;
    }

    public function setRowCallback(callable $cb): self
    {
        $this->rowCallback = Closure::fromCallable($cb);
        return $this;
    }

    public function getResult(): array
    {
        $post = $this->request->getPost();
        $draw = intval($post['draw'] ?? 1);
        $start = intval($post['start'] ?? 0);
        $length = intval($post['length'] ?? config('Crb')->crud['length']);
        $searchValue = $post['search']['value'] ?? null;

        // === 1. HITUNG TOTAL RECORDS SEBELUM FILTER SEARCH GLOBAL ===
        // Mengkloning builder dasar (yang sudah membawa where/join filter kustom dari controller)
        $totalBuilder = clone $this->builder;
        $recordsTotal = $totalBuilder->countAllResults(false);

        // === 2. PENCARIAN GLOBAL ===
        if ($searchValue && !empty($this->searchable)) {
            $keyword  = $this->escapeLike($searchValue);
            $this->builder->groupStart();
            foreach ($this->searchable as $col) {
                $this->builder->orLike($col, $keyword);
            }
            $this->builder->groupEnd();
        }

        // === 3. HITUNG FILTERED RECORDS SETELAH SEARCH GLOBAL ===
        $filteredBuilder = clone $this->builder;
        $recordsFiltered = $filteredBuilder->countAllResults(false);

        // === 4. ORDERING ===
        $orderColIndex = $post['order'][0]['column'] ?? null;
        $orderDir = strtolower($post['order'][0]['dir'] ?? 'asc');
        $appliedOrder = false;

        $orderableByIndex = $this->orderable;
        if (array_is_list($this->orderable)) {
            $sortableIndexes = [];
            foreach ($post['columns'] ?? [] as $index => $column) {
                if (is_array($column) && filter_var($column['orderable'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $sortableIndexes[] = (int) $index;
                }
            }

            $orderableByIndex = [];
            foreach ($this->orderable as $position => $column) {
                if (! isset($sortableIndexes[$position])) {
                    break;
                }
                $orderableByIndex[$sortableIndexes[$position]] = $column;
            }
        }

        if ($orderColIndex !== null && isset($orderableByIndex[$orderColIndex])) {
            $colName = $orderableByIndex[$orderColIndex];
            if (in_array($orderDir, ['asc', 'desc'], true)) {
                $this->builder->orderBy($colName, $orderDir);
                $appliedOrder = true;
            }
        }

        if (!$appliedOrder && !empty($this->defaultOrder)) {
            foreach ($this->defaultOrder as $c => $d) {
                $d = strtolower($d) === 'desc' ? 'desc' : 'asc';
                $this->builder->orderBy($c, $d);
            }
        }

        // === 5. PAGINATION & AMBIL DATA ===
        // Proteksi jika length bernilai -1 (artinya DataTables meminta 'All Data')
        if ($length != -1) {
            $this->builder->limit($length, $start);
        }

        $rows = $this->builder->get()->getResult();

        // === 6. PROSES CALLBACK ===
        $data = [];
        if ($this->rowCallback) {
            foreach ($rows as $r) {
                $data[] = call_user_func($this->rowCallback, $r);
            }
        } else {
            $data = $rows;
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
