<?php

namespace App\Controller;

use App\Entities\User;
use App\Entities\Database;
use App\Authentication\Auth;
use App\Controller\AdminController;
use App\Controller\AffiliateController;

class BaseController{
	
	public function __construct()
	{
		//
	}

	public function __call($method,$arg)
	{
		return $this->NotFoundException();
		exit;
	}

    /**
     * @param string $page
     * @param int $id
     * @param string $action
     * @return mixed
     */
    public function admin($page="", $id = 0, $action="")
	{

		if(!is_logged_and_is_admin())
		{
			$notification = "Please Login To Proceed !";

			$url = geturl('login');

			redirect_to($url,array('as' => 'notification','message' => $notification));
		}

		switch ($page) {
			case 'dashboard':
				return App('App\Controller\AdminController')->dashboard();
				break;
			case 'affiliate':
				return App('App\Controller\AdminController')->affiliate($id,$action);
				break;
			case 'clients':
				return App('App\Controller\AdminController')->clients();
				break;
			case 'addaffiliate':
				return App('App\Controller\AdminController')->addaffiliate();
				break;
			case 'editaffiliate':
				return App('App\Controller\AdminController')->editaffiliate();
				break;
			case 'database':
				return App('App\Controller\AdminController')->database($id,$action);
				break;
			case 'addatabase':
				return App('App\Controller\AdminController')->addatabase();
				break;
			case 'editdatabase':
				return App('App\Controller\AdminController')->editdatabase();
				break;
			case 'assignfrequency':
				return App('App\Controller\AdminController')->assignfrequency($id);
				break;
			case 'reassignvariable':
				return App('App\Controller\AdminController')->reassignvariables($id);
				break;
			case 'requests':
				return App('App\Controller\AdminController')->requests($id,$action);
				break;
			case 'viewrequest':
				return App('App\Controller\AdminController')->viewrequest();
				break;
			case 'tables':
				return App('App\Controller\AdminController')->tables($id,$action);
				break;
			case 'databasetables':
				return App('App\Controller\AdminController')->databasetables($id);
				break;
			case 'addtables':
				return App('App\Controller\AdminController')->addtables();
				break;
			case 'editable':
				return App('App\Controller\AdminController')->editable($id);
				break;
			case 'reassigntables':
				return App('App\Controller\AdminController')->reassigntables($id);
				break;
			case 'levelaggregation':
				return App('App\Controller\AdminController')->levelaggregation($id,$action);
				break;
			case 'addaggregation':
				return App('App\Controller\AdminController')->addaggregation();
				break;
			case 'editaggregation':
				return App('App\Controller\AdminController')->editaggregation();
				break;
			case 'categoryaggregation':
				return App('App\Controller\AdminController')->categoryaggregation($id,$action);
				break;
			case 'addcategory':
				return App('App\Controller\AdminController')->addcategory();
				break;
			case 'editcategory':
				return App('App\Controller\AdminController')->editcategory();
				break;
			case 'variables':
				return App('App\Controller\AdminController')->variables($id,$action);
				break;
			case 'addvariables':
				return App('App\Controller\AdminController')->addvariables();
				break;
			case 'editvariable':
				return App('App\Controller\AdminController')->editvariable();
				break;
			case 'tablevariables':
				return App('App\Controller\AdminController')->tablevariables($id);
				break;
			case 'frequency':
				return App('App\Controller\AdminController')->frequency($id,$action);
				break;
			case 'addfrequency':
				return App('App\Controller\AdminController')->addfrequency();
				break;
			case 'editfrequency':
				return App('App\Controller\AdminController')->editfrequency();
				break;
			case 'period':
				return App('App\Controller\AdminController')->period();
				break;
			case 'addperiod':
				return App('App\Controller\AdminController')->addperiod();
				break;
			case 'editperiod':
				return App('App\Controller\AdminController')->editperiod();
				break;
			case 'survey':
				return App('App\Controller\AdminController')->survey($id,$action);
				break;
			case 'addsurvey':
				return App('App\Controller\AdminController')->addsurvey();
				break;
			case 'users':
				return App('App\Controller\AdminController')->users($id,$action);
				break;
			case 'adduser':
				return App('App\Controller\AdminController')->adduser();
				break;
			case 'edituser':
				return App('App\Controller\AdminController')->edituser();
				break;
			case 'addaffiliateduser':
				return App('App\Controller\AdminController')->addaffiliateduser();
				break;
			case 'sector':
				return App('App\Controller\AdminController')->sector($id,$action);
				break;
			case 'addsector':
				return App('App\Controller\AdminController')->addsector();
				break;
			case 'report':
				return App('App\Controller\AdminController')->getreport();
				break;
			case 'addsubscription':
				return App('App\Controller\AdminController')->addsubscription();
				break;
			case 'subscriptions':
				return App('App\Controller\AdminController')->subscriptions();
				break;
            case 'subscription':
                return App('App\Controller\AdminController')->subscription($id,$action);
                break;
            case 'condition':
                return App('App\Controller\AdminController')->condition();
                break;
            case 'contact':
                return App('App\Controller\AdminController')->contact();
                break;
			default:
				return App('App\Controller\AdminController')->dashboard();
				break;
		}
	}

