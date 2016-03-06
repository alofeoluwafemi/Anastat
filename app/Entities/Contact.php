<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Contact {

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
		$stmt = $this->db->query("SELECT * FROM `contact` WHERE id =  1");

		return $data = $stmt->fetch();
	}

	/**
	 * Update
	 */
	public function update($data)
	{
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE `contact` SET address = :address,contact = :contact,facebook = :facebook,twitter = :twitter,google = :google WHERE id = 1"); 

		$stmt->bindParam(':address', $address , PDO::PARAM_STR);       
		$stmt->bindParam(':contact', $contact , PDO::PARAM_STR);       
		$stmt->bindParam(':facebook',$facebook , PDO::PARAM_STR);       
		$stmt->bindParam(':twitter', $twitter , PDO::PARAM_STR);       
		$stmt->bindParam(':google',  $google , PDO::PARAM_STR);       

	    getError($stmt);
		
		$stmt->execute();
	}

}