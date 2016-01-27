<?php

namespace App\Entities;

use App\Singleton;
use App\Entities\Paginator;
use PDO;

class Request{
	
	use Singleton;

	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}

	public function count()
	{
		$query = $this->db->query("SELECT COUNT(*) AS count FROM `requests` WHERE display = TRUE");
		
		$this->results = $query->fetch();
		// var_dump($this->results);
		return (is_null($this->results) OR empty($this->results)) ? 0 : $this->results['count'];
	}
	
	/**
	 * Insert a new request into the database
	 */
	public function insert($array)
	{
		// dd($array);

		extract($array);

		$comma = ',';
		$space = ' ';
		$code = $databasename.$space;
		$code .= $table.$space;
		$code .= $laggregation.$space;
		$code .= implode($comma,$caggregation) . $space;
		$code .= $frequency.$space;
		$code .= implode($comma,$variable) . $space;

		$period = [];
		$variable    = array_filter($variable);


		$variables    = json_encode($variable,JSON_FORCE_OBJECT);

		//If additional manual periods was entered add to $periods array
		if(isset($from) && !empty($from) && isset($to) && !empty($to))
		{
			$addperiod = $from.' - '.$to;

			$period[] = $addperiod;
		}

 		// if($index = array_search('manualperiod',$period))
 		// {
 		// 	unset($period[$index]);
 		// }

		$periods      = json_encode($period,JSON_FORCE_OBJECT);
		$caggregation = json_encode(array_filter($caggregation),JSON_FORCE_OBJECT);

		 $query = $this->db->prepare('INSERT INTO requests (databasename,datatable,level_aggregation,category_aggregation,frequency,variable,period,comment,code) 
                      VALUES (:database,:table,:level_aggregation,:category_aggregation,:frequency,:variable,:period,:comment,:code)');
		 
                $query->bindParam(':database', 				 $databasename,PDO::PARAM_STR);
                $query->bindParam(':table', 				 $table,PDO::PARAM_STR);
                $query->bindParam(':level_aggregation', 	 $laggregation,PDO::PARAM_STR);
                $query->bindParam(':category_aggregation',   $caggregation,PDO::PARAM_STR);
                $query->bindParam(':frequency',   			 $frequency,PDO::PARAM_STR);
                $query->bindParam(':variable',   			 $variables,PDO::PARAM_STR);
                $query->bindParam(':period',   				 $periods,PDO::PARAM_STR);
                $query->bindParam(':comment',    			 $comment,PDO::PARAM_STR);
                $query->bindParam(':code',    			 	 $code,PDO::PARAM_STR);
             
        $query->execute();

        getError($query);

        // echo $this->db->lastInsertId();
        return $this->db->lastInsertId();
	}

	/**
	 * update a request detail
	 */
	public function update($array)
	{
		extract($array);

		$comma = ',';
		$space = ' ';
		$code = $databasename.$space;
		$code .= $table.$space;
		$code .= $laggregation.$space;
		$code .= implode($comma,$caggregation) . $space;
		$code .= $frequency.$space;
		$code .= implode($comma,$variable) . $space;

		$period = [];
		$variable    = array_filter($variable);

		//If additional manual periods was entered add to $periods array
		if(isset($from) && !empty($from) && isset($to) && !empty($to))
		{
			$addperiod = $from.' - '.$to;

			$period[] = $addperiod;
		}

 		// if($index = array_search('manualperiod',$period))
 		// {
 		// 	unset($period[$index]);
 		// }

		$periods      = json_encode($period,JSON_FORCE_OBJECT);
		$caggregation = json_encode(array_filter($caggregation),JSON_FORCE_OBJECT);
		$variables    = json_encode($variable,JSON_FORCE_OBJECT);


		 $query = $this->db->prepare('UPDATE requests SET databasename = :database ,datatable = :table ,level_aggregation = :level_aggregation,
		 								category_aggregation = :category_aggregation,frequency = :frequency,
		 								variable = :variable,period = :period,comment = :comment,code = :code WHERE id = :editid'); 
		 
                $query->bindParam(':database', 				 $databasename,PDO::PARAM_STR);
                $query->bindParam(':table', 				 $table,PDO::PARAM_STR);
                $query->bindParam(':level_aggregation', 	 $laggregation,PDO::PARAM_STR);
                $query->bindParam(':category_aggregation',   $caggregation,PDO::PARAM_STR);
                $query->bindParam(':frequency',   			 $frequency,PDO::PARAM_STR);
                $query->bindParam(':variable',   			 $variables,PDO::PARAM_STR);
                $query->bindParam(':period',   				 $periods,PDO::PARAM_STR);
                $query->bindParam(':comment',    			 $comment,PDO::PARAM_STR);
                $query->bindParam(':code',    			 	 $code,PDO::PARAM_STR);
                $query->bindParam(':editid',    			 $editid,PDO::PARAM_INT);
             
        $query->execute();

        getError($query);

        return $editid;
	}

	/**
	 * Get a particular request details from storage
	 */
	public function getrequest($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `requests` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Get a particular request details from storage
	*/
	public function get($id)
	{
		$stmt = $this->db->prepare("SELECT clients.name,clients.id as clientid,clients.sex,clients.email,clients.phone,clients.address,
										  	clients.position_instituition,requests.id as id,requests.databasename,requests.datatable,
											requests.client_id,requests.code,requests.approved,requests.level_aggregation,requests.category_aggregation,requests.frequency,
									 		requests.variable,requests.period,requests.comment,requests.status,affiliates.affiliate_name,affiliates.affiliate_code
									 		 FROM requests
									INNER JOIN clients ON clients.id = requests.client_id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE requests.id =  :id AND display = TRUE");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Fetch all tables for
	 * a database
	**/
	public function lists($paginate = false)
	{
		$query = "SELECT requests.client_id,requests.code,requests.approved,requests.databasename,requests.status,clients.instituition,clients.name,requests.id,affiliates.affiliate_name
									FROM `requests` INNER JOIN clients ON clients.id = requests.client_id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE display = TRUE ORDER BY requests.created_at DESC";

		//Return Paginated data else
		if($paginate)
		{
			$pagination = new Paginator(null,$query,20);
			return $pagination;
		}
		else {
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
	 * Fetch all tables for
	 * a database
	**/
	public function sort($affiliate,$type,$status,$approval)
	{
		// dd(func_get_args());

		$query = "SELECT requests.client_id,requests.code,requests.approved,requests.databasename,requests.status,clients.instituition,clients.name,requests.id,affiliates.affiliate_name
									FROM `requests` INNER JOIN clients ON clients.id = requests.client_id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE display = TRUE ";


		/**
		 * Build Sorting
		 */
		if(!empty($affiliate)) 
			{
				$query .= " AND ";

				$query .= "  clients.affiliate_id = {$affiliate} ";

			}

		if(!empty($type))
		{
			$query .= " AND ";

			$query .= ($type == 'personal') ? "  clients.affiliate_id = 0 " : " clients.affiliate_id != 0 ";
		} 

		if(!empty($status)) 
		{
			$query .= " AND ";

			$query .= "  status = {$status} ";
		}

		if(!empty($approval)) 
		{
			$query .= " AND ";

			$query .= "  approved = '{$approval}' ";
		}

		$query .= " ORDER BY requests.created_at DESC";

		$stmt = $this->db->query($query);

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		$pagination = new Paginator(null,$query,20);
		return $pagination;
	}




	/**
	 * Fetch all requests for
	 * a database
	 * List by client
	**/
	public function listsByClient($id)
	{
		$stmt = $this->db->prepare("SELECT clients.name,clients.id as clientid,clients.name,clients.sex,clients.email,clients.phone,clients.address,
										  	clients.position_instituition,requests.id as id,requests.databasename,requests.datatable,
											requests.client_id,requests.code,requests.approved,requests.level_aggregation,requests.category_aggregation,requests.frequency,
									 		requests.variable,requests.period,requests.comment,requests.status,affiliates.affiliate_name,affiliates.affiliate_code
									 		FROM `clients`
											INNER JOIN requests ON clients.id = requests.client_id 
											LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
											WHERE clients.id = :id
											");
		
		$stmt->bindParam(':id',$id,PDO::PARAM_STR);

		$stmt->execute();

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
	 * Fetch all requests for
	 * a database
	 * List by Affiliate
	**/
	public function listByAffiliate($id)
	{
		$stmt = $this->db->prepare("SELECT clients.name,clients.id as clientid,clients.name,clients.sex,clients.email,clients.phone,clients.address,
										  	clients.position_instituition,requests.id as id,requests.databasename,requests.datatable,
											requests.client_id,requests.code,requests.approved,requests.level_aggregation,requests.category_aggregation,requests.frequency,
									 		requests.variable,requests.period,requests.comment,requests.status,affiliates.affiliate_name,affiliates.affiliate_code
									 		FROM `clients`
											INNER JOIN requests ON clients.id = requests.client_id 
											LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
											WHERE clients.affiliate_id = :id
											");
		
		$stmt->bindParam(':id',$id,PDO::PARAM_STR);

		$stmt->execute();

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$results[] = $result;
		}
		
		return (is_null($results) OR empty($results)) ? array() : $results;
	}

	public function DatabasePair()
	{
		$stmt = $this->db->query("SELECT databasename,shortcode FROM `databases`");
		
		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}

		$library = array();

		foreach ($this->results as $key => $value) {
			$library[ strtolower($value['databasename'])] = $value['shortcode'];
		}

		return $library;
	}

	/**
	 * Fetch all requests for
	 * a database
	 * List by client
	**/
	public function listsByClientEmail($email)
	{
		$stmt = $this->db->prepare("SELECT clients.name,clients.id as clientid,clients.name,clients.sex,clients.email,clients.phone,clients.address,
										  	clients.position_instituition,requests.id as id,requests.databasename,requests.datatable,
											requests.client_id,requests.code,requests.approved,requests.level_aggregation,requests.category_aggregation,requests.frequency,
									 		requests.variable,requests.period,requests.comment,requests.status,affiliates.affiliate_name,affiliates.affiliate_code
									 		FROM `clients`
											INNER JOIN requests ON clients.id = requests.client_id 
											LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
											WHERE clients.email = :email
											");
		
		$stmt->bindParam(':email',$email,PDO::PARAM_STR);

		$stmt->execute();

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
	 * Update request status
	 */
	public function updateStatus($id,$status)
	{
		// dd($status);
		$stmt = $this->db->prepare("UPDATE `requests` SET status = :status WHERE id = :id"); 

				$stmt->bindParam(':status', $status, PDO::PARAM_INT);       
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 

	    $stmt->errorInfo();

		$stmt->execute();
	}

	/**
	 * Approve request status
	 */
	public function approve($id)
	{
		$status = 'YES';
		$stmt = $this->db->prepare("UPDATE `requests` SET approved = :status WHERE id = :id"); 

				$stmt->bindParam(':status', $status, PDO::PARAM_INT);       
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 

	    $stmt->errorInfo();

		$stmt->execute();
	}

	/**
	 * Approve request status
	 */
	public function disapprove($id)
	{
		$status = 'NO';
		$stmt = $this->db->prepare("UPDATE `requests` SET approved = :status WHERE id = :id"); 

				$stmt->bindParam(':status', $status, PDO::PARAM_INT);       
				$stmt->bindParam(':id', $id, PDO::PARAM_INT); 

	    $stmt->errorInfo();

		$stmt->execute();
	}

	/**
	 * Delete from storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM `requests` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

	/**
	 * 
	 */
	public function dropWithoutClient($id)
	{
		$stmt = $this->db->prepare("DELETE FROM `requests` WHERE client_id = 0 AND id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}
}