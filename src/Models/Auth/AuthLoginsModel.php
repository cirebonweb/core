<?php

namespace Cirebonweb\Models\Auth;

use CodeIgniter\Model;

class AuthLoginsModel extends Model
{
	protected $table 		  	= 'auth_logins';
	protected $primaryKey 	  	= 'id';
	protected $returnType 	  	= 'object';
	protected $allowedFields  	= ['ip_address', 'user_agent', 'id_type', 'identifier', 'user_id', 'date', 'success'];
	protected $useTimestamps  	= false;

	public function tabel()
    {
        return $this->db->table('auth_logins a')
            ->select('a.id auth_id, ip_address, user_agent, id_type, identifier, date, success,
                perangkat, os, browser, brand, model, negara, wilayah, distrik, zona_waktu, isp, tipe, error, username')
            ->join('user_login b', 'b.login_id = a.id', 'left')
            ->join('users c', 'c.id = a.user_id', 'left');
    }

    public function getProfil($userId)
    {
        return $this
            ->select('auth_logins.*, user_login.*')
            ->join('user_login', 'user_login.login_id = auth_logins.id', 'left')
            ->where('user_id', $userId)
            ->first();
    }

    public function cekNull()
    {
        return $this->select('id')->limit(1)->get()->getRow();
    }
}