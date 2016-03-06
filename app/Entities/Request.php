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
	
	/**
	 * Insert a new request into the database
	 */
	public function insert($array)
	{
		// dd($array);

		extract($array);

		$variable     = array_filter($variable);
		$caggregation = array_filter($caggregation);

		$code              = array();
		$code['database']  = explode('::',$databasename)[1];
		$code['table']     = explode('::',$table)[1];
		$code['level']     = explode('::',$laggregation)[1];
		$code['frequency'] = explode('::',$frequency)[1];
		$code['variables'] = array_map(function($data)
										{
											return explode('::',$data)[1];
										}, $variable);
		$code['categories']  = array_map(function($data)
										{
											return explode('::',$data)[1];
										}, $caggregation);

		$code = json_encode($code);

		//If additional manual periods was entered add to $periods array
		if(isset($from) && !empty($from) && isset($to) && !empty($to))
		{
			$addperiod = $from.' - '.$to;

			$period[] = $addperiod;
		}

		$variables    = json_encode($variable,JSON_FORCE_OBJECT);
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

		$variable     = array_filter($variable);
		$caggregation = array_filter($caggregation);

		$code              = array();
		$code['database']  = explode('::',$databasename)[1];
		$code['table']     = explode('::',$table)[1];
		$code['level']     = explode('::',$laggregation)[1];
		$code['frequency'] = explode('::',$frequency)[1];
		$code['variables'] = array_map(function($data)
										{
											return explode('::',$data)[1];
										}, $variable);
		$code['categories']  = array_map(function($data)
										{
											return explode('::',$data)[1];
										}, $caggregation);

		$code = json_encode($code);

		$period = [];

		//If additional manual periods was entered add to $periods array
		if(isset($from) && !empty($from) && isset($to) && !empty($to))
		{
			$addperiod = $from.' - '.$to;

			$period[] = $addperiod;
		}


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
		$stmt = $this->db->prepare("SELECT * FROM `requests`
									WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Get a particular request details from storage
	*/
	public function get($id)
	{
		$stmt = $this->db->prepare("SELECT requests.id as id,requests.datatable,requests.databasename,requests.level_aggregation,requests.category_aggregation,
				 		 requests.frequency,requests.variable,requests.period,requests.comment,requests.client_id,requests.transaction_id,
				 		 requests.code,requests.created_at,clients.name,clients.sex,clients.email,clients.phone,clients.address,clients.position_instituition,
				 		 clients.identification_no,affiliates.affiliate_name,affiliates.affiliate_code,affiliates.id as affiliate_id,affiliates.affiliate_email,affiliates.affiliate_type,
				 		 transactions.id as transactionid,transactions.transaction_id,transactions.billed,transactions.data,transactions.balance_after,transactions.approved,
				 		 transactions.delivery,transactions.manager_comment,transactions.datacost
									FROM `requests` INNER JOIN clients 
									ON requests.client_id = clients.id
									INNER JOIN transactions ON requests.transaction_id = transactions.id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE display = TRUE AND requests.id = :id GROUP BY transactions.transaction_id  ORDER BY transactions.created_at DESC");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Get a particular request details from storage
	*/
	public function getByTransaction($id)
	{
		$stmt = $this->db->prepare("SELECT requests.id as id,requests.datatable,requests.databasename,requests.level_aggregation,requests.category_aggregation,
				 		 requests.frequency,requests.variable,requests.period,requests.comment,requests.client_id,requests.transaction_id,
				 		 requests.code,requests.created_at,clients.name,clients.sex,clients.email,clients.phone,clients.address,clients.position_instituition,
				 		 clients.identification_no,affiliates.affiliate_name,affiliates.affiliate_code,affiliates.id as affiliate_id,affiliates.affiliate_email,affiliates.affiliate_type,
				 		 transactions.id as transactionid,transactions.transaction_id,transactions.billed,transactions.data,transactions.balance_after,transactions.approved,
				 		 transactions.delivery,transactions.manager_comment,transactions.datacost
									FROM `requests` INNER JOIN clients 
									ON requests.client_id = clients.id
									INNER JOIN transactions ON requests.transaction_id = transactions.id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE display = TRUE AND transactions.id = :id ");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetchAll();
	}

	/**
	 * Get a particular request details from storage
	*/
	public function getByTransactionId($id)
	{
		$stmt = $this->db->prepare("SELECT requests.id as id,requests.datatable,requests.databasename,requests.level_aggregation,requests.category_aggregation,
				 		 requests.frequency,requests.variable,requests.period,requests.comment,requests.client_id,requests.transaction_id,
				 		 requests.code,requests.created_at,clients.name,clients.sex,clients.email,clients.phone,clients.address,clients.position_instituition,
				 		 clients.identification_no,affiliates.affiliate_name,affiliates.affiliate_code,affiliates.id as affiliate_id,affiliates.affiliate_email,affiliates.affiliate_type,
				 		 transactions.id as transactionid,transactions.transaction_id,transactions.billed,transactions.data,transactions.balance_after,transactions.approved,
				 		 transactions.delivery,transactions.manager_comment,transactions.datacost
									FROM `requests` INNER JOIN clients 
									ON requests.client_id = clients.id
									INNER JOIN transactions ON requests.transaction_id = transactions.id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE display = TRUE AND transactions.transaction_id = :id ");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetchAll();
	}

	/**
	 * Fetch all tables for
	 * a database
	**/
	public function lists($paginate = false)
	{
		$query = "SELECT requests.id as id,requests.datatable,requests.databasename,requests.level_aggregation,requests.category_aggregation,
				 		 requests.frequency,requests.variable,requests.period,requests.comment,requests.client_id,requests.transaction_id,
				 		 requests.code,requests.created_at,clients.name,clients.sex,clients.email,clients.phone,clients.address,clients.position_instituition,
				 		 clients.identification_no,affiliates.affiliate_name,affiliates.affiliate_code,affiliates.id as affiliate_id,affiliates.affiliate_email,affiliates.affiliate_type,
				 		 transactions.id as transactionid,transactions.transaction_id,transactions.billed,transactions.data,transactions.balance_after,transactions.approved,
				 		 transactions.delivery,transactions.manager_comment,transactions.datacost
									FROM `requests` INNER JOIN clients 
									ON requests.client_id = clients.id
									INNER JOIN transactions ON requests.transaction_id = transactions.id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE display = TRUE GROUP BY transactions.transaction_id  ORDER BY transactions.created_at DESC";

		//Return Paginated data else
		if($paginate)
		{
			$pagination = new Paginator(null,$query,30);
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
	 * Fetch all requests ina sorted manner
	*/
	public function sort($affiliate,$type,$status,$approval)
	{
		$query = "SELECT requests.id as id,requests.datatable,requests.databasename,requests.level_aggregation,requests.category_aggregation,
				 		 requests.frequency,requests.variable,requests.period,requests.comment,requests.client_id,requests.transaction_id,
				 		 requests.code,requests.created_at,clients.name,clients.sex,clients.email,clients.phone,clients.address,clients.position_instituition,
				 		 clients.identification_no,affiliates.affiliate_name,affiliates.affiliate_code,affiliates.id as affiliate_id,affiliates.affiliate_email,affiliates.affiliate_type,
				 		 transactions.id as transactionid,transactions.transaction_id,transactions.billed,transactions.data,transactions.balance_after,transactions.approved,
				 		 transactions.delivery,transactions.manager_comment,transactions.datacost
									FROM `requests` INNER JOIN clients 
									ON requests.client_id = clients.id
									INNER JOIN transactions ON requests.transaction_id = transactions.id
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

			$query .= "  transactions.delivery = {$status} ";
		}

		if(!empty($approval)) 
		{
			$query .= " AND ";

			$query .= "  transactions.approved = '{$approval}' ";
		}

		$query .= "GROUP BY transactions.transaction_id ORDER BY transactions.created_at DESC";


		$pagination = new Paginator(null,$query,30);
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
		$query = "SELECT requests.id as id,requests.datatable,requests.databasename,requests.level_aggregation,requests.category_aggregation,
				 		 requests.frequency,requests.variable,requests.period,requests.comment,requests.client_id,requests.transaction_id,
				 		 requests.code,requests.created_at,clients.name,clients.sex,clients.email,clients.phone,clients.address,clients.position_instituition,
				 		 clients.identification_no,affiliates.affiliate_name,affiliates.affiliate_code,affiliates.id as affiliate_id,affiliates.affiliate_email,affiliates.affiliate_type,
				 		 transactions.id as transactionid,transactions.transaction_id,transactions.billed,transactions.data,transactions.balance_after,transactions.approved,
				 		 transactions.delivery,transactions.manager_comment,transactions.datacost
									FROM `requests` INNER JOIN clients 
									ON requests.client_id = clients.id
									INNER JOIN transactions ON requests.transaction_id = transactions.id
									LEFT JOIN affiliates ON clients.affiliate_id = affiliates.id
									WHERE display = TRUE AND clients.affiliate_id = {$id} GROUP BY transactions.transaction_id  ORDER BY transactions.created_at DESC";

		$pagination = new Paginator(null,$query,40);

		return $pagination;
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
	public function updateStatus($id,$approval)
	{
		// dd(func_get_args());
		$stmt = $this->db->prepare("UPDATE `transactions` SET approved = :approval WHERE id = :id"); 

		$stmt->bindParam(':approval', $approval, PDO::PARAM_STR);       
		$stmt->bindParam(':id', $id, PDO::PARAM_INT); 

	    $stmt->errorInfo();

		$stmt->execute();
	}

	/**
	 * Approve request status
	 */
	public function updateTransaction($data)
	{
		// dd($data);
		extract($data);

		$approval = empty($approval) ? 'awaiting' : $approval;

		$stmt = $this->db->prepare("UPDATE `transactions` SET approved = :approved,manager_comment = :comment WHERE id = :id"); 

				$stmt->bindParam(':approved', $approval, PDO::PARAM_INT);       
				$stmt->bindParam(':comment', $comment, PDO::PARAM_INT); 
				$stmt->bindParam(':id', $transaction, PDO::PARAM_INT);

	    $stmt->errorInfo();

		$stmt->execute();
	}

	/**
	 * Process transaction from admin end
	 * Change delivery status
	 * Add data size
	 * Deduct datasize from affiliate data account
	 */
	public function processTransaction($data)
	{
		extract($data);

			//Bill this transaction if
			//it has not yet being billed
			if($billed == 0)
			{	
				//If client is an affiliated instituition
				//Deduct data
				if(!empty($affiliateid))
				{
					$this->deductData($affiliateid,$datasize,$transactionid);

					//Set billings to true
					$billed = 1;

					$balance_after = $this->balanceAfter;
				}
			}

					//Update affiliate billing
					if(!empty($affiliateid))
					{	
						//Set new inputed datasize || already inputed datasize as charge 
						$datasize = empty($data) ? $datasize : $data;

						$stmt = $this->db->prepare("UPDATE `transactions` SET billed = 1,delivery = :delivery,data = :data,balance_after = :balance_after WHERE id = :id"); 

						$stmt->bindParam(':delivery', $delivery, PDO::PARAM_INT);       
						$stmt->bindParam(':data',     $datasize, PDO::PARAM_INT); 
						$stmt->bindParam(':balance_after',     $balance_after, PDO::PARAM_INT); 
						$stmt->bindParam(':id',       $transactionid, PDO::PARAM_INT);

					    $stmt->errorInfo();

						$stmt->execute();

						//Bill affiliate account
						$this->updateBalance($affiliateid);

						// dd(['delivery' => $delivery,'data' => $datasize,'balance_after' => $balance_after,'id' => $transactionid]);

					}else{

						$stmt = $this->db->prepare("UPDATE `transactions` SET billed = 1,delivery = :delivery,datacost = :datacost WHERE id = :id"); 

						$stmt->bindParam(':delivery', 			$delivery, PDO::PARAM_INT);       
						$stmt->bindParam(':datacost',     		$datasize, PDO::PARAM_STR); 
						$stmt->bindParam(':id',       		   $transactionid, PDO::PARAM_INT);

					    $stmt->errorInfo();

						$stmt->execute();

						// dd(['delivery' => $delivery,'data' => $datasize,'balance_after' => $balance_after,'id' => $transactionid]);

					}
			
		
	}

	/**
	 * Deduct a certain kb data from an affiliate account
	 */
	private function deductData($affiliateid,$charge,$transactionid)
	{

		$datasize = $this->getBalance($affiliateid);

		$this->balanceAfter = intval($datasize) - intval($charge);

		//Check if databalane is zero
		//Cut Off Affiliate
		if($this->balanceAfter == 0)
		{
			$status = 0;

			$stmt = $this->db->prepare("UPDATE `affiliate_plan` SET status = :status WHERE affiliate_id = :id AND status = 1"); 

						$stmt->bindParam(':status', 		   $status, PDO::PARAM_INT);       
						$stmt->bindParam(':id',       		   $transactionid, PDO::PARAM_INT);

					    $stmt->errorInfo();

						$stmt->execute();
		}
	}

	/**
	 * Get data account balance for an affiliate
	 */
	public function getBalance($affiliateid)
	{
		$stmt = $this->db->prepare("SELECT datasize FROM affiliate_plan WHERE status = 1 AND affiliate_id = :id ");

		$stmt->bindParam(':id',$affiliateid,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch()['datasize'];
	}

	/**
	 * Deduct charge from affiliate datasize
	 */
	private function updateBalance($affiliateid)
	{
		$stmt = $this->db->prepare("UPDATE `affiliate_plan` SET datasize = :size WHERE affiliate_id = :id AND status = 1"); 

		$stmt->bindParam(':size', 		   $this->balanceAfter, PDO::PARAM_INT);       
		$stmt->bindParam(':id',       	   $affiliateid, PDO::PARAM_INT);

		$stmt->errorInfo();

		$stmt->execute();
	}


	/**
	 * Delete from storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM `transactions` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}


	/**
	 * Get number of approved affiliate reuqests
	 * @return mixed
     */
	public  function getApprovedAffiliatedRQ()
	{
		$stmt = $this->db->prepare("SELECT COUNT(*) as count FROM transactions WHERE approved = 'approved' ");

		$stmt->execute();

		return !empty($stmt->fetch()) ? $stmt->fetch()['count'] : 0;
	}

	/**
	 * Get number of approved affiliate reuqests
	 * @return mixed
	 */
	public  function getPaidIndependentRQ()
	{
		$stmt = $this->db->prepare("SELECT COUNT(*) as count FROM transactions WHERE approved = 'paid' ");

		$stmt->execute();

        return !empty($stmt->fetch()) ? $stmt->fetch()['count'] : 0;
    }

}