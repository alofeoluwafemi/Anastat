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
	public function getall($table,$level,$frequency)
	{
		$query = "SELECT * FROM `variables` INNER JOIN `frequency_table_variable` 
				 ON variables.id = frequency_table_variable.variable_id
				 WHERE table_id =  :table AND level_id = :level AND frequency_id = :frequency";

		$stmt = $this->db->prepare($query);

		$stmt->bindParam(':table', 	$table,PDO::PARAM_INT);
		$stmt->bindParam(':level', 	$level,PDO::PARAM_INT);
		$stmt->bindParam(':frequency', 	$frequency,PDO::PARAM_INT);

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		$stmt->execute();

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$results[] = $result;
		}
		
		return (is_null($results) OR empty($results)) ? array() : $results;
	}

	/**
	* @param $id
	* @return array
	* Select level for this table,
	* Select frequency for this table
	* Retrive variables connected with this table,levels retrieved & frequency
	*/
	public function listByTable($id)
	{
		$Frequencies    = App('App\Entities\Frequency')->gettableFrequency($id);				//Retrieve frequencies available for this table
		$Frequencies	= !empty($Frequencies) ? implode(',',$Frequencies) : '0';
		$Frequencies  	= '('. $Frequencies .')';												

		$levels      = App('App\Entities\Laggregation')->getTableaggregations($id);				//Retrieve aggregation available for this table
		$levels		 = !empty($levels) ? implode(',',$levels) : '0';
		$levels 	 = '('. $levels .')';														
		
		$query 		 = "SELECT * FROM `variables` INNER JOIN frequency_table_variable 
									ON variables.id = frequency_table_variable.variable_id  
									WHERE frequency_table_variable.table_id = '{$id}'
									AND  frequency_table_variable.frequency_id IN {$Frequencies}
									AND  frequency_table_variable.level_id IN {$levels} 
									GROUP BY generic_table_code";

		$stmt 		= $this->db->query($query);

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$results[]                = $result;
		}
		
		return (is_null($results) OR empty($results)) ? array() : $results;
	}

	/**
	 * Lists all Variables
	*/
	public function lists($paginate = false)
	{
		$query = "SELECT * FROM `variables` ";

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
				$results[] = $result;
			}
			
			return (is_null($results) OR empty($results)) ? array() : $results;
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
		extract($array);

		$data = array_combine($codes,$variables);

		foreach ($data as $code => $variable) 
		{
			$query = $this->db->prepare('INSERT INTO  `variables`(variable_name,code) VALUES(:variable,:code)');
		
		    $query->bindParam(':variable', 	$variable,PDO::PARAM_STR);
		    $query->bindParam(':code', 		$code,PDO::PARAM_STR);
		         
		    $query->execute();

		    getError($query);
		}
		
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
		
		$stmt = $this->db->prepare("UPDATE `variables` SET variable_name = :variable,code = :code WHERE id = :id"); 

				$stmt->bindParam(':variable', $variable , PDO::PARAM_STR); 
				$stmt->bindParam(':code', $code , PDO::PARAM_STR); 
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 


	    getError($stmt);
		
		$stmt->execute();
	}


	/**
	* Assign Variables to table
	*/
	public function parse($data)
	{
		$table     ="";
		$level     = "";
		$frequency = "";
		$variables = "";
		$from      = "";
		$to        = "" ;

		// dd($data);
		extract($data);

		foreach ($variables as $key => $variable) {

			$Table = App('App\Entities\Table')->edit($table);
			$Level = App('App\Entities\Laggregation')->edit($level);
			$Freq  = App('App\Entities\Frequency')->edit($frequency);

			$code = $Table['code'].$Level['code'].$Freq['code'];

			$this->assign($table,$level,$frequency,$variable,$to,$from,$code);
		}

	}

	/**
	 * 
	 */
	private function  assign($table,$level,$frequency,$variable,$to,$from,$code)
	{		
		$query = $this->db->prepare('INSERT INTO  `frequency_table_variable`(table_id,level_id,
																			frequency_id,variable_id,
																			date_to,date_from,
																			generic_table_code)
									 								  VALUES(:table,:level,
									 								  		 :frequency,:variable,
									 								  		 :date_to,:date_from,
									 								  		 :code)');
		
		$query->bindParam(':table', 	$table,PDO::PARAM_INT);
		$query->bindParam(':level', 	$level,PDO::PARAM_INT);
		$query->bindParam(':frequency', $frequency,PDO::PARAM_INT);
		$query->bindParam(':variable', 	$variable,PDO::PARAM_INT);
		$query->bindParam(':date_to', 			$to,PDO::PARAM_STR);
		$query->bindParam(':date_from', 		$from,PDO::PARAM_STR);
		$query->bindParam(':code', 				$code,PDO::PARAM_STR);
		         
		$query->execute();

		getError($query);

	}

	/**
	 * Check if a generic table exist
	 * @return boolean
	 */
	public function genericMatch($table,$level,$frequency)
	{
		$stmt = $this->db->prepare("SELECT * FROM `frequency_table_variable` WHERE table_id =  :table AND level_id = :level AND frequency_id = :frequency");

		$stmt->bindParam(':table',$table,PDO::PARAM_INT);
		$stmt->bindParam(':level',$level,PDO::PARAM_INT);
		$stmt->bindParam(':frequency',$frequency,PDO::PARAM_INT);

		$stmt->execute();

		$data = $stmt->fetch();

		if($data) return TRUE;

		return FALSE;
	} 

	/**
	 * Drop all relationships
	 */
	public function detach($table,$level,$frequency)
	{
		$stmt = $this->db->prepare("DELETE FROM `frequency_table_variable` WHERE table_id =  :table 
									AND level_id = :level 
									AND frequency_id = :frequency");

		$stmt->bindParam(':table',$table,PDO::PARAM_INT);
		$stmt->bindParam(':level',$level,PDO::PARAM_INT);
		$stmt->bindParam(':frequency',$frequency,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * Detach rows from pivot tables
	 * & Add new rows into storage
	 */
	public function sync($data)
	{
		// dd($data);
		//Make a copy of the POST data
		$copy = $data;

		extract($data);

		//Remove all assignments
		$this->detach($table,$level,$frequency);

		//Add new ones
		$this->parse($copy);
	}

	/**
	 * 
	 */
	public function GenericVariable($table,$level,$freq)
	{
		$stmt = $this->db->prepare("SELECT * FROM `frequency_table_variable` WHERE table_id =  :table AND level_id = :level AND frequency_id = :frequency");

		$stmt->bindParam(':table',$table,PDO::PARAM_INT);
		$stmt->bindParam(':level',$level,PDO::PARAM_INT);
		$stmt->bindParam(':frequency',$freq,PDO::PARAM_INT);

		$stmt->execute();

		while($result = $stmt->fetch())
		{
			// $data[]              = $result;
			$data['variables'][] = $result['variable_id'];
			$data['to']          = $result['date_to'];
			$data['from']        = $result['date_from'];
		}

		return $data;
	}

}