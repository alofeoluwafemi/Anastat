<?php

namespace App\Entities;

use App\Singleton;
use PDO;

class User{
	
	use Singleton;
	
	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}

		/**
		 * Fetch all tables for
		 * a database
		**/
	public function lists()
	{
		$stmt = $this->db->query("SELECT * FROM `users` ORDER BY id");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}

		// var_dump($this->results);
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	/**
	 * Get a user
	*/
	public function get($id)
	{

		$stmt = $this->db->prepare("SELECT * FROM `users` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	* Get a user
	*/
	public function getwithUsername($username)
	{

		$stmt = $this->db->prepare("SELECT * FROM `users` WHERE username =  :username");

		$stmt->bindParam(':username',$username,PDO::PARAM_STR);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	* Get a user
	*/
	public function getwithAffiliate($affiliateid)
	{

		$stmt = $this->db->prepare("SELECT * FROM `users` WHERE affiliate_id =  :affiliateid");

		$stmt->bindParam(':affiliateid',$affiliateid,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Get a user
	*/
	public function login($username,$password)
	{
		// dd(func_get_args());
		$stmt = $this->db->prepare("SELECT * FROM `users` WHERE username =  :username AND password = :password AND type = 'admin' ");

		$stmt->bindParam(':username',$username,PDO::PARAM_STR);
		$stmt->bindParam(':password',$password,PDO::PARAM_STR);

		$stmt->execute();

		// dd($stmt->fetch());
		return $data = $stmt->fetch();
	}

	/**
	 * Login affiliate
	*/
	public function loginaffiliate($username,$password)
	{
		// dd(func_get_args());
		$stmt = $this->db->prepare("SELECT * FROM `users` WHERE username =  :username AND password = :password AND type = 'affiliate' ");

		$stmt->bindParam(':username',$username,PDO::PARAM_STR);
		$stmt->bindParam(':password',$password,PDO::PARAM_STR);

		$stmt->execute();

		// dd($stmt->fetch());
		return $data = $stmt->fetch();
	}

	/**
	 * Add new 
	*/
	public function addnew($array)
	{
		extract($array);

		$password = md5($password);
		$type     = "admin";

		$query = $this->db->prepare('INSERT INTO `users` (username,password,type,role) VALUES (:username,:password,:type,:role)');
			 
	        $query->bindParam(':username', 	$username,PDO::PARAM_STR);
	        $query->bindParam(':password', 	$password,PDO::PARAM_STR);
	        $query->bindParam(':type', 			$type,PDO::PARAM_STR);
	        $query->bindParam(':role', 			$role,PDO::PARAM_INT);
	             
	    $query->execute();

	    getError($query);
	}

	/**
	 * Add new affiliate
	*/
	public function addnewaffiliate($array)
	{
		// dd($array);
		extract($array);

		$password = md5($password);
		$type     = "affiliate";
		$role     = 0;

		$query = $this->db->prepare('INSERT INTO `users`(username,password,type,role,affiliate_id) 
												VALUES (:username,:password,:type,:role,:affiliateid)');
			 
	        $query->bindParam(':username', 	$username,PDO::PARAM_STR);
	        $query->bindParam(':password', 	$password,PDO::PARAM_STR);
	        $query->bindParam(':type', 			$type,PDO::PARAM_STR);
	        $query->bindParam(':role', 			$role,PDO::PARAM_INT);
	        $query->bindParam(':affiliateid', 	$affiliateid,PDO::PARAM_INT);
	             
	    $query->execute();

	    getError($query);
	}


	public function update($data)
	{
		// dd($data);
		extract($data);

		$password = md5($password);

		$stmt = $this->db->prepare("UPDATE `users` SET username = :username,password = :password,role = :role WHERE id = :id"); 

				$stmt->bindParam(':username', $username, PDO::PARAM_STR);       
				$stmt->bindParam(':password', $password, PDO::PARAM_STR);  
	        	$stmt->bindParam(':role', 		$role,PDO::PARAM_INT);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 

	    $stmt->errorInfo();

		$stmt->execute();
	}

	/**
	 * Delete from storage
	*/
	public function drop($id)
	{
		/**
		 * Delete clients
		*/
		$stmt = $this->db->prepare("DELETE FROM `users` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}
		
}

