<?php

namespace Cirebonweb\Models;

use CodeIgniter\Model;

class AuthSesiModel extends Model
{
    protected $table            = 'auth_sesi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'remember_id', 'perangkat', 'os', 'browser', 'dibuat'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = false;
    protected $updatedField  = false;
    protected $deletedField  = false;

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Menampilkan sesi perangkat user.
     * @see app\Controllers\Profil.php -> getPerangkat
     * @return Object
     */
    public function getProfil(int $userId, int $perPage, int $expiration)
    {
        return $this->select(
            'id, ip_address, timestamp, remember_id, perangkat, os, browser, dibuat, UNIX_TIMESTAMP(timestamp) AS timestamp_unix',
            false
        )
            ->where('user_id', $userId)
            ->where('timestamp >= NOW() - INTERVAL ' . max(0, $expiration) . ' SECOND', null, false)
            ->orderBy('timestamp', 'DESC')
            ->paginate($perPage);
    }

    public function getSesiNonAktif($userId, $sesiId)
    {
        return $this->select('id, remember_id')
            ->where('user_id', $userId)
            ->where('id !=', $sesiId)
            ->findAll();
    }

    public function hapusSesi($sesiId)
    {
        return $this->where('id', $sesiId)->delete();
    }
}