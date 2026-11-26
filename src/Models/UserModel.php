<?php

namespace Cirebonweb\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table 		  	= 'users';
	protected $primaryKey 	  	= 'id';
	protected $useAutoIncrement = true;
	protected $returnType 	  	= 'object';
	protected $useSoftDeletes   = false;
	protected $protectFields    = true;
	protected $allowedFields  	= ['username', 'status', 'active'];

	protected $useTimestamps  	= true;
	protected $createdField   	= 'created_at';
	protected $updatedField   	= 'updated_at';
	protected $deletedField		= 'deleted_at';

	protected $validationRules = [
		'id' => [
			'label' => 'ID',
			'rules' => 'permit_empty|is_natural_no_zero'
		],
		'username' => [
			'label' => 'Nama Lengkap',
			'rules' => 'required|min_length[3]|max_length[30]|regex_match[/\A[a-zA-Z0-9\.]+\z/]|is_unique[users.username,id,{id}]'
		],
		'active' => [
			'label' => 'Aktivasi',
			'rules' => 'required|integer'
		]
	];

	protected $validationMessages = [];
	protected $skipValidation     = false;

	/**
	 * Query dasar untuk tabel server side user-list.
	 * @var \CodeIgniter\Database\BaseConnection $db
	 */
	public function tabel()
	{
		return $this->db->table('users a')
			->select('a.id, a.username, a.active, a.created_at, a.updated_at, b.last_used_at, b.secret, GROUP_CONCAT(c.`group` SEPARATOR ", ") as `group`', false)
			->join('auth_identities b', 'b.user_id = a.id AND b.type = "email_password"', 'left')
			->join('auth_groups_users c', 'c.user_id = a.id', 'left')
			->groupBy('a.id');
	}

	/**
	 * Menampilkan data ajax modal edit.
	 * @param mixed $id
	 */
	public function getIdCustom($id)
	{
		return $this
			->select('users.id, users.username, users.active, aid.secret, COALESCE(agu.`group`, "") as `group`', false)
			->join('auth_identities aid', 'aid.user_id = users.id')
			->join('(SELECT user_id, GROUP_CONCAT(DISTINCT `group` ORDER BY `group` SEPARATOR ", ") as `group` FROM auth_groups_users GROUP BY user_id) agu', 'agu.user_id = users.id', 'left')
			->where('users.id', $id)
			->first();
	}

	/**
	 * Menampilkan data profil user.
	 * @uses app\Controllers\Profil.php:index()
	 * @return object
	 */
	public function getProfil($id)
	{
		return $this
			->select('users.username, aid.secret email, aid.last_used_at, aid.created_at, upf.id id_profil, upf.perusahaan, upf.whatsapp, upf.telegram, upf.alamat, upf.foto')
			->join('auth_identities aid', 'aid.user_id = users.id')
			->join('user_profil upf', 'upf.user_id = users.id')
			->where('users.id', $id)
			->first();
	}
}
