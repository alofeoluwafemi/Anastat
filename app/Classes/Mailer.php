<?php

namespace App\Classes;

use App\Singleton;

class Mailer
{

	use Singleton;

	private $table;

	public function __construct()
	{
		$this->open();
	}

	/**
	 * Parse request build structure and send to admin
	*/
	public function mailRequest($data,$detail)
	{
		// dd($data);
		$client            = $detail['clientid'];
		$this->transaction = $detail['transactionid'];

		extract($data);

		if(empty($requestid)) die('empty request');

		$this->appendclient($client);

		$requestid = array_filter($requestid);


		foreach($requestid as $key => $request)
		{
			$this->process($key,$request);
		}

		$this->close();

		$this->subject = 'New Request From ANASTAT Platform ('.date('D m Y h:i:s').')';

		$this->notice = "<br> <b style='color:red'>NOTE :</b> You can always check your requests on our platform using your submission code";

		//Send client a copy
		if(!empty($this->ClientEmail)) sendmail($this->ClientEmail,'ANASTAT PLATFORM',$this->subject,$this->table.$this->notice);

		//Send admin a copy
		if(!empty(config('site-email'))) sendmail(config('site-email'),'ANASTAT PLATFORM',$this->subject,$this->table);

		/*
		* If client is affiliate : send manager an email
		* Hide submission code from affiliate
		*/
		$this->table = str_replace($this->transaction, '###########', $this->table);

		if(!empty($this->AffiliateEmail)) sendmail($this->AffiliateEmail,'ANASTAT PLATFORM',$this->subject,$this->table);
	}

	/**
	* 
 	*/
	private function process($key,$request)
	{
		$result = App('App\Entities\Request')->getrequest($request);

		if(!empty($result))
		{
			// dd($result);
			extract($result);

			$code   = json_decode($code,true);
			$period = json_decode($period,true);

			if($key == 1)
			{
				$this->putInRow('&nbsp;','&nbsp;');
				$this->putInRow('REQUESTS','');
				$this->putInRow('&nbsp;','&nbsp;');
			}

			$key = $key++;
			if($key != 1)$this->putInRow('&nbsp;','&nbsp;');
			$this->putInRow('Request '.$key,'&nbsp;');
			$this->putInRow('Database Code',$code['database']);
			$this->putInRow('Variables code',implode(',',$code['variables']));
			$this->putInRow('Table code',$code['table'].$code['level'].$code['frequency']);
			$this->putInRow('Categories',implode(',',$code['categories']));
			$this->putInRow('Periods',implode($period));
			$this->putInRow('Comment',$comment);
		}
		
	}

	private function open()
	{

		$this->table .= 
		'<style>
		table{
			width: 80%;
        	border-collapse: collapse !important;
		}
		table tr td{
			width: 50%;
        	border: 1px solid black;
        	border-collapse: collapse !important;
        	font-family:candara;
        	padding:3px;
		}
        </style>';

		$this->table .= '<table>';
	}

	private function close()
	{
		$this->table .= '</table>';
	}

	private function putInRow($key,$value)
	{
		$this->table .= '<tr>';
		$this->table .= '<td>';
		$this->table .= '<b>';
		$this->table .= $key;
		$this->table .= '</b>';
		$this->table .= '</td>';
		$this->table .= '<td>';
		$this->table .= $value;
		$this->table .= '</td>';
		$this->table .= '</tr>';
	}


	private function appendclient($id)
	{
		$client = App('App\Entities\Client')->get($id);

		extract($client);

		//Save client email if any available
		$this->ClientEmail = $email;


		$this->putInRow('SUBMISSION CODE',$this->transaction);

		$this->putInRow('&nbsp;','&nbsp;');

		$this->putInRow('Name',$name);
		$this->putInRow('Sex',$sex);
		$this->putInRow('Email',$email);
		$this->putInRow('Phone',$phone);
		$this->putInRow('Address',$address);
		$this->putInRow('Client Type', empty($affiliate_id) ? 'Independent' : 'Affiliated' );


		if(!empty($affiliate_id))
		{
			$Affiliate = App('App\Entities\Affiliate')->edit($affiliate_id);
			
			// dd($affiliate_id);
			// dd($Affiliate);
			
			extract($Affiliate);

			//Save affiliate email if any available
			$this->AffiliateEmail = $Affiliate['affiliate_email'];

			$this->putInRow('&nbsp;','&nbsp;');

			$this->putInRow('Institution',$affiliate_name);
			$this->putInRow('Institution code',$affiliate_code);
			$this->putInRow('Designation',$position_instituition);
			$this->putInRow('Identification number',$identification_no);
		}
	}

}

