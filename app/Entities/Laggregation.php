<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Laggregation {

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
	public function getaggregations($table_id)
	{
		$stmt = $this->db->query("SELECT DISTINCT level_aggregation_name,id,code,table_id
		 						 FROM `level_aggregations` WHERE table_id = {$table_id} ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		return $this->results;
	}

	/**
	 * Lists all Tables and affiliated databases
	*/
	public function lists()
	{
		$stmt = $this->db->query("SELECT * FROM `level_aggregations`");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		// return $this->results;
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	/**
	 * Lists all Tables and affiliated databases
	*/
	public function all($paginate = false)
	{
		// $query = "SELECT * FROM `level_aggregations` INNER JOIN `tables` ON level_aggregations.id = level_table.table_id";
		$query = "SELECT * FROM `level_aggregations`";
		
		$childquery = "SELECT * FROM `level_table` INNER JOIN `tables` 
						ON level_table.table_id = tables.id
						WHERE level_id = '{level_id}' ";
		
		//Return Paginated data else
		if($paginate)
		{
			$pagination = new Paginator(['query' => $childquery,'placeholder' => '{level_id}','column' => 'id'],$query,2);

			return $pagination;
		}else{

			$stmt = $this->db->query($query);

			$stmt->setFetchMode(\PDO::FETCH_ASSOC);

			//If PDO error
			getError($stmt);

			$key = 0;

			while($result = $stmt->fetch())
			{
				$this->results[$key] = $result;

				$query = "SELECT * FROM `level_table` INNER JOIN `tables` 
						ON level_table.table_id = tables.id
						WHERE level_id = '{$result['id']}' ";
				
				$tables = $this->db->query($query);

				while($data = $tables->fetch())
				{
					$this->results[$key]['tables'][] = $data['table_name'];
				}

				$key++;

			}

			return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;

			}
	}

	/**
	 * Delete from Tables storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM level_aggregations WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * Add new lists of tables to database
	 */
	public function addnew($array)
	{
		// dd($array);
		extract($array);


		$query = $this->db->prepare('INSERT INTO level_aggregations (level_aggregation_name,code) VALUES (:level,:code)');
			 
	    $query->bindParam(':level', 	$level,PDO::PARAM_STR);
	    $query->bindParam(':code', 		$code,PDO::PARAM_STR);
	             
	    getError($query);

	    $query->execute();

	    $id = $this->db->lastInsertId();

	    $data = ['level' => $id,'tables' => $tables];

	    $this->assign($data);

	}

	/**
	 * Add new lists of tables to database
	 */
	public function assign($array)
	{
		// dd($array);
		extract($array);

		foreach ($tables as $table) {
			$query = $this->db->prepare('INSERT INTO level_table (level_id,table_id) VALUES (:level,:table)');
			 
	        $query->bindParam(':level', 	$level,PDO::PARAM_INT);
	        $query->bindParam(':table', 	$table,PDO::PARAM_INT);
	            
	    	getError($query);

	    	$query->execute();

		}

	}

	/**
	 * Edit
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `level_aggregations` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		$levels = $stmt->fetch();

		if(!empty($levels))
		{

			$query = $this->db->query("SELECT * FROM `level_table` INNER JOIN `tables` 
										ON level_table.table_id = tables.id
										WHERE level_id = '{$id}'");

			while($data = $query->fetch())
			{
					$levels['tables'][] = $data['table_id'];
			}

			return $levels;

		}else{
			return array();
		}
	}

	/**
	 * Update table details
	 */
	public function update($data)
	{		
		// dd($data);
		extract($data);
		
		$oldname  = $this->edit($id)['level_aggregation_name'];
		
		$stmt = $this->db->prepare("UPDATE `level_aggregations` SET level_aggregation_name = :level,code = :code WHERE id = :id"); 
					$stmt->bindParam(':level', $level , PDO::PARAM_STR);       
		        	$stmt->bindParam(':code', 		$code,PDO::PARAM_STR);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT); 


		getError($stmt);
			
		$stmt->execute();

		//Add new to List
		$data = ['level' => $id,'code' => $code,'tables' => $tables];

		$this->sync($data);
	}


	/**
	 * Sync
	*/
	public function sync($data)
	{
		extract($data);

		$stmt = $this->db->prepare("DELETE FROM level_table WHERE level_id =  :id");

		$stmt->bindParam(':id',$level,PDO::PARAM_INT);

		$stmt->execute();

		$array = ['level' => $level,'tables' => $tables];

		$this->assign($array);
	}

}