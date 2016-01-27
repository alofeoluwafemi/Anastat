<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Survey {

	use Singleton;
	

	private $db;

	private $results;

	// protected static $instance;

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
		$stmt = $this->db->query("SELECT surveys.filename,surveys.pathname,surveys.id as id,sectors.name FROM `surveys`
								INNER JOIN sectors ON surveys.sector_id = sectors.id ORDER BY surveys.sector_id");

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
	 * 
	 */
	public function getSurveys($id)
	{
		$stmt = $this->db->query("SELECT surveys.filename,surveys.pathname FROM `surveys` 
									INNER JOIN sectors ON sectors.id = surveys.sector_id WHERE surveys.sector_id = '{$id}' ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	public function listSectors()
	{
		$stmt = $this->db->query("SELECT * FROM `sectors`");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$results[] = $result;
		}
		
		return (is_null($results) OR empty($results)) ? array() : $results;
	}

	/**
	 * Delete from storage
	*/
	public function drop($id)
	{
		/**
		 * Delete 
		 */
		$stmt = $this->db->prepare("DELETE FROM `surveys` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * Delete a sub-sector from storage
	*/
	public function dropsector($id)
	{
		/**
		 * Delete 
		 */
		$stmt = $this->db->prepare("DELETE FROM `sectors` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * Get a particular request details from storage
	 */
	public function get($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `surveys` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	public function addnew($data)
	{
		// dd($data);
		extract($data);
		$pathname = hasFile('file') ? getFileName('file') : 'default.txt';

		$query = $this->db->prepare('INSERT INTO `surveys` (filename,pathname,sector_id) VALUES (:filename,:pathname,:sector)');
			 
	        $query->bindParam(':filename', 	$filename,PDO::PARAM_STR);
	        $query->bindParam(':pathname', 	$pathname,PDO::PARAM_STR);
	        $query->bindParam(':sector', 	$sector,PDO::PARAM_STR);
	             
	    $query->execute();

	    getError($query);


		// dd(config('storage_path'));
	    if(hasFile('file')) move(config('storage_path_survey'),'file');
	}

	public function addnewsector($data)
	{
		extract($data);

		$query = $this->db->prepare('INSERT INTO `sectors` (name) VALUES (:name)');
			 
	    $query->bindParam(':name', 	$name,PDO::PARAM_STR);
	             
	    $query->execute();

	    getError($query);
	}


	/**
	 * Edit
	*/
	public function editsector($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `sectors` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Update table details
	 */
	public function updatesector($data)
	{
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE `sectors` SET name = :name WHERE id = :id"); 

				$stmt->bindParam(':name', $name , PDO::PARAM_STR);       
				$stmt->bindParam(':id',   $id,    PDO::PARAM_INT);  

	    getError($stmt);
		
		$stmt->execute();
	}

}