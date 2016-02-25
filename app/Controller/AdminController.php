<?php

namespace App\Controller;
use App\Singleton;

class AdminController{

	use Singleton;

	public function __construct()
	{
		//
	}

	public function dashboard()
	{
		backview('dashboard',compact('Affiliates','Clients','Requests'));
	}

	/**
	 * Download General Report Into ExcelSheet
	 */
	public function getreport()
	{
		return App('App\Report')->GetAllAffiliateReport();
	}

    /**
     *
     */
    public function affiliate($id,$action)
    {
        switch($action)
        {
            case "edit" :
                return $this->editAffiliate($id);
            break;
            case "drop" :
                return $this->dropAffiliate($id);
            break;
            case "report" :
                return $this->reportAffiliate($id);
            break;
            case "view" :
                return $this->viewAffiliate($id);
                break;
            case "user" :
                return $this->viewAffiliateUser($id);
                break;
            case "adduser" :
                return $this->addaffiliateduser($id);
                break;
            case "dropuser" :
                return $this->dropaffiliateduser($id);
                break;
            case "edituser" :
                return $this->editAffiliateuser($id);
                break;
            default:
                $Affiliates = App('App\Entities\Affiliate')->lists();
                backview('affiliate/all',compact('notification','Affiliates'));
                break;
        }

	}

	/**
	 * View affiliate manager
	 */
	private function viewAffiliateUser($id)
	{
		$User = App('App\Entities\User')->getwithAffiliate($id);
		// dd($User);
		backview('affiliate/user',compact('id','User'));
	}

	/**
	 * Delete an affiliated manager
	 */
	private function dropaffiliateduser($affiliateid)
	{
		if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

		//Obtain id of manager
		$User = App('App\Entities\User')->getwithAffiliate($affiliateid);

		extract($User);

		//Delete manager details from storage
		App('App\Entities\User')->drop($id);

		redirect_to("/admin/affiliate/{$affiliateid}/user",array('as' => 'notification','message' => $notification));
	}

	/**
	 * Edit affiliate user from storage
	 * @return view
	 */
	private function editAffiliateuser($id)
	{
		if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

		if(request() == "post")
		{
			$data         = $_POST;
			$notification = "Affiliate manager details successfully updated";

			App('App\Entities\User')->update($data);
			redirect_to("/admin/affiliate/{$id}/user",array('as' => 'notification','message' => $notification));
		}

		$User          = App('App\Entities\User')->getwithAffiliate($id);
		$Affiliate     = App('App\Entities\Affiliate')->edit($id);
		$AffiliateName = $Affiliate['affiliate_name'];

		backview('affiliate/edituser',compact('id','AffiliateName','User'));
	}

	/**
	 * Add a new affiliated manager
	 */
	private function addaffiliateduser($id)
	{
		if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

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
			elseif(App('App\Entities\User')->getwithAffiliate($id))
			{
					$notification = "Error: A manager already exist for this affiliate. Remove to add new one";
			}
			else{

					$data = $_POST;

					App('App\Entities\User')->addnewaffiliate($data);

					$notification = "Affiliate manager successfully added on platform";

					redirect_to("/admin/affiliate/{$id}/user",array('as' => 'notification','message' => $notification));

			}

			
		}

		$Affiliate     = App('App\Entities\Affiliate')->edit($id);
		$AffiliateName = $Affiliate['affiliate_name'];

