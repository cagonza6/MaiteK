<?php

namespace App\Database;

use \PDO;

class Database {

	protected static $connectionData;
	protected static $conn = null;

	public function __construct(){}

	public function addConnection($connectionData){
		self::$connectionData = $connectionData;
	}

	protected static function connection() {
		if(!self::$conn) { // If no instance then make one
			$data = self::$connectionData;
			$connType = 'mysql:dbname='.$data['database'].';host='.$data['host'];
			self::$conn = new \PDO($connType, $data['username'], $data['password']);
		}
		return self::$conn;
	}

	protected static function fetchOne($query, $queryData=array()){
		if (!$query)
			return new Dataobject();

		$stmt = self::connection()->prepare($query);
		$stmt->setFetchMode(PDO::FETCH_CLASS , __NAMESPACE__ . '\\Dataobject');
		$stmt->execute($queryData);
		$user = $stmt->fetch();
		return $user ? $user:new Dataobject();
	}

	protected static function fetchAll($query, $queryData=array()){
		if (!$query)
			return [];

		$stmt = self::connection()->prepare($query);
		$stmt->setFetchMode(PDO::FETCH_CLASS , __NAMESPACE__ . '\\Dataobject');
		$stmt->execute($queryData);
		$data = $stmt->fetchAll();
		return $data;
	}

	protected static function executeQuery($query, $queryData){
		if (!$query)
			return new DataObject();

		$stmt = self::connection()->prepare($query);
		return $stmt->execute($queryData);
	}

						/*
						 * General Methods
						*/
	/*
	 * This method check if the given email exists in the database
	 * input: @email
	 * return: string | boolean
	 * */
	public static function emailInUse($mail){
		$mail = trim($mail);
		$query = 'SELECT count(email) AS count FROM users WHERE email=:mail;';
		$queryData = array(
			':mail' => $mail,
		);
		$emails = self::fetchOne($query, $queryData);

		return (int)$emails->count ? true:false;
	}

	/*
	 * Checks if the given username exists in the database
	 * Input: @username
	 * Return: boolean
	 * */
	public static function userExists($username){
		if (trim($username)=='')
			return false;
		$query = 'SELECT username FROM users WHERE username=:username LIMIT 1;';
		$queryData = array(
			':username' => $username,
		);
		$user = self::fetchOne($query, $queryData);

		if($user->username)
			return true;
		return false;
	}

						/*
						 * Create Account Methods
						*/

	/*
	 * Creates a new user's entry in the database with the given parameters
	 * Input: $username, $password, $email, $level
	 * Return: int
	 * */
	public static function newUser($username, $password, $email, $status, $level, $team){
		$conn = self::connection();
		$status = $status?1:0;
		$level= (int) $level;

		$query = 'INSERT INTO users (username, password, email, level, status, team ) ';
		$query .= 'values( :username, :password, :email, :level, :status, :team ); ';
		$queryData = array(
			':username' => $username,
			':password' => $password,
			':email' => $email,
			':level' => $level,
			':status' => $status,
			':team' => $team,
		);

		$stmt = $conn->prepare($query);
		$stmt->execute($queryData);
		return $conn->lastInsertId();
	}

	/*
	 * Sets the value of active account for the account with the given ID
	 * It also erases the token that is not required
	 * */
	public static function activateAccount($id){
		$id = (int)$id;
		$query  = 'UPDATE users SET status =:status, activated_at = now() WHERE user_id =:id';
		$queryData = array(
			':id' => $id,
			':status' => 1,
		);
		self::executeQuery($query, $queryData);
	}

	/*
	 * Generates a new token to activate the account, it will be used with
	 * a mail to activate the account
	 * */
	public static function generateAccountToken($account_id, $email, $token, $autoActive=false){
		$status = $autoActive?0:1;

		$query = 'INSERT INTO users_creation_log (user_id, email, token, status) ';
		$query .= 'values( :id, :email, :token, :status); ';

		$queryData = array(
			':id' => $account_id,
			':email' => $email,
			':token' => $token,
			':status' => $status,
		);

		self::executeQuery($query, $queryData);
	}

