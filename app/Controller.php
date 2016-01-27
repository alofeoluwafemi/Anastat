<?php

namespace App;

use App\Entities\User;
use App\Entities\Database;
use App\Authentication\Auth;
use App\AdminController;
use App\AffiliateController;

class Controller{
	
	public function __construct()
	{
		//
	}

	public function __call($method,$arg)
	{
		return $this->NotFoundException();
		exit;
	}

	public function admin($page="",$id = 0)
	{

		if(!is_logged_and_is_admin())
		{
			$notification = "Please Login To Proceed !";

			$url = geturl('login');

			redirect_to($url,array('as' => 'notification','message' => $notification));
		}

		switch ($page) {
			case 'dashboard':
				return App('App\AdminController')->dashboard();
				break;
			case 'affiliate':
				return App('App\AdminController')->affiliate();
				break;
			case 'clients':
				return App('App\AdminController')->clients();
				break;
			case 'addaffiliate':
				return App('App\AdminController')->addaffiliate();
				break;
			case 'editaffiliate':
				return App('App\AdminController')->editaffiliate();
				break;
			case 'database':
				return App('App\AdminController')->database();
				break;
			case 'addatabase':
				return App('App\AdminController')->addatabase();
				break;
			case 'editdatabase':
				return App('App\AdminController')->editdatabase();
				break;
			case 'clients':
				return App('App\AdminController')->clients();
				break;
			case 'requests':
				return App('App\AdminController')->requests();
				break;
			case 'viewrequest':
				return App('App\AdminController')->viewrequest();
				break;
			case 'tables':
				return App('App\AdminController')->tables($id);
				break;
			case 'databasetables':
				return App('App\AdminController')->databasetables($id);
				break;
			case 'addtables':
				return App('App\AdminController')->addtables();
				break;
			case 'editable':
				return App('App\AdminController')->editable($id);
				break;
			case 'reassigntables':
				return App('App\AdminController')->reassigntables($id);
				break;
			case 'levelaggregation':
				return App('App\AdminController')->levelaggregation();
				break;
			case 'addaggregation':
				return App('App\AdminController')->addaggregation();
				break;
			case 'editaggregation':
				return App('App\AdminController')->editaggregation();
				break;
			case 'categoryaggregation':
				return App('App\AdminController')->categoryaggregation();
				break;
			case 'addcategory':
				return App('App\AdminController')->addcategory();
				break;
			case 'editcategory':
				return App('App\AdminController')->editcategory();
				break;
			case 'variables':
				return App('App\AdminController')->variables();
				break;
			case 'addvariables':
				return App('App\AdminController')->addvariables();
				break;
			case 'editvariable':
				return App('App\AdminController')->editvariable();
				break;
			case 'frequency':
				return App('App\AdminController')->frequency();
				break;
			case 'addfrequency':
				return App('App\AdminController')->addfrequency();
				break;
			case 'editfrequency':
				return App('App\AdminController')->editfrequency();
				break;
			case 'period':
				return App('App\AdminController')->period();
				break;
			case 'addperiod':
				return App('App\AdminController')->addperiod();
				break;
			case 'editperiod':
				return App('App\AdminController')->editperiod();
				break;
			case 'survey':
				return App('App\AdminController')->survey();
				break;
			case 'addsurvey':
				return App('App\AdminController')->addsurvey();
				break;
			case 'users':
				return App('App\AdminController')->users();
				break;
			case 'adduser':
				return App('App\AdminController')->adduser();
				break;
			case 'edituser':
				return App('App\AdminController')->edituser();
				break;
			case 'addaffiliateduser':
				return App('App\AdminController')->addaffiliateduser();
				break;
			case 'sector':
				return App('App\AdminController')->sector();
				break;
			case 'addsector':
				return App('App\AdminController')->addsector();
				break;
			case 'report':
				return App('App\AdminController')->getreport();
				break;
			default:
				return App('App\AdminController')->dashboard();
				break;
		}
	}

