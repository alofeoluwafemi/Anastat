<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Table {

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
	public function gettables($databaseid)
	{
		$stmt = $this->db->query("SELECT * FROM `tables` WHERE database_id = {$databaseid} ");

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
		$stmt = $this->db->query("SELECT * FROM `tables`");

		if($stmt) 
		{
			$stmt->setFetchMode(\PDO::FETCH_ASSOC);

			//If PDO error
			getError($stmt);

			while($result = $stmt->fetch())
			{
				$this->results[] = $result;
			}
		}

		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	/**
	* Lists all Tables and affiliated databases
	*/
	public function listByTable($id)
	{
		$stmt = $this->db->query("SELECT * FROM `databases` INNER JOIN database_table ON databases.id = database_table.database_id INNER JOIN tables ON database_table.table_id = tables.id WHERE databases.id = '{$id}' ");

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
	 * List Pivot Columns
	*/
	public function pivot($databaseid)
	{
		$stmt = $this->db->query("SELECT * FROM `database_table` WHERE database_id = {$databaseid} ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$results[] = $result['table_id'];
		}
		
		return (is_null($results) OR empty($results)) ? array() : $results;
	}


	/**
	 * Sync
	*/
	public function sync($data)
	{
		extract($data);

		$stmt = $this->db->prepare("DELETE FROM database_table WHERE database_id =  :id");

		$stmt->bindParam(':id',$database,PDO::PARAM_INT);

		$stmt->execute();

		$array = ['database' => $database,'tables' => $tables];

		$this->assign($array);
	}

	/**
	 * Delete from Tables storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM tables WHERE id =  :id");

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
		$datas = array_combine($codes,$tables);

		foreach ($datas as $code => $table) {
			$query = $this->db->prepare('INSERT INTO tables (table_name,code) VALUES (:table_name,:code)');
			 
	        $query->bindParam(':table_name', 	$table,PDO::PARAM_STR);
	        $query->bindParam(':code', 			$code,PDO::PARAM_STR);
	             
	    	$query->execute();

	    	getError($query);
		}

	}

	/**
	 * Add new lists of tables to database
	 */
	public function assign($array)
	{
		// dd($array);
		extract($array);

		foreach ($tables as $table) {
			$query = $this->db->prepare('INSERT INTO database_table (database_id,table_id) VALUES (:database,:table)');
			 
	        $query->bindParam(':database', 	$database,PDO::PARAM_INT);
	        $query->bindParam(':table', 	$table,PDO::PARAM_INT);
	             
	    	$query->execute();

	    	getError($query);
		}

	}

	/**
	 * Edit
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `tables` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Update table details
	 */
	public function update($data)
	{
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE `tables` SET table_name = :table ,code = :code WHERE id = :table_id"); 

				$stmt->bindParam(':table', 			$table , PDO::PARAM_STR);       
	        	$stmt->bindParam(':code', 			$code,PDO::PARAM_STR);
				$stmt->bindParam(':table_id', $table_id, PDO::PARAM_INT);  

	    getError($stmt);
		
		$stmt->execute();
	}
}