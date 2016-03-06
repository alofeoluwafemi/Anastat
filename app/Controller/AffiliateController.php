<?php

namespace App\Controller;
use App\Singleton;

class AffiliateController{

	use Singleton;

	public function __construct()
	{
		//
	}

	/**
	 * List Institution Request On Affiliate Dashboard
     */
	public function dashboard()
	{
		$affiliateid = affiliate('affiliate_id');
	
		//Requests lists paginated
		$Results       = App('App\Entities\Request')->listByAffiliate($affiliateid);
		$Requests      = !empty($Results) ? $Results->results() : array();
		$Links         = !empty($Results) ? $Results->links() : "";

//        dd($Requests);

		$environment   = 'affiliate';
		$notification  = empty($Requests) ? "Request lists empty! No request made yet" : "";
		
		backview('affiliate/manager/dashboard',compact('Requests','Links','environment','notification'));
	}

	/**
	 * View a client request details
	 */
	public function view($id)
	{
		if(request() == "post")
		{
			$data = $_POST;

			App('App\Entities\Request')->updateTransaction($data);

            $notification = "Transaction updated!";

			redirect_to('/affiliate/dashboard',array('as' => 'notification','message' => $notification));
		}

		$Requests = App('App\Entities\Request')->getByTransaction($id);
		$Client   = ($Requests[0]) ? $Requests[0] : array();

		// dd($Client);

		$environment   = 'affiliate';

		backview('affiliate/manager/view',compact('Requests','environment','Client'));
	}


} 