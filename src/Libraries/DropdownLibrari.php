<?php

namespace Cirebonweb\Libraries;

use CodeIgniter\Database\BaseBuilder;
use InvalidArgumentException;
use RuntimeException;

class DropdownLibrari
{
    /**
     * Mengembalikan daftar opsi dropdown dalam bentuk [{id, nama}, ...].
     */
    public function opsi(string $resource): array
    {
        $config = $this->resourceConfig($resource);
        $model  = new $config['model']();

        if ($config['callback'] !== null && method_exists($model, $config['callback'])) {
            return array_values($model->{$config['callback']}());
        }

        $rows = $this->query($model, $config)->findAll();

        $result = [];

        foreach ($rows as $row) {
            $row      = (array) $row;
            $result[] = [
                'id'   => $row[$config['value']] ?? null,
                'nama' => $row[$config['label']] ?? null,
            ];
        }

        return $result;
    }

    protected function query(object $model, array $config): BaseBuilder
    {
        $builder = $model->builder()->select([$config['value'], $config['label']]);

        foreach ($config['where'] as $field => $value) {
            $builder->where($field, $value);
        }

        $builder->orderBy($config['orderBy'] ?? $config['label'], 'ASC');

        if ($config['limit'] !== null) {
            $builder->limit((int) $config['limit']);
        }

        return $builder;
    }

    protected function resourceConfig(string $resource): array
    {
        $items = config('Dropdown')->items;

        if (! isset($items[$resource])) {
            throw new InvalidArgumentException('Dropdown tidak terdaftar: ' . $resource);
        }

        $config = array_merge([
            'value'    => 'id',
            'label'    => 'nama',
            'where'    => [],
            'orderBy'  => null,
            'limit'    => null,
            'callback' => null,
        ], $items[$resource]);

        if (! isset($config['model'])) {
            throw new RuntimeException('Dropdown "' . $resource . '" belum mendefinisikan model.');
        }

        return $config;
    }
}