	public static function deleteAccountToken($user_id){
		$query  = 'UPDATE users_creation_log SET token = null, status = 0 WHERE user_id=:user_id ;';
		$queryData = array(
			':user_id' => $user_id,
		);
		self::executeQuery($query, $queryData);
	}

	/*
	 * For a given account and token, it gets the values from the DB
	 * since those values are always given in pairs, it will check them
	 * in pairs
	 * */
	public static function getUserCreationToken($email){
		$query  = 'SELECT user_id, email, token FROM users_creation_log WHERE ';
		$query .= 'status = 1 AND email=:email LIMIT 1';

		$queryData = array(':email' => $email,);

		return self::fetchOne($query, $queryData);
	}

						/*
						 * Password Recovery Functions
						*/

	public static function generateRecoverryToken($userid, $email, $token){
		$query = 'INSERT INTO users_recoveryPassword_log (user_id, email,  token) ';
		$query .= 'values( :userid, :email, :token );';
		$queryData = array(
			':userid' => $userid,
			':email' => $email,
			':token' => $token,
		);
		self::executeQuery($query, $queryData);
		return $queryData;
	}

	public static function deleteRecoverryToken($user_id){
		$query  = 'UPDATE users_recoveryPassword_log SET token = null, status = 0 WHERE user_id=:user_id and id>0;';
		$queryData = array(':user_id' => $user_id,);

		self::executeQuery($query, $queryData);
	}

	public static function getRecoveryToken($email){
		$query  = 'SELECT id, user_id, email, token FROM users_recoveryPassword_log WHERE email=:email AND status=1 LIMIT 1';
		$queryData = array(':email' => $email,);
		return self::fetchOne($query, $queryData);
	}

						/*
						 * Email Change functions
						 * 
						*/

	public static function createEmailchangeToken($user_id, $old_email, $new_email, $token){
		$query = 'INSERT INTO users_changeEmail_log (user_id, old_email,  new_email, token) values( :user_id, :old_email, :new_email, :token ); ';
		$queryData = array(
			':user_id' => $user_id,
			':old_email' => $old_email,
			':new_email' => $new_email,
			':token' => $token
		);
		self::executeQuery($query, $queryData);
	}

	public static function deleteEmailChangeToken($user_id){
		$query  = 'UPDATE users_changeEmail_log SET token = null, status = 0 WHERE user_id=:user_id and id>0;';
		$queryData = array(':user_id' => $user_id,);

		self::executeQuery($query, $queryData);
	}

	public static function getEmailChangeToken($email){
		$query  = 'SELECT user_id, old_email, new_email, token FROM users_changeEmail_log ';
		$query .= 'WHERE  status=1 AND new_email=:email ORDER BY id DESC LIMIT 1';
		$queryData = array(':email' => $email,);

		return self::fetchOne($query, $queryData);
	}

	public function changeMail($id, $email){

		$query = 'UPDATE users SET user_email=:email WHERE user_id=:id ;';
		$queryData = array(
			':email' => $email,
			':id' => $id,
		);
		self::executeQuery($query, $queryData);
	}

						/*
						 * Profile Changes
						*/


	public static function setEmail($id, $email){
		$query = 'UPDATE users SET email=:email WHERE user_id=:id ;';
		$queryData = array(
			':email' => $email,
			':id' => $id,
		);
		self::executeQuery($query, $queryData);
	}

	public function setSex($id, $sex){
		$query = 'UPDATE users SET gender=:sex WHERE user_id=:id ;';
		$queryData = array(
			':sex' => $sex,
			':id' => $id,
		);
		self::executeQuery($query, $queryData);
	}

	public function setTeam($id, $team){
		$query = 'UPDATE users SET team=:team WHERE user_id=:id ;';
		$queryData = array(
			':team' => $team,
			':id' => $id,
		);
		self::executeQuery($query, $queryData);
	}

	public function setLevel($id, $level){
		$query = 'UPDATE users SET level=:level WHERE user_id=:id ;';
		$queryData = array(
			':level' => $level,
			':id' => $id,
		);
		self::executeQuery($query, $queryData);
	}
};
?>
