<?php

namespace App;
use App\Singleton;

class AffiliateController{

	use Singleton;

	public function __construct()
	{
		//
	}

	public function dashboard()
	{
		$affiliateid = affiliate('affiliate_id');
		$Requests = App('App\Entities\Request')->listByAffiliate($affiliateid);
		// dd($Requests);

		view('affiliatedashboard',compact('Requests'));

		exit;
	}

	public function action()
	{
		if(!empty($_GET['for']) && ($_GET['for'] == 'approve') && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			// dd('approve');

			$notification = "Request status successfully changed to approved";

			App('App\Entities\Request')->approve($id);
		} 

		if(!empty($_GET['for']) && ($_GET['for'] == 'disapprove') && !empty($_GET['id']))
		{
			$id = $_GET['id'];
			// dd('disapprove');

			$notification = "Request status successfully changed to disapproved";
			App('App\Entities\Request')->disapprove($id);
		}

		redirect_to('dashboard',array('as' => 'affiliateupdate','message' => $notification));
	}


} 