		backview('affiliate/adduser',compact('notification','affiliateid','id','AffiliateName'));
	}

    /**
     * @param $id
     */
    private function viewAffiliate($id)
    {
        if(request() == 'post')
        {
            $affiliate = "";
            $plan = "";

            $data = $_POST;
            extract($data);

            App('App\Entities\Subscription')->subscribe($affiliate,$plan);
        }

        $Affiliate = App('App\Entities\Affiliate')->edit($id);
        $History   = App('App\Entities\Subscription')->listByAffiliate($id);
        $Active    = App('App\Entities\Subscription')->listActiveByAffiliate($id)[0];

        //Lazy-load subscriptions list
        if(empty($Active)) $Subscriptions = App('App\Entities\Subscription')->lists();

        backview('affiliate/view',compact('Affiliate','History','Active','Subscriptions'));
    }

    /**
     * Delete an affiliate details from storage
     * @param $id
     */
    private function dropAffiliate($id)
    {
            //Do not allow ordinary users to delete
            if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

            App('App\Entities\Affiliate')->drop($id);

            $notification = "Affiliate successfully removed and related clients including requests";

            redirect_to("/admin/affiliate",array('as' => 'notification','message' => $notification));
    }

    /**
     * Download report for an affiliate
     * @param $id
     * @return mixed
     */
    private function reportAffiliate($id)
    {
        $id = $_GET['id'];
        return App('App\Report')->GetAffiliateReport($id);
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

            redirect_to("/admin/affiliate",array('as' => 'notification','message' => $notification));
        }

        $Subscriptions = App('App\Entities\Subscription')->lists();
		backview('affiliate/add',compact('Subscriptions'));
	}


    /**
     * Edit an affiliate
     */
    public function editAffiliate($id)
	{
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Affiliate')->update($data);

			$notification = "Affiliate details successfully updated";

			$Affiliate = App('App\Entities\Affiliate')->edit($id);

            redirect_to("/admin/affiliate",array('as' => 'notification','message' => $notification));
		}

		$Affiliate = App('App\Entities\Affiliate')->edit($id);
        $Active    = App('App\Entities\Subscription')->listActiveByAffiliate($id)[0];
        $Subscriptions = App('App\Entities\Subscription')->lists();


        backview('affiliate/edit',compact('Affiliate','Active','Subscriptions'));
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Tables
	 ***************************************************************************************************************************/

	/**
	 * View Table Lists
	 */
	public function tables($id,$action)
	{
		switch($action){
            case  'edit':
                return $this->editTable($id);
            break;
            case  'drop':
                return $this->dropTable($id);
            break;
            case 'variables':
            	return $this->tableVariables($id);
            	break;
            case 'a_variables':
            	return $this->assignVariable($id);
            	break;
            case 'r_variables':
            	return $this->reassignVariables($id);
            	break;
            case 'drop_variables':
            	return $this->dropVariables($id);
            	break;
            default:
            $Tables = App('App\Entities\Table')->lists($id);
			backview('table/all',compact('notification','Tables'));
			break;
        }
	}

	/**
	 *  @param id
	*/
	private function dropTable($id)
	{
			//Do not allow ordinary users to delete		
			if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

			App('App\Entities\Table')->drop($id);

			$notification = "Table and associated data successfully removed";

            redirect_to("/admin/tables",array('as' => 'notification','message' => $notification));
	}

	/**
	 * Add new tables into storage
	 */
	public function addtables()
	{
		$Databases = App('App\Entities\Database')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Table')->addnew($data);

			$notification = "Table successfully added";

            redirect_to("/admin/tables",array('as' => 'notification','message' => $notification));
		}

		backview('table/add',compact('Databases'));
	}


	/**
	 * Add new database into storage
	*/
	private function editTable($id)
	{
		$id = intval($id);

		//Database lists
		$Databases = App('App\Entities\Database')->lists();
		
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Table')->update($data);

			$notification = "Table details successfully updated";

            redirect_to("/admin/tables",array('as' => 'notification','message' => $notification));
		}


		$Table = App('App\Entities\Table')->edit($id);

		backview('table/edit',compact('Table','Databases'));
	}

	/**
	 * List table variables
	 */
	private function tableVariables($id)
	{
		$Database_id = base64_decode($_GET['dt']);

		if(!empty($Database_id)) $Database = App('App\Entities\Database')->edit($Database_id);

		$Variables = App('App\Entities\Variable')->listByTable($id);
		$Tableid   = $id;
		
		backview('table/variable',compact('Variables','Database','Tableid'));
	}


	/**
	 * Form to assign variables for a table
	*/
	public function assignVariable($id)
	{
		if(request() == "post")
		{
			$datas     = $_POST;
			$table     = $datas['table'];
			$level     = $datas['level'];
			$frequency = $datas['frequency'];

		/**
		 * Prevent from adding a new generic match if one matching table already exist
		 * @param $table int
		 * @param $level int
		 * @param $frequency int
		 */
		if(App('App\Entities\Variable')->genericMatch($table,$level,$frequency))
		{
			$notification = 'Generic table already exist, kindly edit to add more
			 				 or combine another unique table,level,frequency
			 				 to assign variables';

			redirect_to("/admin/tables/{$id}/variables",array('as' => 'notification','message' => $notification));
		}

			App('App\Entities\Variable')->parse($datas);

			redirect_to("/admin/tables/{$id}/variables");
		}

		$Levels 	 = App('App\Entities\Laggregation')->TableAggregations($id);		//Retrieve aggregation available for this table
		$Frequencies = App('App\Entities\Frequency')->getTableFrequencies($id);			//Retrieve frequencies available for this table
		$Variables   = App('App\Entities\Variable')->lists();
		$Table       = App('App\Entities\Table')->edit($id);

		backview('table/assignvariable',compact('Variables','Frequencies','Levels','Table','Database'));
	}


	/**
	 * Form to assign tables for a database
	*/
	public function reassignVariables($id)
	{

		$Tableid      = base64_decode($_GET['table']);
		$Levelid      = base64_decode($_GET['level']);
		$Freqid       = base64_decode($_GET['freq']);
		$GenericTable = base64_decode($_GET['generic']);

		if(request() == "post")
		{
			$datas = $_POST;
			App('App\Entities\Variable')->sync($datas);

			redirect_to("/admin/tables/{$id}/variables");
		}

		if(!empty($Tableid) && !empty($Levelid) && !empty($Freqid) && !empty($GenericTable))
		{
			$Results 	 = App('App\Entities\Variable')->GenericVariable($Tableid,$Levelid,$Freqid);

			$Levels 	 = App('App\Entities\Laggregation')->TableAggregations($Tableid);					//Retrieve aggregation available for this table
			$Frequencies = App('App\Entities\Frequency')->getTableFrequencies($Tableid);					//Retrieve frequencies available for this table
			$Variables   = App('App\Entities\Variable')->lists();
			$Table       = App('App\Entities\Table')->edit($Tableid);

			backview('table/reassignvariable',compact('Levels','Frequencies','Levelid','Frequencyid','Variables','Table','GenericTable','Results'));
		}

		redirect_to("/admin/tables/{$id}/variables");
	}


	/**
	 * Form to assign tables for a database
	*/
	public function dropVariables($id)
	{

		$Tableid      = base64_decode($_GET['table']);
		$Levelid      = base64_decode($_GET['level']);
		$Freqid       = base64_decode($_GET['freq']);

		if(!empty($Tableid) && !empty($Levelid) && !empty($Freqid))
		{

			App('App\Entities\Variable')->detach($Tableid,$Levelid,$Freqid);
			
			redirect_to("/admin/tables/{$id}/variables",array('as' => 'notification','message' => 'Generic Table successfully deleted!'));
		}

		redirect_to("/admin/tables/{$id}/variables",array('as' => 'notification','message' => 'Could Not Delete Generic Table! Try Again'));
	}


	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Level Aggregation
	 ***************************************************************************************************************************/

	/**
	 * View Aggregation Lists
	 */
	public function levelaggregation($id,$action)
	{
        switch($action){
            case  'edit':
                return $this->editLevel($id);
                break;
            case  'drop':
                return $this->dropLevel($id);
                break;
            default:
                $Results      = App('App\Entities\Laggregation')->all(true);
                $Aggregations = !empty($Results) ? $Results->results() : array();
                $Links        = !empty($Results) ? $Results->links() : "";

                backview('level/all',compact('notification','Aggregations','Links'));
                break;
        }
	}

    /**
     * Edit Level
     * @param $id
     */
    private function editLevel($id)
    {

        $Tables = App('App\Entities\Table')->lists();

        if(request() == "post")
        {
            $data = $_POST;

            App('App\Entities\Laggregation')->update($data);

            $notification = "Aggregation details successfully updated";

            $Aggregation = App('App\Entities\Laggregation')->edit($id);

            redirect_to("/admin/levelaggregation",array('as' => 'notification','message' => $notification));
        }

        $Aggregation = App('App\Entities\Laggregation')->edit($id);

        backview('level/edit',compact('Tables','Aggregation'));
    }

    /**
     * @param $id
     */
    private function dropLevel($id)
    {
        //Do not allow ordinary users to delete
        if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

        App('App\Entities\Laggregation')->drop($id);

        $notification = "Level of aggregation and associated data successfully removed";

        redirect_to("/admin/levelaggregation",array('as' => 'notification','message' => 'Plan successfully deleted!'));
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

			backview('addaggregations',compact('notification','Tables'));

			exit;
		}

		backview('level/add',compact('Tables'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Category Aggregation
	 ***************************************************************************************************************************/

	/**
	 * View Category Aggregation Lists
	 */
	public function categoryaggregation($id,$action)
	{
        switch($action){
            case  'edit':
                return $this->editCategory($id);
                break;
            case  'drop':
                return $this->dropCategory($id);
                break;
            default:
                $Results      = App('App\Entities\Caggregation')->lists(true);
                $Categories   = !empty($Results) ? $Results->results() : array();
                $Links        = !empty($Results) ? $Results->links() : "";

                backview('category/all',compact('notification','Categories','Links'));
                break;
        }
	}

    /**
     * Drop category
     * @param $id
     */
    private function dropCategory($id)
    {
        //Do not allow ordinary users to delete
        if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

        App('App\Entities\Caggregation')->drop($id);

        $notification = "Category of aggregation and associated data successfully removed";

        redirect_to("/admin/categoryaggregation",array('as' => 'notification','message' => $notification));
    }

	/**
	 * Add new tcategory to storage
	 */
	public function addcategory()
	{

		//Level lists
		$Levels = App('App\Entities\Laggregation')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Caggregation')->addnew($data);

			$notification = "Categories successfully added";

            redirect_to("/admin/categoryaggregation",array('as' => 'notification','message' => $notification));
        }

		backview('category/add',compact('Levels'));
	}

	/**
	 * Edit Category Aggregation
	*/
	private function editCategory($id)
	{
		//Levels lists
		$Levels = App('App\Entities\Laggregation')->lists();
		
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Caggregation')->update($data);

			$notification = "Category details successfully updated";

			$Category = App('App\Entities\Caggregation')->edit($id);

            redirect_to("/admin/categoryaggregation",array('as' => 'notification','message' => $notification));
		}

		$Category = App('App\Entities\Caggregation')->edit($id);

		backview('category/edit',compact('Levels','Category'));
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Variables
	 ***************************************************************************************************************************/

	/**
	 * View Variable Lists
	 */
	public function variables($id,$action)
	{
		 switch($action){
            case  'edit':
                return $this->editVariable($id);
                break;
            case  'drop':
                return $this->dropVariable($id);
                break;
            default:
				$Results   = App('App\Entities\Variable')->lists(true);
				$Variables = !empty($Results) ? $Results->results() : array();
				$Links     = !empty($Results) ? $Results->links() : "";

				backview('variable/all',compact('notification','Variables','Links'));
                break;
        }
	}

	private function dropVariable($id)
	{
		//Do not allow ordinary users to delete		
		if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

		App('App\Entities\Variable')->drop($id);

		$notification = "Variable successfully removed";

        redirect_to("/admin/variables",array('as' => 'notification','message' => $notification));
	}

	/**
	 * Add new variable to storage
	 */
	public function addvariables()
	{

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Variable')->addnew($data);

			$notification = "Variable successfully added";

        	redirect_to("/admin/variables",array('as' => 'notification','message' => $notification));
		}

		backview('variable/add');

		exit;
	}


	/**
	 * Edit Variables
	*/
	public function editvariable($id)
	{
		$Tables = App('App\Entities\Table')->lists();

		$Aggregations = App('App\Entities\Laggregation')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Variable')->update($data);

			$Variable = App('App\Entities\Variable')->edit($id);

			$notification = "Variable details successfully updated";

        	redirect_to("/admin/variables",array('as' => 'notification','message' => $notification));
		}

		$Variable = App('App\Entities\Variable')->edit($id);

		backview('variable/edit',compact('Variable','Tables','Aggregations'));
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
			//Do not allow ordinary users to delete		
			if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

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

		backview('surveylists',compact('notification','Surveys'));

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
			
			backview('addsurvey',compact('notification','Sectors'));

			exit;
		}

		backview('addsurvey',compact('Sectors'));

		exit;
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Frequencies
	 ***************************************************************************************************************************/

	/**
	 * View Frequency Lists
	 */
	public function frequency($id,$action)
	{
        switch($action){
            case  'edit':
                return $this->editFreq($id);
                break;
            case  'drop':
                return $this->dropFreq($id);
                break;
            default:
                //Frequency lists
                $Results     = App('App\Entities\Frequency')->lists(true);
                $Frequencies = !empty($Results) ? $Results->results() : array();
                $Links       = !empty($Results) ? $Results->links() : "";

                backview('frequency/all',compact('notification','Frequencies','Links'));
                break;
        }
    }

    private function dropFreq($id)
    {
        //Do not allow ordinary users to delete
        if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

        App('App\Entities\Frequency')->drop($id);

        $notification = "Frequency successfully removed";

        redirect_to("/admin/frequency",array('as' => 'notification','message' => $notification));
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

            redirect_to("/admin/frequency",array('as' => 'notification','message' => $notification));
        }

		backview('frequency/add',compact('Tables','notification'));
	}

	/**
	 * Edit Variables
	*/
	private function editFreq($id)
	{
		$Tables    = App('App\Entities\Table')->lists();

		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Frequency')->update($data);

			$notification = "Frequency details successfully updated";

            redirect_to("/admin/frequency",array('as' => 'notification','message' => $notification));
		}

		$Frequency = App('App\Entities\Frequency')->edit($id);

		backview('frequency/edit',compact('Tables','Frequency'));
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
			//Do not allow ordinary users to delete		
			if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

			$id = $_GET['id'];
			App('App\Entities\Period')->drop($id);

			$notification = "Period successfully removed";
		} 

		//Frequency lists
		$Periods = App('App\Entities\Period')->lists();

		backview('periodlists',compact('notification','Periods'));

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

			backview('addperiod',compact('notification','Frequencies'));

			exit;
		}

		backview('addperiod',compact('Frequencies'));

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

			backview('editperiod',compact('notification','Frequencies','Period'));

			exit;
		}

		$Period = App('App\Entities\Period')->edit($id);

		backview('editperiod',compact('Frequencies','Period'));

		exit;
	}


	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Databases
	 ***************************************************************************************************************************/

	public function database($id,$action)
	{
        switch($action)
        {
            case "drop":
                return $this->dropDatabase($id);
                break;
            case "edit":
                return $this->editDatabase($id);
                break;
            case "tables":
                return $this->databaseTables($id);
                break;
            case "a_tables":
                return $this->assignTables($id);
                break;
            case "r_tables":
                return $this->reassignTables($id);
                break;
            default:
                $Databases = App('App\Entities\Database')->lists();
                backview('database/all',compact('notification','Databases'));
                break;
        }
	}

    /**
     * List database tables
     */
    public function databaseTables($id)
    {

        if(request() == "post")
        {
            $data = $_POST;
            App('App\Entities\Table')->assign($data);

            redirect_to("/admin/database/{$id}/tables");
        }

        $Database    = App('App\Entities\Database')->edit($id);

        //Table lists
        $Tables = App('App\Entities\Table')->listByTable($id);

        backview('database/tables',compact('Tables','Database'));
    }

     /**
     * Form to assign tables for a database
     */
    private function assignTables($id)
    {
        $Database    = App('App\Entities\Database')->edit($id);
        $Tables 	 = App('App\Entities\Table')->lists();

        if(request() == "post")
		{
			$datas = $_POST;
			App('App\Entities\Table')->assign($datas);

			redirect_to("/admin/database/{$id}/tables",array('as' => 'notification','message' => $notification));
		}

        backview('database/assigntable',compact('name','Database','Tables'));
    }

    /**
	 * Form to assign tables for a database
	*/
	private function reassignTables($id)
	{
		if(request() == "post")
		{
			$datas = $_POST;
			App('App\Entities\Table')->sync($datas);

			redirect_to("/admin/database/{$id}/tables",array('as' => 'notification','message' => $notification));
		}

		$Database    = App('App\Entities\Database')->edit($id);
		$Tables 	 = App('App\Entities\Table')->lists();
		$Assigned 	 = App('App\Entities\Table')->pivot($id);

		backview('database/reassigntable',compact('Database','Tables','Assigned'));
	}

    /**
     * Delete a database feom storage
     * @param $id
     */
    private function dropDatabase($id)
    {
        //Do not allow ordinary users to delete
        if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

        App('App\Entities\Database')->drop($id);

        $notification = "Database successfully Removed";

        redirect_to("/admin/database",array('as' => 'notification','message' => $notification));
    }

	/**
	 * Assign frequency to a table
	 */
	function assignfrequency($id)
	{

		if(request() == "post")
		{
			$data = $_POST;
			App('App\Entities\Frequency')->sync($data);

			$notification = "Assignment successfully completed";
		}

		$Assigned    = App('App\Entities\Frequency')->gettableFrequency($id);
		$Table       = App('App\Entities\Table')->edit($id);
		$Frequencies = App('App\Entities\Frequency')->lists();
		// dd($Frequencies);

		$name        = $Table['table_name'];
		$table_id    = $id;

		backview('assignfrequency',compact('notification','name','table_id','Frequencies','Assigned'));
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

            redirect_to("/admin/database",array('as' => 'notification','message' => $notification));
		}

		backview('database/add');

		exit;
	}

	/**
	 * Add new database into storage
	 */
	private function editDatabase($id)
	{
        $data = $_POST;

        App('App\Entities\Database')->update($data);

        $notification = "Database details successfully updated";

        $Database = App('App\Entities\Database')->edit($id);

        redirect_to("/admin/database",array('as' => 'notification','message' => $notification));
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Client
	 ***************************************************************************************************************************/

	public function clients()
	{
		if(isset($_GET['action']) && $_GET['action'] == 'drop' && !empty($_GET['id']))
		{
			//Do not allow ordinary users to delete		
			if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

			$id = $_GET['id'];
			App('App\Entities\Client')->drop($id);

			$notification = "Client and all requests successfully removed";
		}

		if(isset($_GET['action']) && $_GET['action'] == 'view' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			$Client = App('App\Entities\Client')->get($id);

			backview('client',compact('notification','Client'));

			exit;
		}

		if(isset($_GET['action']) && $_GET['action'] == 'requests' && !empty($_GET['id']))
		{
			$id = $_GET['id'];

			$Requests = App('App\Entities\Request')->listsByClient($id);
			// dd($Requests);
		
			backview('requests',compact('notification','Requests'));

			exit;
		}

		if(isset($_GET['for']) && $_GET['for'] == 'affiliate' && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			$Clients = App('App\Entities\Client')->listByAffiliate($id);

			backview('clients',compact('notification','Clients'));
		}

		//Clients lists
		$Clients = App('App\Entities\Client')->lists();

		backview('clients',compact('notification','Clients'));

		exit;
	}


	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Requests
	 ***************************************************************************************************************************/

	public function requests($id,$action)
	{
		switch($action){
            case  'view':
                return $this->viewRequest($id);
           	 	break;
            case  'drop':
                return $this->dropRequest($id);
                break;
            case 'affiliate':
            	return $this->affiliateRequest($id);
            	break;
            case 'updatestatus':
            	return $this->updateRequestStatus($id);
            	break;
            case 'sort':
            	return $this->sortRequest($id);
            default:
            	if(!empty($_GET['for'])) return $this->sortRequest();
	            $Affiliates = App('App\Entities\Affiliate')->lists();

				//Requests lists paginated
				$Results  = App('App\Entities\Request')->lists(true);
				$Requests = !empty($Results) ? $Results->results() : array();
				$Links    = !empty($Results) ? $Results->links() : "";

				backview('request/all',compact('Affiliates','notification','Requests','Links'));
            	break;
        }

	}

	private function sortRequest()
	{
		//Paginate result here
		$affiliate = $_GET['affiliate'];
		$type      = $_GET['type'];
		$status    = $_GET['status'];
		$approval  = $_GET['approved'];

		$Results  = App('App\Entities\Request')->sort($affiliate,$type,$status,$approval);
		$Requests = !empty($Results) ? $Results->results() : array();
		$append   =  'for=sort&affiliate='.$affiliate.'&type='.$type.'&status='.$status.'&approved='.$approval;
		$Links    = !empty($Results) ? $Results->links($append) : "";

		// dd($Requests);

		$notification = "Sorted Data Returned!";

		backview('request/all',compact('Affiliates','notification','Requests','Links'));
	}

	/**
	 * Change request delivery status 
	 */
	private function updateRequestStatus($id)
	{
		$id     = $_GET['id'];
		$status = $_GET['status'];

		App('App\Entities\Request')->updateStatus($id,$status);

		$notification = "Request status successfully updated";

		$Request = App('App\Entities\Request')->get($id);

		backview('viewrequest',compact('Affiliates','notification','Request'));
	}

	/**
	 * List request for an affiliate
	 */
	private function affiliateRequest($id)
	{
		$Affiliates = App('App\Entities\Affiliate')->lists();

		//Requests lists paginated
		$Results  = App('App\Entities\Request')->listByAffiliate($id);
		$Requests = !empty($Results) ? $Results->results() : array();
		$Links    = !empty($Results) ? $Results->links() : "";
		$affiliateview = $id;

		backview('request/all',compact('Affiliates','notification','Requests','Links','affiliateview'));
	}

	/**
	 * Delete a request from storage
	 */
	private function dropRequest($id)
	{
		$notification = 'You don\'t have permission to perform action.contact super administrator ';

		//Do not allow ordinary users to delete		
		if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => $notification));

		App('App\Entities\Request')->drop($id);

		$notification = "Requests for this transaction successfully deleted";

		redirect_to('/admin/requests',array('as' => 'notification','message' => $notification));
	}

	/**
	 * Display a request information for futher actions
	 */
	private function viewRequest($id)
	{
		if(request() == "post")
		{
			$data = $_POST;

			if(!empty($data['action']) && $data['action'] == 'updatestatus')
			{
				App('App\Entities\Request')->updateStatus($data['transactionid'],$data['approval']);

				$notification = 'Transaction status successfully updated!';
			}else{

				//Bill this transaction if
				//it has not yet being billed
				if($data['billed'] == 0)
				{
					/**
					 * Check if client is affiliated to check 
					 * if billing of data can be performed
					 */
					if(!empty($data['affiliateid']))
					{
						$balance = App('App\Entities\Request')->getBalance($data['affiliateid']);
						
						//Integer value of affiliate account balance
						$balance = intval($balance);
						
						$charge  = intval($data['datasize']);

						$notification = "Cannot charge {$charge}(kb) from {$balance}(kb).Affiliate account balance is low, kindly reduce charge to exact data balance or less";

						if($charge > $balance) return redirect_to("/admin/requests/{$id}/view",array('as' => 'notification','message' => $notification));

					}
				}
				
					App('App\Entities\Request')->processTransaction($data);

					$notification = 'Transaction successfully proccessed on client!';

				}
			
		}

		$Requests = App('App\Entities\Request')->getByTransaction($id);
		$Client   = $Requests[0];

		// dd($Client);
		backview('request/view',compact('Client','Requests','notification'));
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Users
	 ***************************************************************************************************************************/
	/**
	 * View Users Lists
	 */
	public function users($id,$action)
	{
		 switch($action){
            case  'edit':
                return $this->editUser($id);
            break;
            case  'drop':
                return $this->dropUser($id);
            break;
            default:
	            if(!powerabove()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

				//Users lists
				$Users = App('App\Entities\User')->lists();

				backview('user/all',compact('notification','Users'));
            break;
        }
	}

	/**
	 * Delete a user details from storage
	 */
	private function dropUser($id)
	{
		//Do not allow ordinary users to delete		
		if(!super()) return redirect_to('/dashboard',array('as' => 'notification','message' => 'You don\'t have permission to perform action.contact super administrator '));

		if(!super()) return redirect_to('/dashboard',array('as' => 'notification','message' => 'You don\'t have permission to perform action.contact super administrator '));
			
		if($id == user('id')) return redirect_to('/dashboard',array('as' => 'notification','message' => 'You cannot delete logged in user.Logout first'));

		App('App\Entities\User')->drop($id);

		$notification = "User successfully deleted from platform";

		redirect_to('/admin/users',array('as' => 'notification','message' => $notification));
	}

	/**
	 * Add new user to storage
	 */
	public function adduser()
	{
		if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

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

			backview('user/add',compact('notification'));
		}

		backview('user/add',compact('notification'));
	}

	/**
	 * Edit Variables
	*/
	private function editUser($id)
	{
		if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

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

					$notification = "User details successfully updated";

					redirect_to('/admin/users',array('as' => 'notification','message' => $notification));
			}

			backview('user/edit',compact('notification','User'));
		}

		$User = App('App\Entities\User')->get($id);

		backview('user/edit',compact('User'));
	}

	/**--------------------------------------------------------------------------------------------------------------------------/
	 * Manage Users
	 ***************************************************************************************************************************/
	


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
			//Do not allow ordinary users to delete		
			if(!super()) return redirect_to('/dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));

			$id = $_GET['id'];
			App('App\Entities\Survey')->dropsector($id);

			$notification = "Sector successfully deleted";
		} 

		//Sectors lists
		$Sectors = App('App\Entities\Survey')->listSectors();

		backview('sectorlists',compact('notification','Sectors'));

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

			backview('addsector',compact('notification'));

		}

		backview('addsector');

	}

	/**
	 * @return null
	 */
	public function subscriptions()
	{
		$Subscriptions = App('App\Entities\Subscription')->lists();

		backview('subscriptions/all',compact('Subscriptions'));
	}

	/**
	 * Add new subscription into storage
     */
	public function addsubscription()
	{
		if(request() == 'post')
		{
			$data = $_POST;
			App('App\Entities\Subscription')->addnew($data);

            $notification = "New subscription plan successfully added";

            redirect_to("/admin/subscriptions",array('as' => 'notification','message' => $notification));
        }

		backview('subscriptions/add',compact('notification'));
	}

    /**
     * @param $id
     * @param $action
     */
    public function subscription($id, $action)
    {
        switch($action){
            case  'edit':
                return $this->editPlan($id);
            break;
            case  'drop':
                return $this->dropPlan($id);
            break;
        }
    }

    /**
     * @param $id
     */
    private function editPlan($id)
    {
        if(request() == "post")
        {
            $data = $_POST;
            App('App\Entities\Subscription')->update($data);
            $notification = "Plan details successfully updated";
        }
        $Plan = App('App\Entities\Subscription')->edit($id);

        backview('subscriptions/edit',compact('notification','Plan'));
    }

    /**
     * Delete a plan from storage
     * @param $id
     */
    private function dropPlan($id)
    {
        App('App\Entities\Subscription')->drop($id);
        redirect_to("/admin/subscriptions",array('as' => 'notification','message' => 'Plan successfully deleted!'));
    }


    /**
     * Manage terms & condition
     */
    public function condition()
    {
    	if(request() == "post")
    	{
    		$data = $_POST;
    		App('App\Entities\Condition')->update($data);
    		$notification = "Terms &amp; conditions successfully updated";
    	}

    	$term = App('App\Entities\Condition')->get();

    	backview('term&condition/term',compact('notification','term'));
    }

} 