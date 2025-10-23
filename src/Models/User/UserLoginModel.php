<?php

namespace Cirebonweb\Models\User;

use CodeIgniter\Model;

class UserLoginModel extends Model
{
    protected $table         = 'user_login';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'login_id',
        'perangkat',
        'os',
        'browser',
        'brand',
        'model',
        'negara',
        'wilayah',
        'distrik',
        'zona_waktu',
        'isp',
        'tipe',
        'error'
    ];
    protected $useTimestamps = false;
    protected $afterInsert   = ['clearStatsCache'];

    protected function clearStatsCache(array $data)
    {
        $cacheKey = 'statistik_user_login';
        cache()->delete($cacheKey);

        return $data;
    }
}