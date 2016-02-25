<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Condition {

	use Singleton;
	

	private $db;

	private $results;

	// protected static $instance;

	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}

	/**
	 *
	 */
	public function get()
	{
		$stmt = $this->db->query("SELECT * FROM `agreement` WHERE id =  1");

		return $data = $stmt->fetch();
	}

	/**
	 * Update
	 */
	public function update($data)
	{
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE `agreement` SET content = :content WHERE id = 1"); 

				$stmt->bindParam(':content', $agreement , PDO::PARAM_STR);       

	    getError($stmt);
		
		$stmt->execute();
	}

}