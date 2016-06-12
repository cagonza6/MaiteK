<?php

namespace App\models;

use App\Database\Database;

class User extends Database{
	/*
	 * Loads the user information with the given data
	 * @id       : user's Id
	 * @email    : user's email
	 * @username : user's username, the clean version of it
	 * Return: array with the users data
	 * */
	public static function userData($id, $email, $username){
		$queryData = [];
		$query = 'SELECT username, username_clean, real_name, password, user_id AS id, email, status, gender, level, team, activated_at, lang FROM users WHERE ';
		if ($id){
			$query .='user_id=:id ';
			$queryData[':id'] = $id;
		}
		elseif($email){
			$query .='email=:email ';
			$queryData[':email'] = $email;
		}
		elseif($username){
			$query .='username_clean=:username ';
			$queryData[':username'] = $username;
		}

		$query .= 'Limit 1';
		return self::fetchOne($query, $queryData);
	}

	public static function searchbyEmail($email){
		return self::userData(false, $email, false);
	}

	public static function loadUser($user_name){
		return self::userData(false, false, $user_name);
	}

	public static function setPassword($userId, $password){
		$query = 'UPDATE users SET password=:password WHERE user_id=:id ;';
		$queryData = array(
			':password' => $password,
			':id' => $userId,
		);
		self::executeQuery($query, $queryData);
	}

	public static function setLang($userId, $lang){
		$query = 'UPDATE users SET lang=:lang WHERE user_id=:id ;';
		$queryData = array(
			':lang' => $lang,
			':id' => $userId,
		);
		self::executeQuery($query, $queryData);
	}

	public static function createUser($username, $password, $email, $status, $level, $team){
		$status = $status?1:0;
		return self::newUser($username, $password, $email, $status, $level, $team);
	}

	public static function gravatarAvatar($email, $options=[]){
		$size = isset($options['size'])?$options['size']:100;
		return 'http://www.gravatar.com/avatar/'. md5($email). '?s=' . $size . '&d=identicon' ;
	}
}