	//Affiliate Area
	public function affiliate($page="")
	{

		if(!is_logged_and_is_affiliate())
		{
			$notification = "Please Login To Proceed !";

			$url = geturl('affiliatelogin');

			redirect_to($url,array('as' => 'notification','message' => $notification));
		}

		switch ($page) {
			case 'dashboard':
				return App('App\AffiliateController')->dashboard();
				break;
			case 'action':
				return App('App\AffiliateController')->action();
				break;
			default:
				return App('App\AffiliateController')->dashboard();
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
					view('affiliatelogin',compact('notification'));
				}

			exit;
		}

		view('affiliatelogin');
	}


	private function NotFoundException()
	{
		// header("HTTP/1.0 404 Not Found");
		view('404');
	}

	public function database($id)
	{
		$Tables = App('App\Entities\Table')->gettables($id);

		view('tables',compact('Tables'));
	}

	public function contactus()
	{
		if(request() == "post")
		{
			extract($_POST);

			$head  = "Message From: ".$firstname. " " .$lastname."\r\n<br/>";
			$head .= "Phone Number: ".$phone."\r\n<br/>";
			$head .= "Email: ".$email."\r\n<br/><br/>";

			$message = wordwrap($message,70,"\r\n");

			$body = '<b>'.$head.$message.'<b/>';

			// @mail(config('site-email'),$subject,$body);

			sendmail(config('site-email'),$firstname,$subject,$body);

			$notification = "We received your message,we shall get back to you shortly";
		}

		view('contact',compact('notification'));
	}

	public function clientarea()
	{
		if(request() == "post")
		{
			$email = $_POST['email'];

			$Requests = App('App\Entities\Request')->listsByClientEmail($email);

			// dd($Requests);
			if(empty($Requests)) $notfound = 'Sorry no match found for this record</a>';
			
			if(!empty($Requests)) $notification = 'Match found!';
		}

		view('clientarea',compact('Requests','notification','notfound'));
	}

	public function surveyresearch()
	{
		$Micros        = App('App\Entities\Database')->allmicros();
		$Macros        = App('App\Entities\Database')->allmacros();
		$Instituitions = App('App\Entities\Affiliate')->lists();
		$Sectors       = App('App\Entities\Survey')->listSectors();

		if(!empty($_GET['for']) && ($_GET['for'] == 'survey') && !empty($_GET['id']) )
		{
			$id = $_GET['id'];
			$Surveys = App('App\Entities\Survey')->getSurveys($id);

			// dd($Surveys);

			view('surveysectors',compact('Micros','Macros','Instituitions','Sectors','Surveys'));

			exit;
		}

		view('surveyresearch',compact('Micros','Macros','Instituitions','Sectors'));
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

	public function laggregation($id)
	{
		$Laggregation = App('App\Entities\Laggregation')->getaggregations($id);
		
		view('laggregation',compact('Laggregation'));
	}

	public function caggregation($id)
	{
		$Caggregation = App('App\Entities\Caggregation')->getcategories($id);

		view('caggregation',compact('Caggregation'));
	}

	public function frequency($id)
	{
		$Frequencies = App('App\Entities\Frequency')->getall($id);

		view('frequency',compact('Frequencies'));
		// view('frequency');
	}

	//Delete a user request from DB from frontend
	//were it has not bin own by a client
	public function ddr($id)
	{
		App('App\Entities\Request')->dropWithoutClient($id);
	}

	public function variable($tableid,$levelid)
	{
		$Variables = App('App\Entities\Variable')->getall($tableid,$levelid);

		view('variable',compact('Variables'));
	}

	public function periods($id)
	{
		$Periods = App('App\Entities\Period')->getall($id);

		view('period',compact('Periods'));
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

		view('editrequest');
		exit;
	}

	public function jserror()
	{
		view('jserror');
		exit;
	}

	public function printrequest()
	{
		$id      = $_GET['overview'];
		$Request = App('App\Entities\Request')->getrequest($id);
		// dd($Request);
		view('request',compact('Request'));
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
					view('login',compact('notification'));
				}

			exit;
		}

		view('login');
	}

	public function logout()
	{
		logout();
	}

	public function addclient()
	{
		/**
		 * Send admin a new mail if new request made by client
		 * others can hook into this
		*/
		register_hook('new_client','mailadmin');

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

		fire_hook('new_client');

		view('index',compact('notification','Micros','Macros','Instituitions'));

		exit;
	}
}