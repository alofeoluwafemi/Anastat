<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Caggregation {

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
	public function getcategories($level_aggregation_id)
	{
		$stmt = $this->db->query("SELECT * FROM `category_aggregations` WHERE level_aggregation_id = {$level_aggregation_id} ");

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
	 * Lists all Tables and affiliated databases
	*/
	public function lists($paginate = false)
	{
		$query = "SELECT * FROM `category_aggregations`";

		$childquery = "SELECT * FROM `category_level` INNER JOIN `level_aggregations` 
						ON category_level.level_id = level_aggregations.id
						WHERE category_id = '{category_id}' ";

		//Return Paginated data else
		if($paginate)
		{
			$pagination = new Paginator(['query' => $childquery,'placeholder' => '{category_id}','column' => 'id','fetch' => 'level_aggregation_name'],$query,20);

			return $pagination;
		}else{
				$stmt = $this->db->query($query);

				$stmt->setFetchMode(\PDO::FETCH_ASSOC);

				//If PDO error
				getError($stmt);

				$key = 0;

				while($result = $stmt->fetch())
				{
					$this->results[] = $result;

					$this->results[$key] = $result;

					$query = "SELECT * FROM `category_level` INNER JOIN `level_aggregations` 
							ON category_level.level_id = level_aggregations.id
							WHERE category_id =  '{$result['id']}' ";
					
					$tables = $this->db->query($query);

					while($data = $tables->fetch())
					{
						$this->results[$key]['tables'][] = $data['level_aggregation_name'];
					}

					$key++;	
				}
							
		}

		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;

	}

	/**
	 * Delete from Tables storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM category_aggregations WHERE id =  :id");

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

			$query = $this->db->prepare('INSERT INTO category_aggregations (category_aggregation_name,code) 
										VALUES (:category,:code)');
			 
	        $query->bindParam(':category', 	$category,PDO::PARAM_STR);
	        $query->bindParam(':code', 		$code,PDO::PARAM_STR);

	    	$query->execute();

	    	getError($query);

	    $id = $this->db->lastInsertId();

	    $data = ['category' => $id,'levels' => $levels];

		$this->assign($data);

	}



	/**
	 * Add new lists of tables to database
	 */
	public function assign($array)
	{
		// dd($array);
		extract($array);

		foreach ($levels as $level) {
			$query = $this->db->prepare('INSERT INTO category_level (category_id,level_id) VALUES (:category,:level)');
			 
	        $query->bindParam(':category', 	$category,PDO::PARAM_INT);
	        $query->bindParam(':level', 	$level,PDO::PARAM_INT);
	            
	    	getError($query);

	    	$query->execute();

		}

	}

	/**
	 * Edit
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `category_aggregations` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		$category =  $data = $stmt->fetch();

		if(!empty($category))
		{

			$query = $this->db->query("SELECT * FROM `category_level` WHERE category_id =  '{$category['id']}' ");

			while($data = $query->fetch())
			{
					$category['levels'][] = $data['level_id'];
			}

			return $category;

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
		
		$stmt = $this->db->prepare("UPDATE `category_aggregations` SET category_aggregation_name = :category,code = :code WHERE id = :id"); 

				$stmt->bindParam(':category', $category , PDO::PARAM_STR);       
				$stmt->bindParam(':code', 		$code, PDO::PARAM_STR);  
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);  

	    getError($stmt);
		
		$stmt->execute();

		$array = ['category' => $id,'levels' => $levels];
		$this->sync($array);
	}


	/**
	* Sync
	*/
	public function sync($data)
	{
		extract($data);

		$stmt = $this->db->prepare("DELETE FROM category_level WHERE category_id =  :id");

		$stmt->bindParam(':id',$category,PDO::PARAM_INT);

		$stmt->execute();

		$array = ['category' => $category,'levels' => $levels];

		$this->assign($array);
	}
}