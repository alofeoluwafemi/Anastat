<?php
namespace App\Authentication;

use App\Entities\User;
use App\SecureSessionHandler as SecureSessionHandler;
/**
 * Class Auth
 * handles the user's login, registration and logout process
 */
class Auth
{

    public $userRepo;

    /**
     * @var array Collection of error messages
     */
    public $errors = array();
    /**
     * @var array Collection of success / neutral messages
     */
    public $messages = array();

    /**
     * the function "__construct()" automatically starts whenever an object of this class is created,
     * you know, when you do "$auth = new Auth();"
     */
    public function __construct(User $userRepo)
    {

        $this->userRepo = $userRepo;
       
        // create/read session, absolutely necessary
        $this->session = new SecureSessionHandler('adsr');

         
        if ( ! $this->session->isValid(60)) {
            $this->session->forget();
        }
        
        setupSession($this->session);
         
         
        $this->session->start();

    }

    /**
     * log in with post data
     */
    public function loginUser($data = array())
    {

        if (! isset($data['username']) || is_null($data['username']) || $data['username'] == '') 
        {
            $this->errors[] = "Username field was empty.";
        } 
        elseif (! isset($data['password'])  || is_null($data['password']) || $data['password'] == '') 
        {
            $this->errors[] = "Password field was empty.";
        } 
        elseif (isset($data['username']) && isset($data['password']))
        {
            try
            {
                $user = $this->userRepo->getUserByUsername($data['username'], true);

                $passwordHasher = new PasswordHasher;

                if ($passwordHasher->check($data['password'], $user['password']) == true) 
                {
                    
                    $this->session->put('username', $user['username']);
                    $this->session->put('name', $user['name']);
                    $this->session->put('activated', $user['activated']);
                    $this->session->put('user_login_status', 1);
                    $this->session->put('error', '');
                    $this->session->put('errors', array());                    
                    $this->session->put('message', '');
                    $this->session->put('messages', array());
                    $this->session->put('video_does_not_exist', 0);
                    $this->session->put('song_does_not_exist', 0);
                    $this->session->put('user_does_not_exist', 0);
                    $this->session->put('crbt_does_not_exist', 0);

                    //check if user is not activated
                    if ($user['activated'] == false )
                    {

                        $this->session->forget();

                        $this->errors[] = "You have not been activated, ask an Admin to Activate you.";
                        // exit();
                        // 
                        return false;
        
                    }
                    

                } 
                else 
                {
                    $this->errors[] = "Wrong password. Try again.";
                }

            }
            catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e)
            {
                $this->errors[] = "This user does not exist.";
            }

        } 
        else 
        {
            $this->errors[] = "There was a Problem Logging in.";
        }



    }

    /**
     * perform the logout
     */
    public function logoutUser()
    {
        try
        {
            $this->userRepo->updateLastLoginTime($this->session->get('username'));
           
            // delete the session of the user
            // $this->session->destroy(session_id());
            $this->session->forget();
            session_destroy();

            $this->messages[] = "You have been logged out.";
        }
        catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e)
        {
            $this->errors[] = "This user does not exist.";
        }
    }

    /**
     * simply return the current state of the user's login
     * @return boolean user's login status
     */
    public function isUserLoggedIn()
    {
        if ( $this->session->get('user_login_status') == 1) 
        {
            return true;
        }

        return false;
    }


     /**
     * handles the entire registration process. checks all error possibilities
     * and creates a new user in the database if everything is fine
     */
    public function registerNewUser($data = array())
    {

        if (! isset($data['name']) || is_null($data['name'])) 
        {
            $this->errors['name'] = "Empty Full name";
        } 
        if (! isset($data['username']) || is_null($data['username'])) 
        {
            $this->errors['username'] = "Empty Username";
        } 
        elseif (! isset($data['password']) || ! isset($data['password_confirm']) || is_null($data['password_confirm']) || is_null($data['password'])) 
        {
            $this->errors['password'] = "Empty Password";
        } 
        elseif ($data['password'] !== $data['password_confirm']) 
        {
            $this->errors['password_confirm'] = "Password and password confirmation are not the same";
        } 
        elseif (strlen($data['password']) < 6) 
        {
            $this->errors['password'] = "Password has a minimum length of 6 characters";
        } 
        elseif (strlen($data['username']) > 64 || strlen($data['username']) < 2) 
        {
            $this->errors['username'] = "Username cannot be shorter than 2 or longer than 64 characters";
        } 
        elseif (!preg_match('/^[a-z\d]{2,64}$/i', $data['username'])) 
        {
            $this->errors['username'] = "Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters";
        } 
        elseif (!empty($data['name']) && !empty($data['username']) && strlen($data['username']) <= 64 && strlen($data['username']) >= 2
                && preg_match('/^[a-z\d]{2,64}$/i', $data['username']) && !empty($data['password']) && !empty($data['password_confirm'])
                && ($data['password'] === $data['password_confirm'])) 
        {
            try
            {
                $this->userRepo->getUserByUsername($data['username']);
                
                $this->errors['username'] = "Sorry, that username is already taken.";
            }
            catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e)
            {
                $user = $this->userRepo->addANewUser($data);

                if ($user) 
                {
                    $this->messages[] = "Your account has been created successfully. You can now ask an admin to activate it.";
                } 
                else 
                {
                    $this->errors[] = "Sorry, your registration failed. Please go back and try again.";
                }
            }

        } 
        else 
        {
            $this->errors[] = "An unknown error occurred.";
        }
    }

    public function session ()
    {
        return $this->session;
    }
}