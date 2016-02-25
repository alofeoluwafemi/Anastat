<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Period {

	use Singleton;
	

	private $db;

	private $results;

	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}


	/**
	 * Fetch all tables for
	 * a database
	**/
	public function getall($frequency_id)
	{
		$stmt = $this->db->query("SELECT * FROM `periods` WHERE frequency_id = '{$frequency_id}' ORDER BY id");

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
	 * Fetch all  
	 * 
	 */
	public function lists()
	{
		$stmt = $this->db->query("SELECT periods.id as id,periods.frequency_id,periods.period_name,frequency_datas.frequency_data_name FROM 
								`periods` INNER JOIN frequency_datas on periods.frequency_id = frequency_datas.id ORDER BY frequency_datas.id");

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
	 * Delete from frequency
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM periods WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * Add new frequency
	 */
	public function addnew($array)
	{
		// dd($array);
		extract($array);

		$query = $this->db->prepare('INSERT INTO  `periods`(period_name,frequency_id) VALUES(:period,:frequency)');
		
	    $query->bindParam(':period', 	$period,PDO::PARAM_STR);
	    $query->bindParam(':frequency', 	$frequencyid,PDO::PARAM_INT);
	         
	    $query->execute();

	    getError($query);
	}

	/**
	* Edit frequency
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `periods` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Update frequency details
	*/
	public function update($data)
	{
		// dd($data);
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE `periods` SET period_name = :period,frequency_id = :frequency WHERE id = :id"); 

				$stmt->bindParam(':period', $period , PDO::PARAM_STR);
				$stmt->bindParam(':frequency', $frequencyid , PDO::PARAM_INT);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);  

	    getError($stmt);
		
		$stmt->execute();
	}

	public function getDateRange($table,$level,$frequency)
	{
		$query = "SELECT * FROM `variables` INNER JOIN `frequency_table_variable` 
				 ON variables.id = frequency_table_variable.variable_id
				 WHERE table_id =  {$table} AND level_id = {$level} AND frequency_id = {$frequency} LIMIT 1";

		$stmt = $this->db->query($query);

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$results['from'] = $result['date_from'];
			$results['to']   = $result['date_to'];
		}
		
		return (is_null($results) OR empty($results)) ? array() : $results;
	}

}