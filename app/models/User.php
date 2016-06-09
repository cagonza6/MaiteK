<?php

namespace App\models;

use App\Database\Database;

class User extends Database{
	/*
	 * Load the user's data required after login to load it. It validates
	 * the password parameters.
	 * Input: $user_name, $user_pass
	 * Return: array|false
	 * */

	/*
	 * Loads the non sensitive information of the user with the given id
	 * Input: $id
	 * Return: array
	 * */
	public static function userData($id){
		$query = 'SELECT username, user_id AS id, password, email, gender, level, team, status, lang  FROM users WHERE user_id=:id Limit 1;';
		$queryData = array(':id' => $id,);
		return self::fetchOne($query, $queryData);
	}

	public static function searchbyEmail( $email){
		$query = 'SELECT user_id AS id, username AS username, email, lang FROM users WHERE email=:email LIMIT 1;';
		$queryData = array(':email' => $email,);

		return self::fetchOne($query, $queryData);
	}

	public static function loadUser($user_name){

		$query = 'SELECT username, real_name, password, user_id, email, status, gender, level, team, activated_at, lang FROM users WHERE username=:user_name Limit 1;';

		$queryData = array(
			':user_name' => $user_name,
		);
		$user = self::fetchOne($query, $queryData);
		return $user;
	}

	public static function setPassword($userId, $password){
		$query = 'UPDATE users SET password=:password WHERE user_id=:id ;';
		$queryData = array(
			':password' => $password,
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
