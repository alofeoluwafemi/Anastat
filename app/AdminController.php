<?php

namespace App;
use App\Singleton;

class AdminController{

	use Singleton;

	public function __construct()
	{
		//
	}

	public function dashboard()
	{
		$Affiliates = App('App\Entities\Affiliate')->count();
		$Clients    = App('App\Entities\Client')->count();
		$Requests   = App('App\Entities\Request')->count();

		view('dashboard',compact('Affiliates','Clients','Requests'));

		exit;
	}

	/**
	 * Download General Report Into ExcelSheet
	 */
	public function getreport()
	{
		return App('App\Report')->GetAllAffiliateReport();
	}

	public function affiliate()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Affiliate')->drop($id);

			$notification = "Affiliate successfully deregistered";
		}

		//Get Report For An Affiliate
		if(isset($_GET['for']) && $_GET['for'] == 'report' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			return App('App\Report')->GetAffiliateReport($id);
		}

		//Affliate lists
		$Affiliates = App('App\Entities\Affiliate')->lists();
		
		view('affiliate',compact('notification','Affiliates'));

		exit;
	}

	/**
	 * Add new affiliate into storage
	 */
	public function addaffiliate()
	{
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Affiliate')->addnew($data);

			$notification = "Affiliate successfully added";

			view('addaffiliate',compact('notification'));

			exit;
		}

		view('addaffiliate');

		exit;
	}


	/**
	 * Edit
	 */
	public function editaffiliate()
	{
		$id = intval($_GET['id']);


		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Affiliate')->update($data);

			$notification = "Affiliate details successfully updated";

			$Affiliate = App('App\Entities\Affiliate')->edit($id);

			view('editaffiliate',compact('notification','Affiliate'));

			exit;
		}

		$Affiliate = App('App\Entities\Affiliate')->edit($id);

		view('editaffiliate',compact('Affiliate'));

		exit;
	}
	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Tables
	 ***************************************************************************************************************************/

	/**
	 * View Table Lists
	 */
	public function tables($id = 1)
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Table')->drop($id);

			$notification = "Table and associated data successfully removed";
		} 

		//Table lists
		$id = $_GET['id'];
		$Tables = App('App\Entities\Table')->lists($id);

		view('tablelists',compact('notification','Tables'));

		exit;
	}

	/**
	 * List database tables
	 */
	public function databasetables($id)
	{

		if(request() == "post")
		{
			$data = $_POST;
			App('App\Entities\Table')->assign($data);

			redirect_to("/admin/databasetables/{$id}?assign=true");
		}

		if(isset($_GET['assigntables'])) return  $this->assigntables($id);

		$database_id = $id;

		//Database lists
		$Tables = App('App\Entities\Table')->listByTable($id);

		view('tablelists',compact('Tables','database_id'));

		exit;
	}

	/**
	 * Form to assign tables for a database
	*/
	public function assigntables($id)
	{
		$Database    = App('App\Entities\Database')->edit($id);
		$Tables 	 = App('App\Entities\Table')->lists();
		$name        = $Database['databasename'];
		$database_id = $id;

		view('assigntable',compact('name','database_id','Tables'));
		exit;
	}


	/**
	 * Form to assign tables for a database
	*/
	public function reassigntables($id)
	{
		if(request() == "post")
		{
			$datas = $_POST;
			App('App\Entities\Table')->sync($datas);

			redirect_to("/admin/databasetables/{$id}?assign=true");
		}

		$Database    = App('App\Entities\Database')->edit($id);
		$Tables 	 = App('App\Entities\Table')->lists();
		$Assigned 	 = App('App\Entities\Table')->pivot($id);
		$name        = $Database['databasename'];
		$database_id = $id;

		// dd($Assigned);

		view('reassigntables',compact('name','database_id','Tables','Assigned'));
		exit;
	}

	/**
	 * Add new tables into storage
	 */
	public function addtables()
	{

		//Database lists
		$Databases = App('App\Entities\Database')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Table')->addnew($data);

			$notification = "Table(s) successfully added";

			view('addtables',compact('notification'));

			exit;
		}

		view('addtables',compact('Databases'));

		exit;
	}

	/**
	 * Add new database into storage
	*/
	public function editable($id)
	{
		$id = intval($id);

		//Database lists
		$Databases = App('App\Entities\Database')->lists();
		
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Table')->update($data);

			$notification = "Table details successfully updated";

			$Table = App('App\Entities\Table')->edit($id);

			view('editable',compact('notification','Table'));

			exit;
		}


		$Table = App('App\Entities\Table')->edit($id);

		view('editable',compact('Table','Databases'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Level Aggregation
	 ***************************************************************************************************************************/

	/**
	 * View Aggregation Lists
	 */
	public function levelaggregation()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Laggregation')->drop($id);

			$notification = "Level of aggregation and associated data successfully removed";
		} 

		//Table lists
		$Results      = App('App\Entities\Laggregation')->all(true);
		$Aggregations = !empty($Results) ? $Results->results() : array();
		$Links        = !empty($Results) ? $Results->links() : "";

		// dd($Aggregations);

		view('laggregationlists',compact('notification','Aggregations','Links'));

		exit;
	}

	/**
	 * Add new tables into storage
	 */
	public function addaggregation()
	{

		//Table lists
		$Tables = App('App\Entities\Table')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Laggregation')->addnew($data);

			$notification = "Aggregation(s) successfully added";

			view('addaggregations',compact('notification','Tables'));

			exit;
		}

		view('addaggregations',compact('Tables'));

		exit;
	}

	/**
	 * Edit Level Aggregation
	*/
	public function editaggregation()
	{
		$id = intval($_GET['id']);

		//Table lists
		$Tables = App('App\Entities\Table')->lists();

		// dd($Tables);
		

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Laggregation')->update($data);

			$notification = "Aggregation details successfully updated";

			$Aggregation = App('App\Entities\Laggregation')->edit($id);

			view('editlaggregation',compact('notification','Aggregation','Tables'));

			exit;
		}


		$Aggregation = App('App\Entities\Laggregation')->edit($id);

		// dd($Aggregation['tables']);

		view('editlaggregation',compact('Tables','Aggregation'));

		exit;
	}


	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Category Aggregation
	 ***************************************************************************************************************************/

	/**
	 * View Category Aggregation Lists
	 */
	public function categoryaggregation()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Caggregation')->drop($id);

			$notification = "Category of aggregation and associated data successfully removed";
		} 

		//Categories lists
		$Results      = App('App\Entities\Caggregation')->lists(true);
		$Categories   = !empty($Results) ? $Results->results() : array();
		$Links        = !empty($Results) ? $Results->links() : "";

		// dd($Categories);

		view('categorylists',compact('notification','Categories','Links'));

		exit;
	}

	/**
	 * Add new tcategory to storage
	 */
	public function addcategory()
	{

		//Level lists
		$Levels = App('App\Entities\Laggregation')->lists();
		// dd($Levels);

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Caggregation')->addnew($data);

			$notification = "Categories successfully added";

			view('addcategory',compact('notification','Levels'));

			exit;
		}

		view('addcategory',compact('Levels'));

		exit;
	}

	/**
	 * Edit Category Aggregation
	*/
	public function editcategory()
	{
		$id = intval($_GET['id']);

		//Levels lists
		$Levels = App('App\Entities\Laggregation')->lists();
		
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Caggregation')->update($data);

			$notification = "Category details successfully updated";

			$Category = App('App\Entities\Caggregation')->edit($id);

			view('editcategory',compact('notification','Category','Levels'));

			exit;
		}


		$Category = App('App\Entities\Caggregation')->edit($id);

		view('editcategory',compact('Levels','Category'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Variables
	 ***************************************************************************************************************************/

	/**
	 * View Variable Lists
	 */
	public function variables()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Variable')->drop($id);

			$notification = "Variable successfully removed";
		} 

		//Variable lists
		$Results   = App('App\Entities\Variable')->lists(true);
		$Variables = !empty($Results) ? $Results->results() : array();
		$Links     = !empty($Results) ? $Results->links() : "";

		view('variablelists',compact('notification','Variables','Links'));

		exit;
	}

	/**
	 * Add new variable to storage
	 */
	public function addvariables()
	{
		$Aggregations = App('App\Entities\Laggregation')->lists();

		$Tables = App('App\Entities\Table')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Variable')->addnew($data);

			$notification = "Variable successfully added";

			view('addvariable',compact('notification','Tables','Aggregations'));

			exit;
		}

		view('addvariable',compact('Tables','Aggregations'));

		exit;
	}

	/**
	 * Edit Variables
	*/
	public function editvariable()
	{
		$id = intval($_GET['id']);

		$Tables = App('App\Entities\Table')->lists();

		$Aggregations = App('App\Entities\Laggregation')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Variable')->update($data);

			$notification = "Variable details successfully updated";

			$Variable = App('App\Entities\Variable')->edit($id);

			view('editvariable',compact('notification','Variable','Tables','Aggregations'));

			exit;
		}

		$Variable = App('App\Entities\Variable')->edit($id);

		view('editvariable',compact('Variable','Tables','Aggregations'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Survey Free Database
	 ***************************************************************************************************************************/

	/**
	 * View Variable Lists
	 */
	public function survey()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Survey')->drop($id);

			$notification = "Survey file successfully removed";
		} 

		if(isset($_GET['action']) && $_GET['action'] == 'download' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			$File = App('App\Entities\Survey')->get($id);

			$filepath = config('storage_path_survey').$File['pathname'];
			
			downloadfile($filepath);
		} 

		//Survey lists
		$Surveys = App('App\Entities\Survey')->lists();

		view('surveylists',compact('notification','Surveys'));

		exit;
	}

	/**
	 * Add new survey pdfs to storage
	 */
	public function addsurvey()
	{

		$Sectors = App('App\Entities\Survey')->listSectors();

		if(request() == "post")
		{

			$data = $_POST;

			$filename = getFileName('file');
			$filetype = getFileExtension($filename);
			$allowed  = ['pdf','docx','doc','xlsx','xls'];
			// dd($filetype);

			if(hasFile('file'))
			{
				if(!in_array($filetype,$allowed)){
					App('App\Entities\Survey')->addnew($data);
					$notification = "Free survey database file successfully added";
				}
				else{
					$notification = "Error: Only pdf,docx,doc,xlsx,xls uploads allowed!";
				}
			}else{
					App('App\Entities\Survey')->addnew($data);
					$notification = "Free survey database file successfully added";
			}
			
			view('addsurvey',compact('notification','Sectors'));

			exit;
		}

		view('addsurvey',compact('Sectors'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Frequencies
	 ***************************************************************************************************************************/

	/**
	 * View Frequency Lists
	 */
	public function frequency()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Frequency')->drop($id);

			$notification = "Frequency successfully removed";
		} 

		//Frequency lists
		$Results     = App('App\Entities\Frequency')->lists(true);
		$Frequencies = !empty($Results) ? $Results->results() : array();
		$Links       = !empty($Results) ? $Results->links() : "";

		// dd($Frequencies);

		view('frequencylists',compact('notification','Frequencies','Links'));

		exit;
	}

	/**
	 * Add new variable to storage
	 */
	public function addfrequency()
	{

		//Table lists
		$Tables = App('App\Entities\Table')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Frequency')->addnew($data);

			$notification = "Frequency successfully added";

			view('addfrequency',compact('Tables','notification'));;

			exit;
		}

		view('addfrequency',compact('Tables'));

		exit;
	}

	/**
	 * Edit Variables
	*/
	public function editfrequency()
	{
		$id = intval($_GET['id']);

		$Tables    = App('App\Entities\Table')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Frequency')->update($data);

			$notification = "Frequency details successfully updated";

			$Frequency = App('App\Entities\Frequency')->edit($id);

			view('editfrequency',compact('notification','Tables','Frequency'));

			exit;
		}

		
		$Frequency = App('App\Entities\Frequency')->edit($id);

		view('editfrequency',compact('Tables','Frequency'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Periods
	 ***************************************************************************************************************************/

	/**
	 * View Period Lists
	 */
	public function period()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Period')->drop($id);

			$notification = "Period successfully removed";
		} 

		//Frequency lists
		$Periods = App('App\Entities\Period')->lists();

		view('periodlists',compact('notification','Periods'));

		exit;
	}

	/**
	 * Add new period to storage
	 */
	public function addperiod()
	{

		$Frequencies = App('App\Entities\Frequency')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Period')->addnew($data);

			$notification = "Period successfully added";

			view('addperiod',compact('notification','Frequencies'));

			exit;
		}

		view('addperiod',compact('Frequencies'));

		exit;
	}

	/**
	 * Edit Period
	*/
	public function editperiod()
	{
		$id = intval($_GET['id']);

		$Frequencies = App('App\Entities\Frequency')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Period')->update($data);

			$notification = "Period details successfully updated";

			$Period = App('App\Entities\Period')->edit($id);

			view('editperiod',compact('notification','Frequencies','Period'));

			exit;
		}

		$Period = App('App\Entities\Period')->edit($id);

		view('editperiod',compact('Frequencies','Period'));

		exit;
	}


	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Databases
	 ***************************************************************************************************************************/

	public function database()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Database')->drop($id);

			$notification = "Database successfully Removed";
		} 

		//Database lists
		$Databases = App('App\Entities\Database')->lists();
		
		view('database',compact('notification','Databases'));

		exit;
	}

	/**
	 * Add new database into storage
	 */
	public function addatabase()
	{
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Database')->addnew($data);

			$notification = "Database successfully added";

			view('addatabase',compact('notification'));

			exit;
		}

		view('addatabase');

		exit;
	}

	/**
	 * Add new database into storage
	 */
	public function editdatabase()
	{
		$id = intval($_GET['id']);


		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Database')->update($data);

			$notification = "Database details successfully updated";

			$Database = App('App\Entities\Database')->edit($id);

			view('editdatabase',compact('notification','Database'));

			exit;
		}

		$Database = App('App\Entities\Database')->edit($id);

		view('editdatabase',compact('Database'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Client
	 ***************************************************************************************************************************/

	public function clients()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Client')->drop($id);

			$notification = "Client and all requests successfully removed";
		}

		if(isset($_GET['action']) && $_GET['action'] == 'view' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			$Client = App('App\Entities\Client')->get($id);

			view('client',compact('notification','Client'));

			exit;
		}

		if(isset($_GET['action']) && $_GET['action'] == 'requests' && !empty($_GET['id']))
		{
			$id = $_GET['id'];

			$Requests = App('App\Entities\Request')->listsByClient($id);
			// dd($Requests);
		
			view('requests',compact('notification','Requests'));

			exit;
		}

		if(isset($_GET['for']) && $_GET['for'] == 'affiliate' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			$Clients = App('App\Entities\Client')->listByAffiliate($id);

			view('clients',compact('notification','Clients'));
		}

		//Clients lists
		$Clients = App('App\Entities\Client')->lists();

		view('clients',compact('notification','Clients'));

		exit;
	}


	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Requests
	 ***************************************************************************************************************************/

	public function requests()
	{
		$Affiliates = App('App\Entities\Affiliate')->lists();

		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Request')->drop($id);

			$notification = "Request successfully removed";
		}

		if(isset($_GET['action']) && $_GET['action'] == 'view' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			$Request = App('App\Entities\Request')->get($id);

			// dd($Request);
			view('viewrequest',compact('Affiliates','notification','Request'));

			exit;
		}

		if(isset($_GET['for']) && $_GET['for'] == 'client' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			$Requests = App('App\Entities\Request')->listsByClient($id);

			// dd($Requests);
			view('clientrequests',compact('Affiliates','notification','Requests'));
		}

		if(isset($_GET['for']) && $_GET['for'] == 'status' && !empty($_GET['id']))
		{
			$id     = $_GET['id'];
			$status = $_GET['status'];

			App('App\Entities\Request')->updateStatus($id,$status);

			$notification = "Request status successfully updated";

			$Request = App('App\Entities\Request')->get($id);

			view('viewrequest',compact('Affiliates','notification','Request'));
			exit;
		}


		//Paginate result here
		if(isset($_GET['for']) && $_GET['for'] == 'sort')
		{
			$affiliate = $_GET['affiliate'];
			$type      = $_GET['type'];
			$status    = $_GET['status'];
			$approval  = $_GET['approved'];

			$Results  = App('App\Entities\Request')->sort($affiliate,$type,$status,$approval);
			$Requests = !empty($Results) ? $Results->results() : array();
			$append   =  'for=sort&affiliate='.$affiliate.'&type='.$type.'&status='.$status.'&approved='.$approval;
			$Links    = !empty($Results) ? $Results->links($append) : "";

			$notification = "Sorted Data Returned!";

			view('requests',compact('Affiliates','notification','Requests','Links'));
			exit;
		}

		//Requests lists paginated
		$Results  = App('App\Entities\Request')->lists(true);
		$Requests = !empty($Results) ? $Results->results() : array();
		$Links    = !empty($Results) ? $Results->links() : "";
		
		view('requests',compact('Affiliates','notification','Requests','Links'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Users
	 ***************************************************************************************************************************/
	/**
	 * View Users Lists
	 */
	public function users()
	{
		if(!powerabove()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];

			if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));
			
			if($id == user('id')) return redirect_to('dashboard',array('as' => 'message','message' => 'You cannot delete logged in user.Logout first'));

			App('App\Entities\User')->drop($id);

			$notification = "User successfully deleted from platform";
		} 

		//Users lists
		$Users = App('App\Entities\User')->lists();

		view('users',compact('notification','Users'));

		exit;
	}

	/**
	 * Add new user to storage
	 */
	public function adduser()
	{
		if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

		if(request() == "post")
		{
			$data = $_POST;

			if($data['password'] != $data['password_confirm'])
			{
					$notification = "Error: Password fields does not match";

			}elseif(strlen($data['username']) < 5)
			{
					$notification = "Error: Username cannot be less than 5 characters";

			}elseif(strlen($data['password']) < 5)
			{
					$notification = "Error: Password cannot be less than 5 characters";

			}elseif(!preg_match('#[a-z][0-9]#', $data['password'])) 
			{
					$notification = "Error: Password must be combination of alphanumeric keys";
				
			}elseif(App('App\Entities\User')->getwithUsername($data['username']))
			{
					$notification = "Error: Username already exist!";
			}
			else{

					$data = $_POST;

					App('App\Entities\User')->addnew($data);

					$notification = "User successfully added on platform";
			}

			view('adduser',compact('notification'));

			exit;
			
		}

		view('adduser');

		exit;
	}

	/**
	 * Edit Variables
	*/
	public function edituser()
	{
		if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

		$id = $_GET['id'];

		if(request() == "post")
		{
			$data = $_POST;

			if($data['password'] != $data['password_confirm'])
			{
					$notification = "Error: Password fields does not match";

			}elseif(strlen($data['username']) < 5)
			{
					$notification = "Error: Username cannot be less than 5 characters";

			}elseif(strlen($data['password']) < 5)
			{
					$notification = "Error: Password cannot be less than 5 characters";

			}elseif(!preg_match('#[a-z][0-9]#', $data['password'])) 
			{
					$notification = "Error: Password must be combination of alphanumeric keys";
				
			}elseif(App('App\Entities\User')->getwithUsername($data['username']))
			{
					$notification = "Error: Username already exist!";
			}
			else{

					$data = $_POST;

					App('App\Entities\User')->update($data);

					$User = App('App\Entities\User')->get($id);

					$notification = "User details successfully updated";
			}

			view('edituser',compact('notification','User'));

			exit;
			
		}

		$User = App('App\Entities\User')->get($id);
		view('edituser',compact('User'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Users
	 ***************************************************************************************************************************/
	public function addaffiliateduser()
	{
		if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

		$Affiliates  = App('App\Entities\Affiliate')->lists();
		$affiliateid = $_GET['affiliateid'];

		if(request() == "post")
		{
			$data = $_POST;

			if($data['password'] != $data['password_confirm'])
			{
					$notification = "Error: Password fields does not match";

			}elseif(strlen($data['username']) < 5)
			{
					$notification = "Error: Username cannot be less than 5 characters";

			}elseif(strlen($data['password']) < 5)
			{
					$notification = "Error: Password cannot be less than 5 characters";

			}elseif(!preg_match('#[a-z][0-9]#', $data['password'])) 
			{
					$notification = "Error: Password must be combination of alphanumeric keys";
				
			}elseif(App('App\Entities\User')->getwithUsername($data['username']))
			{
					$notification = "Error: Username already exist!";
			}
			elseif(App('App\Entities\User')->getwithAffiliate($data['affiliateid']))
			{
					$notification = "Error: A manager already exist for this affiliate. Remove to add new one";
			}
			else{

					$data = $_POST;

					App('App\Entities\User')->addnewaffiliate($data);

					$notification = "Affiliate successfully added on platform";
			}

			view('addaffiliateduser',compact('notification','Affiliates','affiliateid'));

			exit;
			
		}

		view('addaffiliateduser',compact('Affiliates','affiliateid'));

		exit;
	}


	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Survey Sector
	 ***************************************************************************************************************************/

	/**
	 * View Variable Lists
	 */
	public function sector()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			App('App\Entities\Survey')->dropsector($id);

			$notification = "Sector successfully deleted";
		} 

		//Sectors lists
		$Sectors = App('App\Entities\Survey')->listSectors();

		view('sectorlists',compact('notification','Sectors'));

		exit;
	}

	/**
	 * Add new sub sector
	 */
	public function addsector()
	{

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Survey')->addnewsector($data);

			$notification = "New sector successfully added";

			view('addsector',compact('notification'));

			exit;
		}

		view('addsector');

		exit;
	}


} 