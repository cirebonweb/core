<?php

namespace Cirebonweb\Models;

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
            ->select('a.id auth_id, a.success, a.date, a.ip_address, a.identifier, a.user_agent, c.username')
            ->join('users c', 'c.id = a.user_id', 'left');
    }

    public function cekNull()
    {
        return $this->select('id')->limit(1)->get()->getRow();
    }
}