<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Frequency {

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
	public function getall($table_id)
	{
		$stmt = $this->db->query("SELECT * FROM `frequency_datas` WHERE table_id = '{$table_id}' ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		// var_dump( $this->results );
		return $this->results;
	}

	/**
	 * Fetch all  
	 * 
	**/
	public function lists($paginate = false)
	{
		$query = "SELECT DISTINCT frequency_datas.frequency_data_name,frequency_datas.id as id,frequency_datas.table_id,tables.table_name FROM 
								`frequency_datas` INNER JOIN tables on frequency_datas.table_id = tables.id ORDER BY tables.id";
		

		//Return Paginated data else
		if($paginate)
		{
			$pagination = new Paginator(null,$query,20);

			return $pagination;
		}else{

				$stmt = $this->db->query($query);

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


	/**
	 * Fetch all  
	 * 
	**/
	public function listsCategorized($paginate = false)
	{
		$query = "SELECT frequency_datas.frequency_data_name,frequency_datas.id as id,frequency_datas.table_id,tables.table_name FROM 
								`frequency_datas` LEFT JOIN tables on frequency_datas.table_id = tables.id ORDER BY tables.id";
		
		$childquery = "SELECT DISTINCT table_name FROM `frequency_datas` INNER JOIN `tables` 
						ON frequency_datas.table_id = tables.id
						WHERE frequency_data_name = '{frequency_data_name}' ";

		//Return Paginated data else
		if($paginate)
		{
			$pagination = new Paginator(['query' => $childquery,'placeholder' => '{frequency_data_name}','column' => 'frequency_data_name'],$query,20);

			return $pagination;
		}else{

				$stmt = $this->db->query($query);

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

	/**
	 * Delete from frequency
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM frequency_datas WHERE id =  :id");

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

		$query = $this->db->prepare('INSERT INTO  `frequency_datas`(frequency_data_name,table_id,code) VALUES(:frequency,:table,:code)');
		
	    $query->bindParam(':frequency', 	$frequency,PDO::PARAM_STR);
	    $query->bindParam(':table', 	$table,PDO::PARAM_INT);
	    $query->bindParam(':code', 	$code,PDO::PARAM_STR);

	    $query->execute();

	    getError($query);
	}

	/**
	* Edit frequency
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `frequency_datas` WHERE id =  :id");

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
		
		$stmt = $this->db->prepare("UPDATE `frequency_datas` SET frequency_data_name = :frequency,table_id = :table,code = :code WHERE id = :id"); 

				$stmt->bindParam(':frequency', $frequency , PDO::PARAM_STR); 
				$stmt->bindParam(':table', $table , PDO::PARAM_INT); 
				$stmt->bindParam(':code', $code , PDO::PARAM_STR); 
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);  

	    getError($stmt);
		
		$stmt->execute();
	}

}