	//Affiliate Area
	public function affiliate($page,$id = NULL)
	{
		if($page == "login") return $this->affiliatelogin();

		if(!is_logged_and_is_affiliate())
		{
			$notification = "Please Login To Proceed !";

			$url = geturl('/affiliate/login');

			redirect_to($url,array('as' => 'notification','message' => $notification));
		}

		switch ($page) {
			case 'drop':
				return App('App\Controller\AffiliateController')->dashboard();
				break;
			case 'view':
				return App('App\Controller\AffiliateController')->view($id);
				break;
			default:
				return App('App\Controller\AffiliateController')->dashboard();
				break;
		}
	}

	// Try Login an affiliate
	public function affiliatelogin()
	{
		if(request() == "post")
		{
			$data = $_POST;

			$logged = loginaffiliate($data);

				if($logged)
				{
				  return $this->affiliate('dashboard');

				}else{
					$notification = "Invalid username or password !";

					backview('affiliate/login',compact('notification'));
				}
		}

		backview('affiliate/login');
	}

	private function NotFoundException()
	{
		header("HTTP/1.0 404 Not Found");
		errorview('404');
	}

	public function database($id)
	{
		$Tables = App('App\Entities\Table')->gettables($id);

		frontview('tables',compact('Tables'));
	}

    /**
     *
     */
    public function contactus()
	{
		if(request() == "post")
		{
			$firstname = "";
            $lastname = "";
            $phone = "";
            $email = "";
            $message = "";
            $subject = "";

			extract($_POST);

			$head  = "Message From: ".$firstname. " " .$lastname."\r\n<br/>";
			$head .= "Phone Number: ".$phone."\r\n<br/>";
			$head .= "Email: ".$email."\r\n<br/><br/>";

			$message = wordwrap($message,70,"\r\n");

			$body = '<b>'.$head.$message.'<b/>';

			sendmail(config('site-email'),$firstname,$subject,$body);

			$notification = "We received your message,we shall get back to you shortly";
		}

		$Contact = App('App\Entities\Contact')->get();

		frontview('contact',compact('notification','Contact'));
	}

    /**
     *
     */
    public function userarea()
	{
		if(request() == "post")
		{
			$code = $_POST['code'];

			$Requests = App('App\Entities\Request')->getByTransactionId($code);
			$Client   = !empty($Requests[0]) ? $Requests[0] : array();

			if(empty($Requests)) 
			{
				$notification = 'Sorry no match found for this submission code</a>';

				frontview('userarea/code',compact('notification'));

				exit;
			}
			
			frontview('userarea/view',compact('Requests','Client'));

			exit;
		}

		frontview('userarea/code',compact('Requests','notification','Client'));
	}

	public function surveyresearch($id = "",$action = "")
	{
		if($action == "sectors") return $this->subSectorList($id);

		$Micros        = App('App\Entities\Database')->allmicros();
		$Macros        = App('App\Entities\Database')->allmacros();
		$Instituitions = App('App\Entities\Affiliate')->lists();
		$Sectors       = App('App\Entities\Survey')->listSectors();

		

		frontview('surveyresearch',compact('Micros','Macros','Instituitions','Sectors'));
	}

	/**
	 * 
	 */
	private function subSectorList($id)
	{
		$Surveys = App('App\Entities\Survey')->getSurveys($id);
		$Micros        = App('App\Entities\Database')->allmicros();
		$Macros        = App('App\Entities\Database')->allmacros();
		$Instituitions = App('App\Entities\Affiliate')->lists();
		$Sectors       = App('App\Entities\Survey')->listSectors();

		$notification = "No downloads available under this sub sector";

		frontview('surveysectors',compact('Micros','Macros','Instituitions','Sectors','Surveys','notification'));
	}

