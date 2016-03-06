<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Affiliate {

	use Singleton;
	

	private $db;

	private $results;

	public function __construct()
	{
		$this->db = App('App\DB')->conn();
	}


    /**
     * Add new affiliate to storage
     * @param $array
     */
    public function addnew($array)
	{
		$affiliate = "";
		$code = "";
		$email = "";
        $type = "";
        $subscription = "";

		extract($array);

		$query = $this->db->prepare('INSERT INTO affiliates (affiliate_name,affiliate_code,affiliate_email,affiliate_type)
									VALUES (:name,:code,:email,:type)');
			 
        $query->bindParam(':name', 	$affiliate,PDO::PARAM_STR);
        $query->bindParam(':code', 	$code,PDO::PARAM_STR);
        $query->bindParam(':email', $email,PDO::PARAM_STR);
        $query->bindParam(':type', $type, PDO::PARAM_STR);

        $query->execute();

        $id = $this->db->lastInsertId();

        /**
         * Subscribe plan for affiliate
         */
        if(!empty($subscription))
        {
            App('App\Entities\Subscription')->subscribe($id,$subscription);
        }

	    getError($query);
	}


	/**
	* Edit
	*/
	public function edit($id)
	{
		$stmt = $this->db->prepare("SELECT * FROM `affiliates` WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	* Edit
	*/
	public function get($id)
	{
		$stmt = $this->db->prepare("SELECT affiliates.id,affiliates.affiliate_name,affiliates.affiliate_code,affiliates.affiliate_email,
									affiliates.affiliate_type,affiliate_plan.status,affiliate_plan.datasize,affiliate_plan.plan_id,affiliate_plan.affiliate_id
		 							FROM `affiliates` INNER JOIN affiliate_plan ON  affiliates.id = affiliate_plan.affiliate_id WHERE affiliates.id =  :id
		 							ORDER BY affiliate_plan.id DESC ");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();

		return $data = $stmt->fetch();
	}

	/**
	 * Fetch all tables for
	 * a database
	**/
	public function lists()
	{
		$this->cutOffExpiredAffiliate();

		$stmt = $this->db->query("SELECT * FROM `affiliates` LEFT JOIN affiliate_plan
								  ON affiliates.id = affiliate_plan.affiliate_id 
								  ORDER BY affiliate_name");

		$stmt->setFetchMode(\PDO::FETCH_ASSOC);

		//If PDO error
		getError($stmt);

		while($result = $stmt->fetch())
		{
			$this->results[] = $result;
		}
		
		// var_dump($this->results);
		return (is_null($this->results) OR empty($this->results)) ? array() : $this->results;
	}

	private function cutOffExpiredAffiliate()
	{
		$this->db->query("UPDATE `affiliate_plan` SET status = 0 WHERE NOW() > expires OR datasize = 0");
	}

	/**
	 * Update
	 */
	public function update($data)
	{
		$affiliate = "";
        $code = "";
        $email = "";
        $type = "";
        $id = "";

		extract($data);

		$stmt = $this->db->prepare("UPDATE `affiliates` SET
                                    affiliate_name = :affiliate,affiliate_code = :code,
                                    affiliate_email = :email ,affiliate_type = :type
                                    WHERE id = :id");

				$stmt->bindParam(':affiliate', $affiliate, PDO::PARAM_STR);
				$stmt->bindParam(':code', $code, PDO::PARAM_STR); 
				$stmt->bindParam(':email', $email, PDO::PARAM_STR); 
				$stmt->bindParam(':type', $type, PDO::PARAM_STR);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);

	    $stmt->errorInfo();

		$stmt->execute();

        /**
         * Subscribe plan for affiliate
         */
        if(!empty($subscription))
        {
            App('App\Entities\Subscription')->subscribe($id,$subscription);
        }
	}

	/**
	 * Delete from Affliate storage
	*/
	public function drop($id)
	{
		$stmt = $this->db->prepare("DELETE FROM affiliates WHERE id =  :id");

		$stmt->bindParam(':id',$id,PDO::PARAM_INT);

		$stmt->execute();
	}

}