<?php

namespace App;
use PDO;

class DB{

	use Singleton;
	
	/**
	 * database name
	*/
	private $database;

	/**
	 * database user
	*/
	private $username;

	/**
	 * database password
	*/
	private $password;

	private $conn;

	// private $instance;

	public function __construct()
	{
		$this->database = config('database');
		$this->username = config('db_user');
		$this->password = config('db_pass');
	}

	/**
	* Return a new connection object 
	*/
	public function conn()
	{

		$dsn = "mysql:dbname={$this->database};host=127.0.0.1";

		try {
		    return  new PDO($dsn, $this->username,$this->password);
		} catch (PDOException $e) {
		    echo 'Connection failed: ' . $e->getMessage();
		}
	}
}