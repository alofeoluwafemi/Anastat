<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Client {

	use Singleton;
	

	private $db;

	private $results;

	// protected static $instance;

	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}

	public function count()
	{
		$query = $this->db->query("SELECT COUNT(*) AS count FROM `clients`");
		
		$this->results = $query->fetch();
		// var_dump($this->results);
		return (is_null($this->results) OR empty($this->results)) ? 0 : $this->results['count'];
	}

	/**
	 * Fetch all tables for
	 * a database
	**/
	public function lists()
	{
		$stmt = $this->db->query("SELECT DISTINCT clients.id as id,clients.name,affiliates.affiliate_name FROM `clients`
									 LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id GROUP BY name");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	public function listByAffiliate($id)
	{
		$stmt = $this->db->query("SELECT DISTINCT clients.id as id,clients.name,affiliates.affiliate_name FROM `clients`
		 							LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
		 							 WHERE affiliate_id = '{$id}' GROUP BY name");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	/**
	 * Delete from storage
	*/
	public function drop($id)
	{
		/**
		 * Delete clients
		 */
		$stmt = $this->db->prepare("DELETE FROM `requests` WHERE client_id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		/**
		 * Remove associated requests
		 */
		$stmt = $this->db->prepare("DELETE FROM `clients` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

	}

	/**
	 * Get a particular request details from storage
	 */
	public function get($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `clients` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Add new client and its requests
	 */
	public function addclient($data)
	{
		// dd($data);

		$update = $data;

		extract($data);

		$client = $this->getClientByEmail($email);

		if(!empty($client))
		{
	       // dd($client);

			//If client exist update client details
			return $this->updateClient($client[0]['id'],$update);

			$this->addRequests($client[0]['id'],$requestid,$clienttype);

		}else{
				// dd('new');

			$stmt = $this->db->prepare('INSERT INTO clients (name,sex,email,phone,address,position_instituition,identification_no,affiliate_id) 
                      					 VALUES (:name,:sex,:email,:phone,:address,:position_instituition,:identification_no,:affiliate_id)');
		 
                $stmt->bindParam(':name', 				 	$name,PDO::PARAM_STR);
                $stmt->bindParam(':sex', 				 	$sex,PDO::PARAM_STR);
                $stmt->bindParam(':email', 	 			$email,PDO::PARAM_STR);
                $stmt->bindParam(':phone',   				$phone,PDO::PARAM_STR);
                $stmt->bindParam(':address',   			 $address,PDO::PARAM_STR);
                $stmt->bindParam(':position_instituition',  $designation,PDO::PARAM_STR);
                $stmt->bindParam(':identification_no',    	 $idn,PDO::PARAM_INT);
                $stmt->bindParam(':affiliate_id',    	 	 $instituition,PDO::PARAM_INT);
             
	        $stmt->execute();

	        getError($stmt);

	       $clientid = $this->db->lastInsertId();
	       // dd($clientid);

			$this->addRequests($clientid,$requestid,$clienttype);

		}

	}

		/**
	 * Update client details if submiting another data
	 */
	private function updateClient($id,$data)
	{
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE clients SET name = :name ,sex = :sex, email = :email,phone = :phone, affiliate_id = :affiliate_id,
									address = :address,position_instituition = :designation,identification_no = :identification_no WHERE  id = :id");
		 
                $stmt->bindParam(':name', 				$name,PDO::PARAM_STR);
                $stmt->bindParam(':sex', 				$sex,PDO::PARAM_STR);
                $stmt->bindParam(':email', 	 			$email,PDO::PARAM_STR);
                $stmt->bindParam(':phone',   			$phone,PDO::PARAM_STR);
                $stmt->bindParam(':affiliate_id',   	$instituition,PDO::PARAM_STR);
                $stmt->bindParam(':address',   			$address,PDO::PARAM_STR);
                $stmt->bindParam(':designation',  		$designation,PDO::PARAM_STR);
                $stmt->bindParam(':identification_no',  $idn,PDO::PARAM_INT);
                $stmt->bindParam(':id',    				$id,PDO::PARAM_INT);
             
	    getError($stmt);
		
		$stmt->execute();
	}


	/**
	 * Insert requests relating to client
	*/
	private function addRequests($clientid,$requestid,$clienttype)
	{
	     // dd( func_get_args() );

	    $true = 1;
	    $approved = ($clienttype == "personal") ? 'YES' : 'NO';

		//Insert to foreign table
       $requestid = array_filter( array_unique($requestid) ); //Make unique and remove empty ones
       // dd($requestid);
       foreach ($requestid as $key => $value) {

				$stmt = $this->db->prepare("UPDATE requests SET client_id = :clientid,display = :true,approved = :approved WHERE id = :value"); 

				$stmt->bindParam(':clientid', $clientid , PDO::PARAM_INT);       
				$stmt->bindParam(':true', $true, PDO::PARAM_INT);  
				$stmt->bindParam(':value', $value, PDO::PARAM_INT);
				$stmt->bindParam(':approved', $approved, PDO::PARAM_STR); 

			$stmt->execute();

       }
	}

	/**
	 * 
	 */
	private function getClientByEmail($email)
	{
		$stmt = $this->db->query("SELECT * FROM `clients` WHERE email = '{$email}' ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

}