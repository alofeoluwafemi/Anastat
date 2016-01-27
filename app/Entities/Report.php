<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Report {

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
	public function AllReport()
	{
		$stmt = $this->db->query("SELECT affiliates.affiliate_name,MONTHNAME(requests.created_at) AS month,YEAR(requests.created_at) as year,
									 clients.name,COUNT(*) as requests
							 		 FROM `affiliates`
									 INNER JOIN clients ON clients.affiliate_id = affiliates.id
                                     INNER JOIN requests ON  clients.id = requests.client_id
									 GROUP BY YEAR(requests.created_at),MONTH(requests.created_at) 
									 ORDER BY affiliates.affiliate_name ,requests.created_at ASC
									 ");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}


	public function ByAffiliate($id)
	{
		$stmt = $this->db->query("SELECT affiliates.affiliate_name,MONTHNAME(requests.created_at) AS month,YEAR(requests.created_at) as year,
									 clients.name,COUNT(*) as requests
							 		 FROM `clients`
									 INNER JOIN affiliates ON clients.affiliate_id = affiliates.id
                                     INNER JOIN requests ON  clients.id = requests.client_id
                                     WHERE clients.affiliate_id = '{$id}'
									 GROUP BY YEAR(requests.created_at),MONTH(requests.created_at) 
									 ORDER BY affiliates.affiliate_name ,requests.created_at ASC
									 ");

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