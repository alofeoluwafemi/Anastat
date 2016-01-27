<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Variable {

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
	public function getall($table_id,$level_id)
	{
		// dd(func_get_args());
		$stmt = $this->db->query("SELECT * FROM `variables` WHERE table_id = '{$table_id}' AND level_aggregation_id = '{$level_id}' ");

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
	 * Lists all Variables
	*/
	public function lists($paginate = false)
	{
		$query = "SELECT variables.id,variables.variable_name,variables.table_id,variables.level_aggregation_id,tables.table_name FROM `variables` INNER JOIN tables ON variables.table_id = tables.id";

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
	 * Delete from Tables storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM variables WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * Add new variable
	 */
	public function addnew($array)
	{
		// dd($array);
		extract($array);

		// $query = $this->db->query("INSERT INTO variables (variable_name,table_id,level_aggregation_id) VALUES('$variable','{$table}','{$level}')");
		$query = $this->db->prepare('INSERT INTO  `variables`(variable_name,table_id,level_aggregation_id,code) VALUES(:variable,:table,:level,:code)');
		
	    $query->bindParam(':variable', 	$variable,PDO::PARAM_STR);
	    $query->bindParam(':table', 	$table,PDO::PARAM_INT);
	    $query->bindParam(':level', 	$level,PDO::PARAM_INT);
	    $query->bindParam(':code', 		$variable,PDO::PARAM_STR);
	         
	    $query->execute();

	    getError($query);

	}

	/**
	* Edit
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `variables` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Update table details
	*/
	public function update($data)
	{
		// dd($data);
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE `variables` SET variable_name = :variable,table_id = :table,level_aggregation_id = :level,code = :code WHERE id = :id"); 

				$stmt->bindParam(':variable', $variable , PDO::PARAM_STR); 
				$stmt->bindParam(':table', $table , PDO::PARAM_INT); 
				$stmt->bindParam(':level', $level , PDO::PARAM_INT); 
				$stmt->bindParam(':code', $code , PDO::PARAM_STR); 
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 


	    getError($stmt);
		
		$stmt->execute();
	}

}