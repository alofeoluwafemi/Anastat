<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Database {

	use Singleton;
	

	private $db;

	private $results;

	// protected static $instance;

	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}


	/**
	 * Fetch all micro database
	**/
	public function allmicros()
	{
		$stmt = $this->db->query("SELECT * FROM `databases` WHERE type = 'micro' ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}

		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	public function allmacros()
	{
		$stmt = $this->db->query("SELECT * FROM `databases` WHERE type = 'macro' ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);
		
		// $stmt->closeCursor();
		
		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$results[] = $result;
		}

		return (is_null($results) OR empty($results)) ? array() : $results;
	}

	/**
	 * Fetch all tables for
	 * a database
	**/
	public function lists()
	{
		$stmt = $this->db->query("SELECT * FROM `databases` ORDER BY type,databasename");

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
	 * Delete from storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM `databases` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * Add new 
	*/
	public function addnew($array)
	{

		extract($array);

		$query = $this->db->prepare('INSERT INTO `databases` (databasename,shortcode,type,help) VALUES (:databasename,:shortcode,:type,:help)');
			 
	        $query->bindParam(':databasename', 	$database,PDO::PARAM_STR);
	        $query->bindParam(':shortcode', 	$code,PDO::PARAM_STR);
	        $query->bindParam(':type', 			$type,PDO::PARAM_INT);
	        $query->bindParam(':help', 			$help,PDO::PARAM_STR);
	             
	    $query->execute();

	    getError($query);
	}

	/**
	 * Edit
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `databases` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	public function update($data)
	{
		// dd($data);
		extract($data);

		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
		$this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

		$stmt = $this->db->prepare("UPDATE `databases` SET type = :type,databasename = :database,shortcode = :shortcode,help = :help WHERE id = :id"); 

				$stmt->bindParam(':type', $type, PDO::PARAM_STR);       
				$stmt->bindParam(':database', $database, PDO::PARAM_STR);  
	        	$stmt->bindParam(':shortcode', 	$code,PDO::PARAM_STR);
	        	$stmt->bindParam(':help', 			$help,PDO::PARAM_STR);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 


	    $stmt->errorInfo();

		$stmt->execute();
	}
}