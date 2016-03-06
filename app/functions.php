<?php

/**
 * Retrive a value from the $_POST 
 * superglobal variable
 *
 */
function get($value)
{
	if(empty($_POST)) return NULL;

	$post = $_POST;

	if(preg_match("#\.#", $value,$matches))
	{
		//Fetch key from array
		$data = explode(".",$value);

		return $post[$data[0]][$data[1]];
	}

	return $post[$value];
}

/**
 * get value from site configuration
*/
function config($key)
{
	$config = include('config.php');

	if(array_key_exists($key, $config)) return $config[$key];

	return NULL;
}

/**
 * Return a view file path
 */
function views($view)
{
	return config('view_path').$view.'.phtml';
	exit;
}

/**
 * Inlude a view path
**/
function frontview($view,$variables = array())
{
	$viewpath = config('view_path');

	// var_dump($variables);
	//Import variables into the current page
	extract($variables);

	include_once( views('frontend/'.$view) );
}

/**
 * Inlude a view path
 **/
function backview($view,$variables = array())
{
	extract($variables);
	ob_flush();
	include_once(views('backend/'.$view));
	ob_clean();
}

/**
 * Inlude a view path
 **/
function errorview($view,$variables = array())
{
	// var_dump($variables);
	//Import variables into the current page
	extract($variables);

	include_once( views('errors/'.$view) );
}

function setupSession($session,$savepath = 'storage/sessions')
{
	ini_set('session.save_handler','files');
	session_set_save_handler($session,true);
	session_save_path(config('base_path').$savepath);
	ini_set('session.gc_probability',1);
}

/**
 * Make
**/
function App($namespace)
{
	$class = $namespace::instance();
	// var_dump($class);
	return $class;
}

/**
 * PDO Error
 */
function getError($stmt)
{
	if(!$stmt)
		{
		    echo "\nPDO::errorInfo():\n";
		    var_dump($stmt->errorInfo());
		}
}

/**
 * Generate url 
*/
function url($fragment)
{
	return config('base_url').$fragment;
}

/**
 * 
 */
function redirectTo($path)
{
	$url = url($path);
	header("Location:{$url}");
	exit;
}

function load_css($file)
{
	echo basename( dirname(__DIR__) ).'/assets/css/'.$file;
}

function load_js($file)
{
	echo basename( dirname(__DIR__) ).'/assets/css/'.$file;
}

function request()
{
	return strtolower($_SERVER['REQUEST_METHOD']);
}

function adminpageis($value)
{
	if(config('url'))
	{
		$page = explode("/",trim(config('url')));

		if(isset($page[1]))
		{
			if($page[1] == $value) return $page[1];
		}
		else{
			return FALSE;
		}
	}else{
		return NULL;
	}
}

function dd($data)
{
	var_dump($data);
	die;
}

function geturl($path)
{
	return 'http://'.config('base_url').$path;
}

// Auth functions
//Hash password to md5
function hashkey($string)
{
    return md5($string);
}

//Get Request input GET/POST/ALL
function input()
{
    if(!$_POST)
    {
        return $_GET;
    }elseif($_POST)
    {
        return $_POST;
    }else{
        return $_REQUEST;
    }
}

/**
 * Login user in or throw login error
 * @param $details
 */
function login($details)
{
	// dd(md5('password'));
	extract($details);
    $hashkey  = hashkey($password);//check

    $url = geturl('login');

    if(!empty($username) || empty($password))
    {
        $db = App('App\DB')->conn();

        $user = App('App\Entities\User')->login($username,$hashkey);
        
        if(!empty($user) && !is_null($user))
        {
            $data = serialize($user);

            //Save details to session
            $site_logged_in = ['logged' => true,'user' => $data,'user_type' => 'admin'];

            session_put('site_logged_in',$site_logged_in);

			$url = geturl('admin/dashboard');
			redirect_to($url,array('as' => 'notification','message' => "Welcome To Anastat Admin Dashboard : ".ucfirst(user('username'))));
        }
        else{

        	$notification = 'Incorrect username or password supplied';

			$url .= "?error={$notification}";

			redirect_to($url,array('as' => 'notification','message' => $notification ));
        }
    }else{

        	$notification = 'Invalid username or password';

			$url .= "?error={$notification}";

			redirect_to($url,array('as' => 'notification','message' => $notification));
    }
}

/**
 * @param $details
 */
function loginaffiliate($details)
{
	$password = "";

	extract($details);

    $hashkey  = hashkey($password);

    $url = geturl('affiliate/login');


    if(!empty($username) || empty($password))
    {
        $db = App('App\DB')->conn();

        $user = App('App\Entities\User')->loginaffiliate($username,$hashkey);

        if(!empty($user) && !is_null($user))
        {
        	$Affiliate  = App('App\Entities\Affiliate')->get($user['affiliate_id']);

//        	dd($Affiliate);
        	
        	$status     = $Affiliate['status'];
        	$balance    = $Affiliate['datasize'];

        	$affiliate = $Affiliate['affiliate_name'];

        	$notification = "Affiliate subscription may have being exhasted. Contact ADSR";

        	if($status == 0 || $balance == 0) 
        	{
        		$url .= "?error={$notification}";

        		return redirect_to($url,array('as' => 'notification','message' => $notification ));
        	}

        	$user['affiliate_name'] = $affiliate;
        	
            $data = serialize($user);

            //Save details to session
            $affiliate_logged_in = ['logged' => true,'user' => $data,'user_type' => 'affiliate'];

            session_put('affiliate_logged_in',$affiliate_logged_in);

			$url = geturl('affiliate/dashboard');

			// $url .= "?error={$notification}";

			redirect_to($url,array('as' => 'message','message' => "Welcome To Anastat Affiliate Manager Dashboard : ".ucfirst(user('username'))));
        }
        else{

        	$notification = 'Incorrect username or password supplied';

        	$url .= "?error={$notification}";

			redirect_to($url,array('as' => 'notification','message' => $notification ));
        }
    }else{
    		$notification = 'Invalid username or password';

    		$url .= "?error={$notification}";

			redirect_to($url,array('as' => 'notification','message' => $notification));
    }
}

