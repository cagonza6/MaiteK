<?php

namespace App\Auth;

use App\Models\User;

class Auth {

	public function __construct($session){
		$this->session = $session;
	}

	public function user(){
		return User::userData($this->session->user, false, false);
	}

	public function check(){
		return isset($this->session->user);
	}

	public function attempt($username, $password){
		// grab user by username
		$user = User::loadUser($username);

		if (!$user)
			return false;

		if((bool)(int)$user->id && !(int)$user->status){
			return null;
		}

		if(password_verify($password, $user->password)){
			$this->session->user = (int)$user->id;
			return true;
		}

		return false;
	}

	public function logout(){
		$this->session->destroy();
	}

	public function userExists($username){
		$user = User::loadUser($username);
		return $user->username?true:false;
	}
}
