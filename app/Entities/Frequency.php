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
		$stmt = $this->db->prepare("SELECT * FROM `frequency_datas`
			 					  INNER JOIN frequency_table ON frequency_datas.id = frequency_table.frequency_id
								  WHERE frequency_table.table_id = :table_id ");

		$stmt->bindParam(':table_id',$table_id,PDO::PARAM_INT);

		getError($stmt);

		$stmt->execute();

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		while($result = $stmt->fetch())
		{
			$results[] = $result;
		}
		
		return $results;
	}

	/**
	 * Fetch all  
	 * 
	**/
	public function lists($paginate = false)
	{
		$query = "SELECT * FROM `frequency_datas` ";

        $childquery = "SELECT * FROM `frequency_table` INNER JOIN `tables`
						ON frequency_table.table_id = tables.id
						WHERE frequency_id = '{frequency_id}' ";

		//Return Paginated data else
		if($paginate)
		{
            $pagination = new Paginator(['query' => $childquery,'placeholder' => '{frequency_id}','column' => 'id'],$query,2);

			return ( $pagination );
		}else{

				$stmt = $this->db->query($query);

				$stmt->setFetchMode(\PDO::FETCH_ASSOC);

				//If PDO error
				getError($stmt);

                $key = 0;

				while($result = $stmt->fetch())
				{
					$results[$key] = $result;

                    $query = "SELECT * FROM `frequency_table` INNER JOIN `tables`
						ON frequency_table.table_id = tables.id
						WHERE frequency_id = '{$result['id']}' ";

                    $tables = $this->db->query($query);

                    while($data = $tables->fetch())
                    {
                        $results[$key]['tables'][] = $data['table_name'];
                    }

                    $key++;
				}
				
				return (is_null($results) OR empty($results)) ? array() : $results;
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
					$results[] = $result;
				}
				
				return (is_null($results) OR empty($results)) ? array() : $results;

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
		$frequency = "";
		$code = "";
		$tables = "";
        $type = "";

		extract($array);

		$query = $this->db->prepare('INSERT INTO  `frequency_datas`(frequency_data_name,code,date_type) VALUES(:frequency,:code,:type)');
		
	    $query->bindParam(':frequency', 	$frequency,PDO::PARAM_STR);
	    $query->bindParam(':code', 	$code,PDO::PARAM_STR);
	    $query->bindParam(':type', 	$type,PDO::PARAM_STR);

	    $query->execute();

	    getError($query);

		$id = $this->db->lastInsertId();

		$data = ['frequency' => $id,'tables' => $tables];

		$this->assign($data);
	}

    /**
     * Add new lists of freq to table in storage
     */
    public function assign($array)
    {
        $frequency = "";
        $tables = "";

        extract($array);

        foreach ($tables as $table) {
            $query = $this->db->prepare('INSERT INTO frequency_table (frequency_id,table_id) VALUES (:frequency,:table)');

            $query->bindParam(':frequency', $frequency,PDO::PARAM_INT);
            $query->bindParam(':table', 	$table,PDO::PARAM_INT);

            getError($query);

            $query->execute();

        }

    }

	/**
	* Edit frequency
	*/
	public function edit($id)
	{
        $stmt = $this->db->prepare("SELECT * FROM `frequency_datas` WHERE id =  :id");

        $stmt->bindParam(':id',$id,PDO::PARAM_INT);

        $stmt->execute();

        $frequency = $stmt->fetch();

        if(!empty($frequency))
        {

            $query = $this->db->query("SELECT * FROM `frequency_table` INNER JOIN `tables`
										ON frequency_table.table_id = tables.id
										WHERE frequency_table.frequency_id = '{$id}'");

            while($data = $query->fetch())
            {
                $frequency['tables'][] = $data['table_id'];
            }

            return $frequency;

        }else{
            return array();
        }
	}

	/**
	 * Retrive a frequency partaining to a table
	*/
	public function gettableFrequency($table_id)
	{
		$stmt = $this->db->prepare("SELECT * FROM frequency_table WHERE table_id =  :id");

		$stmt->bindParam(':id',$table_id,PDO::PARAM_INT);

		//If PDO error
		getError($stmt);

		$stmt->execute();

		while($result = $stmt->fetch())
		{
			$results[] = $result['frequency_id'];
		}
				
		return (is_null($results) OR empty($results)) ? array() : $results;
	}

	/**
	 * Retrive a frequency partaining to a table
	*/
	public function getTableFrequencies($table_id)
	{
		$stmt = $this->db->prepare("SELECT * FROM frequency_datas  
									INNER JOIN frequency_table
									ON  frequency_datas.id = frequency_table.frequency_id
									WHERE table_id =  :id");

		$stmt->bindParam(':id',$table_id,PDO::PARAM_INT);

		//If PDO error
		getError($stmt);

		$stmt->execute();

		while($result = $stmt->fetch())
		{
			$results[] = $result;
		}
				
		return (is_null($results) OR empty($results)) ? array() : $results;
	}

	/**
	 * Update frequency details
	*/
	public function update($data)
	{
        $frequency = "";
        $code = "";
        $id = "";
        $type = "";

        // dd($data);
		extract($data);
		
		$stmt = $this->db->prepare("UPDATE `frequency_datas` SET frequency_data_name = :frequency,code = :code,date_type = :type WHERE id = :id");

				$stmt->bindParam(':frequency', $frequency , PDO::PARAM_STR); 
				$stmt->bindParam(':code', $code , PDO::PARAM_STR); 
				$stmt->bindParam(':type', $type , PDO::PARAM_STR);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);

	    getError($stmt);
		
		$stmt->execute();

		$this->sync(['tables' => $tables,'frequency' => $id]);
	}


	/**
	* Sync
	*/
	public function sync($data)
	{
        $frequencies = "";
        $table = "";

		extract($data);

		$stmt = $this->db->prepare("DELETE FROM frequency_table WHERE frequency_id =  :id");

		$stmt->bindParam(':id',$frequency,PDO::PARAM_INT);

		$stmt->execute();

		$array = ['tables' => $tables,'frequency' => $frequency];

		$this->assign($array);
	}

}