function session_flush()
{
	$_SESSION = array();
}

//check if user is logged in
function is_logged_and_is_admin()
{
    if(isset($_SESSION['site_logged_in']) && $_SESSION['site_logged_in']['user_type'] == "admin") return TRUE;

    return FALSE;
}

function is_logged_and_is_affiliate()
{
	if(isset($_SESSION['affiliate_logged_in']) && $_SESSION['affiliate_logged_in']['user_type'] == "affiliate") return TRUE;

    return FALSE;
}

//Get user detail
function user($key)
{
	if(is_logged_and_is_admin()) return unserialize( $_SESSION['site_logged_in']['user'] )[$key];

    return NULL;
}

//Get affiliate detail
function affiliate($key)
{
	if(is_logged_and_is_affiliate()) return unserialize( $_SESSION['affiliate_logged_in']['user'] )[$key];

    return NULL;
}

function session_put($key,$value)
{
	$_SESSION[$key] = $value;
}

function session_get($key)
{
	if(session_has($key)) 
	{
		$val = $_SESSION[$key];
		 unset($_SESSION[$key]);

		return $val;
	}
	
	return NULL;
}

function session_has($key)
{
	if(isset($_SESSION[$key])) return TRUE;
	return FALSE;
}

function logout($url)
{
	//IF AFFILIATE AND ADMIN IS LOGGED ON THE SAME SYSTEM LOG THEM BOTH OUT
	if(is_logged_and_is_admin() && is_logged_and_is_affiliate())
	{
		 $_SESSION['site_logged_in'] = [];
        unset( $_SESSION['site_logged_in'] );

        $_SESSION['affiliate_logged_in'] = [];
        unset( $_SESSION['affiliate_logged_in'] );

        return redirect_to($url);
	}

	//LOGOUT ADMIN
    if(is_logged_and_is_admin())
    {
        $_SESSION['site_logged_in'] = [];
        unset( $_SESSION['site_logged_in'] );

        return redirect_to('/admin/login');
        // return redirect_to('admin/login');
    }

	//LOGOUT AFFILIATE
    if(is_logged_and_is_affiliate())
    {
    	$_SESSION['affiliate_logged_in'] = [];
        unset( $_SESSION['affiliate_logged_in'] );

        return redirect_to('/affiliate/login');
        // return redirect_to('admin/affiliatelogin');
    }
}

function redirect_to($url,$array = array())
{
	if(!empty($array)) session_put($array['as'],$array['message']);

	header("Location:".$url);
}

/**
 * Get role loggedin user belongs to
 */
function getrole($role)
{
	switch ($role) {
		case 1:
			return "super";
			break;
		case 2:
			return "power";
			break;
		case 0:
			return "user";
			break;

		default:
			# code...
			break;
	}
}

/**
 * User with role less than power
 * check
 */
function power()
{
	if(user('role') == 2) return TRUE;
	return FALSE;
}

function super()
{
	if(user('role') == 1) return TRUE;
	return FALSE;
}

function powerabove()
{
	if(user('role') == 1 OR user('role') == 2) return TRUE;
	return FALSE;
}

function geturi()
{
	$uri = ltrim(config('uri'),'/');

	$start = strpos($uri,'?');
	if($start)
	{
		$end = strlen($uri);

		return $uri = substr_replace($uri,"",$start,$end);
	}
	return ($uri);
}

function formatcode($jsonstring)
{
	$json = json_decode($jsonstring,true);

	$code  = $json['database'];
	$code .= " ";
	$code .= implode(',',$json['variables']);
	$code .= " ";
	$code .= $json['table'].$json['level'].$json['frequency'];
	$code .= " ";
	$code .= implode(',',$json['categories']);

	return $code;
}

function restrict_user()
{
	if(!super()) return redirect_to('dashboard',array('as' => 'message','message' => 'You don\'t have permission to perform action.contact super administrator '));
}


function token()
{
	$token = md5(rand(2,9));

	$input = '<input name="token" value="{token}" type="hidden" />';

	str_replace("{token}", $token, $input);

	if(!session_has('token')) $_SESSION["token"] = [];

	if(session_has('token')) $_SESSION["token"][] = $token;

	echo $input;

	dd($_SESSION);
}


function csrf($data)
{
	// dd(func_get_args());
	extract($data);

	$tokens = (session_has('token')) ? $_SESSION['token'] : array();

	if($key = array_search($token,$tokens))
	{
		unset($_SESSION['token'][$key]);
			$report = TRUE;
	}
	else{
			$report =  FALSE;
	}

	dd($report);

}

/**
 * @param $date
 * @param $format
 * @return bool|string
 */
function format_date($date, $format)
{
   $formated_date =  date($format,strtotime($date));

    if($formated_date) return $formated_date;

    return $date;
}