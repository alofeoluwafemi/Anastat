<?php

namespace App\Entities;

use App\Singleton;
use App\DB;
use PDO;

class Subscription {

    use Singleton;


    private $db;

    private $results;

    public function __construct()
    {
        $this->db = App('App\DB')->conn();
    }


    /**
     * Fetch all plans from storage
     */
    public function lists()
    {
        $stmt = $this->db->query("SELECT * FROM `plans` ORDER BY id");

        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

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
     */
    public function listByAffiliate($id)
    {
        $stmt = $this->db->query("SELECT * FROM `affiliate_plan` INNER JOIN plans
                                  ON affiliate_plan.plan_id = plans.id WHERE affiliate_id = '{$id}' AND status = 0");

        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

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
     */
    public function listActiveByAffiliate($id)
    {
        $stmt = $this->db->query("SELECT * FROM `affiliate_plan` INNER JOIN plans
                                  ON affiliate_plan.plan_id = plans.id
                                  WHERE affiliate_id = '{$id}' AND status = 1
                                  LIMIT 1");

        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        getError($stmt);

        while($result = $stmt->fetch())
        {
            $results[] = $result;
        }

        return (is_null($results) OR empty($results)) ? array() : $results;
    }

    /**
     * @param $array
     */
    public function addnew($array)
    {
        $name = "";
        $validity = "";
        $size = "";

        extract($array);

        $query = $this->db->prepare('INSERT INTO  `plans`(name,validity_days,size) VALUES(:name,:validity,:size)');

        $query->bindParam(':name', 	$name,PDO::PARAM_STR);
        $query->bindParam(':validity', 	$validity,PDO::PARAM_INT);
        $query->bindParam(':size', 	$size,PDO::PARAM_INT);

        $query->execute();

        getError($query);
    }


    /**
     * @param $affiliateid
     */
    public function subscribe($affiliateid,$plan)
    {
        $affiliate_id = "";
        $id = "";
        $status = "";
        $expires = "";
        $size = "";
        $datasize = "";
        $validity_days = "";

        $data = App('App\Entities\Subscription')->edit($plan);

        extract($data);

        $active = $this->checkForActivePlan($affiliateid);

        /**
         * If active plan exist
         * add new data and increase exipry date
         * If there is no active plan running
         * just add plan unto client subscriptions list
         */
        if($active)
        {
            extract($active);

            $newSize = intval($size) + intval($datasize);

            $newExpires = date('Y-m-d h:i:s', strtotime("{$expires}") + 60 * 60 * $validity_days );

            $status = TRUE;

            $query = $this->db->prepare('UPDATE `affiliate_plan` SET affiliate_id = :affiliate_id,plan_id = :plan_id,
                                                                    status = :status,expires = :expires,
                                                                    datasize = :size WHERE id = :id');

            $query->bindParam(':affiliate_id', 	$affiliateid,PDO::PARAM_INT);
            $query->bindParam(':plan_id', 	$id,PDO::PARAM_INT);
            $query->bindParam(':status', 	$status,PDO::PARAM_INT);
            $query->bindParam(':expires', 	$newExpires,PDO::PARAM_STR);
            $query->bindParam(':size', 	    $newSize,PDO::PARAM_INT);
            $query->bindParam(':id', 	    $id,PDO::PARAM_INT);

            $query->execute();

            getError($query);

        }else{

            $expires = date('Y-m-d h:i:s', strtotime("+{$validity_days} days") );

            $status = TRUE;

            $query = $this->db->prepare('INSERT INTO  `affiliate_plan`(affiliate_id,plan_id,status,expires,datasize)
                                         VALUES(:affiliate_id,:plan_id,:status,:expires,:size)');

            $query->bindParam(':affiliate_id', 	$affiliateid,PDO::PARAM_INT);
            $query->bindParam(':plan_id', 	$id,PDO::PARAM_INT);
            $query->bindParam(':status', 	$status,PDO::PARAM_INT);
            $query->bindParam(':expires', 	$expires,PDO::PARAM_STR);
            $query->bindParam(':size', 	    $size,PDO::PARAM_INT);

            $query->execute();

            getError($query);
        }

    }

    /**
     * @param $affiliateid
     * @return bool
     */
    private function checkForActivePlan($affiliateid)
    {
        $bool = false;

        $stmt = $this->db->prepare("SELECT * FROM `affiliate_plan` WHERE id =  :id AND status = 1 ");

        $stmt->bindParam(':id',$affiliateid,PDO::PARAM_INT);

        $stmt->execute();

        if($result = $stmt->fetch())
        {
            $bool = $result;
        }

        return $bool;
    }


    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM `plans` WHERE id =  :id");

        $stmt->bindParam(':id',$id,PDO::PARAM_INT);

        $stmt->execute();

        return $data = $stmt->fetch();
    }


    /**
     * @param $data
     */
    public function update($data)
    {
        $name = "";
        $validity = "";
        $size = "";
        $id = "";

        extract($data);

        $stmt = $this->db->prepare("UPDATE `plans` SET name = :name,validity_days = :validity,size = :size WHERE id = :id");

        $stmt->bindParam(':name', $name , PDO::PARAM_STR);
        $stmt->bindParam(':validity', $validity , PDO::PARAM_INT);
        $stmt->bindParam(':size', $size, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        getError($stmt);

        $stmt->execute();
    }

    /**
     * @param $id
     */
    public function drop($id)
    {
        $stmt = $this->db->prepare("DELETE FROM plans WHERE id =  :id");

        $stmt->bindParam(':id',$id,PDO::PARAM_INT);

        $stmt->execute();
    }
}