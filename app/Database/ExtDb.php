<?php

namespace App\Database;

use \PDO;

class ExtDb {

	protected $connectionData;
	protected $conn = null;

	public function __construct($connectionData){
		$this->connectionData = $connectionData;
	}

	protected function connection() {
		if(!$this->conn) { // If no instance then make one
			$data = $this->connectionData;
			$connType = 'mysql:dbname='.$data['database'].';host='.$data['host'];
			$this->conn = new \PDO($connType, $data['username'], $data['password']);
		}
		return $this->conn;
	}

	protected function fetchOne($query, $queryData=array()){
		if (!$query)
			return new Dataobject();

		$stmt = $this->connection()->prepare($query);
		$stmt->setFetchMode(PDO::FETCH_CLASS , __NAMESPACE__ . '\\Dataobject');
		$stmt->execute($queryData);
		$user = $stmt->fetch();
		return $user;
	}

	// this is the only functionality that the external DB needs.
	public function selectExtUser($username){
		$data = $this->connectionData['tablesInfo'];

		$table = $data['usersTable'];
		$usernameield = $data['usernameField'];
		$passwordField = $data['passwordField'];
		$emailField = $data['emailField'];
		$saltField = $data['saltField'];

		$query = "SELECT {$passwordField} AS password, {$usernameield} AS username, {$emailField} AS email ";
		if ($saltField)
			$query .= ", {$saltField} AS salt ";
		$query .= " FROM {$table} ";
		$query .= "WHERE {$usernameield} = :uname ";
		$queryData = array(':uname'=>$username);

		$userData = $this->fetchOne($query, $queryData);
		if (!$userData || !$userData->username)
			$userData =  new Dataobject;
		return $userData;
	}
}

