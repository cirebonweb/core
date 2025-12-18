<?php

namespace Cirebonweb\Models\User;

use CodeIgniter\Model;

class UserModel extends Model
{
	protected $table 		  	= 'users';
	protected $primaryKey 	  	= 'id';
	protected $returnType 	  	= 'object';
	protected $allowedFields  	= ['username', 'status', 'active'];
	protected $useTimestamps  	= true;
	protected $createdField   	= 'created_at';
	protected $updatedField   	= 'updated_at';
	protected $deletedField		= 'deleted_at';

	protected $validationRules = [
		'username' => 'required|min_length[3]|max_length[30]|regex_match[/\A[a-zA-Z0-9\.]+\z/]', // Nama user
		'active' => 'required|integer', // Aktivasi
	];

	protected $validationMessages = [
		'username' => [
			'required' => 'Nama user wajib diisi.!',
			'min_length' => 'Nama user minimal 3 karakter.!',
			'max_length' => 'Nama user maksimal 30 karakter.!',
			'regex_match' => 'Nama user hanya boleh berisi huruf, angka, dan titik.!',
		],
		'active' => [
			'required' => 'Aktivasi wajib diisi.!',
			'integer' => 'Aktivasi harus berupa angka.!'
		]
	];

	/**
	 * Query dasar untuk tabel server side user-list.
	 * @var \CodeIgniter\Database\BaseConnection $db
	 */
	public function tabel()
	{
		return $this->db->table('users a')
			->select('a.id iduser, a.username, a.active, a.created_at, a.updated_at,
			b.last_used_at, b.secret, c.group')
			->join('auth_identities b', 'b.user_id = a.id AND b.type = "email_password"', 'left')
			->join('auth_groups_users c', 'c.user_id = a.id', 'left');
	}

	/**
	 * Menampilkan data ajax modal edit.
	 * @uses src\Controllers\Admin\User\UserList.php:getId()
	 * @return object
	 */
	public function getId($id)
	{
		return $this
			->select('users.id iduser, users.username, users.active, aid.secret email, agu.group')
			->join('auth_identities aid', 'aid.user_id = users.id')
			->join('auth_groups_users agu', 'agu.user_id = users.id', 'left')
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
			->select('users.username, aid.secret email, aid.last_used_at, aid.created_at,
				upf.id id_profil, upf.perusahaan, upf.whatsapp, upf.telegram, upf.alamat, upf.foto')
			->join('auth_identities aid', 'aid.user_id = users.id')
			->join('user_profil upf', 'upf.user_id = users.id')
			->where('users.id', $id)
			->first();
	}
}
