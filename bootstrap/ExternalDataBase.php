<?php
if (!defined('MIT_INCLUSION')) exit;
require_once CORE_FOLDER . '/DataObject.php';

class ExtDb {

	static $conn = null;
	protected static $data = null;
	private function __construct(){}

	protected static function conn() {
		if(!self::$conn) { // If no instance then make one
			self::$data = include CONF_FOLDER.'/database_ext.php';
			$connType = 'mysql:dbname='.self::$data['dbName'].';host='.self::$data['dbHost'];
			self::$conn = new PDO($connType, self::$data['dbUser'], self::$data['dbPass']);
		}
		return self::$conn;
	}

	protected static function fetchOne($query, $queryData=array()){
		if (!$query)
			return new DataObject();
		$conn = self::conn();

		$stmt = $conn->prepare($query);
		$stmt->setFetchMode(PDO::FETCH_CLASS , 'DataObject');
		$stmt->execute($queryData);
		$user = $stmt->fetch();
		return $user;
	}

	/* Core functions*/
	public static function selectExtUser($username, $password){

		$username = trim($username); // cleans the username
		$password = trim($password); // cleans the username
		$passField = self::$data['userPassField'];
		$uNameField = self::$data['userNameField'];
		$mailField = self::$data['userEmailField'];
		$uIdField = self::$data['useridField'];
		$extUtable = self::$data['userUserTable'];
		
		$query = "SELECT {$passField} AS user_pass, {$uNameField} AS user_name, {$mailField} AS user_email, ";
		$query .= "{$uIdField} AS user_id ";
		$query .= " FROM {$extUtable} ";
		$query .= "WHERE {$uNameField} = :uname ";

		$queryData = array(':uname'=>$username);

		$udata = self::fetchOne($query, $queryData);
		if (!$udata->user_id)
			return false;
		return password_verify($password, $udata->user_pass)?$udata:false;
	}

	/*
	 * Checks if the given username exists in the external database
	 * Input: @username
	 * Return: boolean
	*/

	public static function userExistsEXT($username){
		if (!$username)
			return false;
		// gets the pointer to the database
		$conn = self::conn();
		$uNameField = self::$data['userNameField'];
		$extUtable = self::$data['userUserTable'];

		$query = "SELECT {$uNameField} AS username FROM {$extUtable} WHERE {$uNameField} = :username LIMIT 1;";
		$queryData = array(
			':username' => $username,
		);

		$user = self::fetchOne($query, $queryData);;
		if($user->username)
			return true;
		return false;
	}

}

