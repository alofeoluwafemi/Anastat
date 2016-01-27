<?php
use App\Entities\Database;

$requested_page = trim( config('url') );

/**
 * If no page is request show index else
 * Call method from controller
 * Pass arguements if any
 * Load Page Or perform action dpending on request type
 */
if(empty($requested_page))
{
	$Micros = App('App\Entities\Database')->allmicros();
	$Macros = App('App\Entities\Database')->allmacros();
	$Instituitions = App('App\Entities\Affiliate')->lists();


	if(request() == "post")
	{
		$data = $_POST;

		App('App\Entities\Client')->addclient($data);

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

	}

	view('index',compact('Micros','Macros','Instituitions','notification'));

}else{

	$Controller = new App\Controller();

	if(preg_match("#\/#", $requested_page))
	{
		$data = explode("/",$requested_page);

		$method = array_shift($data);

		$param = $data;
	}
	else {

		$method = $requested_page;

		$param = array();
	}

	call_user_func_array(array($Controller, $method), $param);
}