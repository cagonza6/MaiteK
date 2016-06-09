<?php

namespace App\Helpers;

class Hasher
{
	protected $config;

	public function __construct($config)
	{
		$this->config = $config;
	}

	public function hashPassword($password)
	{
		return password_hash(
			$password,
			$this->config['algo'],
			['cost' => $this->config['cost']
		]);
	}

	public function passwordCheck($password, $hash)
	{
		return password_verify($password, $hash);
	}

	public function hash($input)
	{
		return hash('sha256', $input);
	}

	public function hashCheck($known, $user)
	{
		return hash_equals($known, $user);
	}
}
