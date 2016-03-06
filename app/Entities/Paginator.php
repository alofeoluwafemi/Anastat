<?php

namespace App\Entities;

/**
 * Paginator class
 */
class Paginator
{
    private $db;
    private $limit;
    private $page;
    private $query;
    private $total;
    private $baseUrl = "";
    private $links = 7;
    private $listClass = "pagination";
    private $appends = array();

    public function __construct($childquery = null,$query, $limit = 10, $links = 7)
    {
		$this->db      = App('App\DB')->conn();
		$this->query   = $query;
		$this->page    = isset($_GET['page']) ? $_GET['page'] : 1;
		$this->validatePageNumber();
		$this->limit   = $limit;
		$this->links   = $links;
		$this->baseUrl = config('base_url').geturi();
		$this->childquery = $childquery;
    }

    function validatePageNumber()
    {
        $this->page = (int) str_replace("-", '', $this->page);
    }

    public function setListClass($class = "")
    {
        $this->listClass = $class;
        return $this;
    }

    public function append($param = array())
    {
        $this->appends = $param;
        return $this;
    }

    public function results()
    {
        if ($this->limit == "all") {
            $query = $this->query;
        } else {

        	$stmt = $this->db->query($this->query);
        	$this->total = $stmt->rowCount();

            $query = $this->query . " LIMIT ". (($this->page - 1) * $this->limit). ", {$this->limit}";
            // dd($query);
        }

        $stmt = $this->db->query($query);

        $key = 0;

        /**
         * Portion to be rewritten to be dynamic
         */
		while($result = $stmt->fetch())
		{
			$results[] = $result;

            if(!empty($this->childquery))
            {
                // dd($this->childquery);
                $placeholder = $this->childquery['placeholder'];
                $column = $this->childquery['column'];

                $this->childquery['query'] = str_replace($placeholder,$result[$column], $this->childquery['query']);

                // dd($this->childquery['query']);

                $child = $this->db->query($this->childquery['query']);

                while($data = $child->fetch())
                {
                    $fetch = $this->childquery['fetch'];
                    $results[$key]['tables'][] = !empty($fetch) ? $data[$fetch] : $data['table_name'];
                }

                $key++;

                $this->childquery['query'] = str_replace($result[$column],$placeholder, $this->childquery['query']);
            }


		}
		return (is_null($results) OR empty($results)) ? array() : $results;
    }

    public function links($append = "")
    {
        if ($this->limit == 'all') return '';
        $last = ceil($this->total / $this->limit);
        $start = (($this->page - $this->links) > 0) ? $this->page - $this->links : 1;
        $end = (($this->page + $this->links) < $last) ? $this->page + $this->links : $last;

        $html = "<ul style='padding-bottom: 20px;display: block'  class='{$this->listClass}'>";
        $class = ($this->page == 1) ? "disabled" : "";
        $html .= "<li class='{$class}'> <a href='".$this->getLink($append,(($this->page - 1) == 0) ? 1 : $this->page - 1  )."'>&laquo;</a>";


        if ($start > 1) {
            $html .= "<li><a href='".$this->getLink($append,1)."'>1</a></li>";
            $html .= "<li class='disabled'><span>...</span></li>";
        }

        for( $i = $start; $i <= $end; $i++) {
            $class = ($this->page == $i) ? "active" : "";
            $html .= "<li class='".$class."'><a href='".$this->getLink($append,$i)."'>".$i."</a></li>";
        }

        if ($end < $last) {
            $html .= "<li class='disabled'><span>...</span></li>";
            $html .= "<li><a href='".$this->getLink($append,$last)."'>{$last}</a>";
        }

        $class = ($this->page == $last) ? "disabled" : "";
        $html .= "<li class='{$class}'> <a href='".$this->getLink($append,($last == $this->page) ? $last : $this->page + 1)."'>&raquo;</a>";
        $html .="</ul>";

        return $html;
    }

    public function getLink($append = null,$page)
    {
    	if(!empty($append)) $append = '&'.$append;

        $link = "http://".$this->baseUrl."?page=".$page."&count=".$this->limit.$append;
        foreach($this->appends as $key => $value) {
            $link .= "&".$key."=".$value;
        }
        return $link;
    }
}