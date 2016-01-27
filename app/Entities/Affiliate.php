<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Affiliate {

	use Singleton;
	

	private $db;

	private $results;

	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}


	public function count()
	{
		$query = $this->db->query("SELECT COUNT(*) AS count FROM `affiliates`");
		
		$this->results = $query->fetch();
		// var_dump($this->results);
		return (is_null($this->results) OR empty($this->results)) ? 0 : $this->results['count'];
	}


	public function addnew($array)
	{

		extract($array);

		$query = $this->db->prepare('INSERT INTO affiliates (affiliate_name,affiliate_code) VALUES (:affiliate_name,:affiliate_code)');
			 
	        $query->bindParam(':affiliate_name', 	$affiliate,PDO::PARAM_STR);
	        $query->bindParam(':affiliate_code', 	$code,PDO::PARAM_STR);
	             
	    $query->execute();

	    getError($query);
	}

	/**
	* Edit
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `affiliates` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Fetch all tables for
	 * a database
	**/
	public function lists()
	{
		$stmt = $this->db->query("SELECT * FROM `affiliates` ORDER BY affiliate_name");

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
	 * Update
	 */
	public function update($data)
	{
		// dd($data);
		extract($data);

		$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
		$this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

		$stmt = $this->db->prepare("UPDATE `affiliates` SET affiliate_name = :affiliate,affiliate_code = :code WHERE id = :id"); 

				$stmt->bindParam(':affiliate', $affiliate, PDO::PARAM_STR);
				$stmt->bindParam(':code', $code, PDO::PARAM_STR); 
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 

	    $stmt->errorInfo();

		$stmt->execute();
	}

	/**
	 * Delete from Affliate storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM affiliates WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

}