	/**
	 * Show help content
	 */
	public function help($id)
	{
		$Database = App('App\Entities\Database')->edit($id);
		$help = $Database['help'];

		if(!empty($help)) print($help);
		if(empty($help)) print("No help content added");
	}

	/**
	 * Show agreement content
	 */
	public function agreement()
	{
		$Condition = App('App\Entities\Condition')->get();
		$term      = $Condition['content'];

		if(!empty($term)) print($term);
		if(empty($term))  print("Terms &amp; conditions apply");
	}

	public function laggregation($id)
	{
		$Laggregation = App('App\Entities\Laggregation')->TableAggregations($id);
		
		frontview('laggregation',compact('Laggregation'));
	}

	public function caggregation($id)
	{
		$Caggregation = App('App\Entities\Caggregation')->getcategories($id);

		frontview('caggregation',compact('Caggregation'));
	}

	public function frequency($id)
	{
		$Frequencies = App('App\Entities\Frequency')->getall($id);

		frontview('frequency',compact('Frequencies'));
	}

	//Delete a user request from DB from frontend
	//were it has not bin own by a client
	public function ddr($id)
	{
		App('App\Entities\Request')->dropWithoutClient($id);
	}

	public function variable($tableid,$levelid,$frequencyid)
	{
		$Variables = App('App\Entities\Variable')->getall($tableid,$levelid,$frequencyid);

		frontview('variable',compact('Variables'));
	}

    /**
     * @param $id
     */
    public function periods($id)
	{
		$table 	= "";
		$level 	= "";
		$freq 	= "";
		$from 	= "";
		$to 	= "";

		extract($_GET);

		/**
		* If calendar type set by administrator on
		* generic table is year list: get the range to display
		*/
		$Range = App('App\Entities\Period')->getDateRange($table,$level,$freq);

		extract($Range);

		$from   = $from != null  ? $from  : 1990;
		$to 	= $to   != null  ? $to 	  : date('Y');


		frontview('period',compact('Periods','Range','calendar_type','from','to'));
	}

	public function newrequest()
	{
		$request = $_POST;
		// dd($request);
		echo App('App\Entities\Request')->insert($request);
	}

	public function editrequest()
	{
		if(request() == "post")
		{
			$data = $_POST;
			// dd($_POST);

			echo App('App\Entities\Request')->update($data);
		}

		frontview('editrequest');
		exit;
	}

	public function jserror()
	{
		errorview('jserror');
		exit;
	}

	public function printrequest($id,$action)
	{
	
		$Request = App('App\Entities\Request')->getrequest($id);
		frontview('request',compact('Request'));
	}

	//ADMIN
	public function login()
	{

		if(request() == "post")
		{
			$data = $_POST;
			$logged = login($data);

				if($logged)
				{
				  return $this->admin('dashboard');

				}else{
					$notification = "Invalid username or password !";
					// dd(md5('12345password;'));
					frontview('login',compact('notification'));
				}

			exit;
		}

		frontview('login');
	}

	public function logout()
	{
		$affiliate = $_GET['affiliate'];

		$url = '/admin/login';

		if(!empty($affiliate)) $url = '/admin/affiliate/login';

		logout($url);
	}

	public function addclient()
	{
		/**
		 * Send admin a new mail if new request made by client
		 * others can hook into this
		*/
		// register_hook('new_client','mailadmin');

		$data = $_POST;
		App('App\Entities\Client')->addclient($data);

		$Micros = App('App\Entities\Database')->allmicros();
		$Macros = App('App\Entities\Database')->allmacros();
		$Instituitions = App('App\Entities\Affiliate')->lists();

		if($data['clienttype'] == "personal")
			{
				$notification = 'Thank you for using the Anastat Platform. 
				Our systems are already processing your request. Kindly check your e-mail in a moment for updates on the status of you';
			}
		if($data['clienttype'] == "affiliate")
			{
				$notification = 'Thank you for using the Anastat Platform. Our systems are already processing your request. 
				Kindly contact the help desk in your Instituition';
			}

		// fire_hook('new_client');

		frontview('index',compact('notification','Micros','Macros','Instituitions'));

		exit;
	}
}