<?php

// use PHPMailer;

/**
 * Functions to manipulate file
 */

/**
 * @param $filename
 * @return bool|null
 */
function hasFile($filename)
{
    if(!isset($_FILES)) return FALSE;
    if(!empty($_FILES[$filename]['name'])) return TRUE;
    return FALSE;
}

/**
 * @param $filename
 * @return file size in MB
 */
function getFileSize($filename)
{
    if(!isset($_FILES)) return NULL;
    if(!empty($_FILES[$filename]) && !empty($_FILES[$filename]['size'])) return $_FILES[$filename]['size'] / 1024000;
    return NULL;
}

/**
 * @param $filename
 * @return null
 */
function getFileName($filename)
{
    if(!isset($_FILES)) return NULL;
    if(!empty($_FILES[$filename])) return $_FILES[$filename]['name'];
    return NULL;
}

/**
 * @param $filename
 * @return null
 */
function getFileType($filename)
{
    $types = array ('zip' => 'application/zip');
    if(!isset($_FILES)) return NULL;
    if(!empty($_FILES[$filename])) {
        if(!empty($_FILES[$filename]['type'])) return $_FILES[$filename]['type'];
        return $types[pathinfo($filename)['extension']];
    }
    return NULL;
}

function getFileExtension($filename)
{
    if(empty($filename)) return NULL;
    if(isset(pathinfo($filename)['extension'])) return strtolower( pathinfo($filename)['extension'] );
    return NULL;
}

function move($path,$filename,$newname = null)
{
    $allowed = array('jpg','jpeg','pdf','png','xls','xlsx','zip');

    if(file_exists($_FILES[$filename]['tmp_name']))
    {
        if( in_array(getFileExtension( getFileName($filename) ),$allowed)  && $_FILES[$filename]['error'] == 0)
        {
            $uploadname = isset($newname) ? $newname : getFileName($filename);
            try{
                move_uploaded_file($_FILES[$filename]['tmp_name'],$path.$uploadname);
            }
            catch(Exception $e)
            {
                echo "Error Occurred Uploading File: ".$e->getMessage();
            }
        }
    }else{
        throw new Exception('FILE NOT FOUND');
    }
}

/**
 * Check if request is Ajax
 * @return bool
 */
function isAjax()
{
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) && preg_match('#'.$_SERVER['HTTP_HOST'].'#',config('base_url'))) return TRUE;
    return FALSE;
}

function downloadfile($filepath)
{

    if (file_exists($filepath)) 
    {
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    die("file doesn't exists");
}


//PLUGGABLE FUNCTIONS


function fire_hook($event,$param)
{
    global $action_events;

    if(isset($action_events[$event]))
    {
        foreach ($action_events[$event] as $key => $func) {
            if(!function_exists($func))
            {
                die('Unknown function');
            }
            else{
                call_user_func($func,$param);
            }
        }
    }
}


function register_hook($event,$func)
{
    global $action_events;

    $action_events[$event][] = $func;
}

//Email site admin when new request is received
function mailadmin()
{
    $data = $_POST;
    $email = ""; $phone = "";
    extract($data);

        //send email to notify admin of new request
        $head  = "Request By: ".$email."\r\n<br/>";
        $head .= "Phone Number: ".$phone."\r\n<br/><br/>";

        $message = wordwrap('A new request was just placed on anastat platform. Kindly login to admin to view request details',70,"\r\n");

        $body = $head.$message;

        $subject = "New Request Received:: ".date('m d Y h:i:s');

        sendmail(config('site-email'),'ANASTAT APPLICATION',$subject,$body);
        //end email
}


function sendmail($email,$name,$subject,$body)
{
                //SMTP needs accurate times, and the PHP time zone MUST be set
                //This should be done in your php.ini, but this is how to do it if you don't have access to that
                date_default_timezone_set('Etc/UTC');

                //Create a new PHPMailer instance
                $mail = new PHPMailer;
                //Tell PHPMailer to use SMTP
                $mail->isSMTP();

                //Enable SMTP debugging
                // 0 = off (for production use)
                // 1 = client messages
                // 2 = client and server messages
                $mail->SMTPDebug = 0;
                //Ask for HTML-friendly debug output
                $mail->Debugoutput = 'html';
                //Set the hostname of the mail server
                // $mail->Host = 'smtp.gmail.com';
                // use
                $mail->Host = gethostbyname('smtp.gmail.com');
                // if your network does not support SMTP over IPv6
                //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
                $mail->Port = 587;
                //Set the encryption system to use - ssl (deprecated) or tls
                $mail->SMTPSecure = 'tls';
                //Whether to use SMTP authentication
                $mail->SMTPAuth = true;
                // $mail->Username = 'eniolasadiq@gmail.com';                    // SMTP username
                // $mail->Password = '**************';                          // SMTP password
                $mail->Username = 'anastat2015@gmail.com';                     // SMTP username
                $mail->Password = '************';                             // SMTP password
                //Set who the message is to be sent from
                $mail->setFrom('info@adsr.com.ng',    'Anastat');             //Set who the message is to be sent from
                $mail->addReplyTo('info@adsr.com.ng', 'Anastat');             //Set an alternative reply-to address
                $mail->addAddress($email,$name);                                // Name is optional

                //Set the subject line
                $mail->Subject = $subject;

                $mail->Body    = '<span style="font-family:calibri;font-size:16px"> <img src="../../assets/img/repairlogo.png"/> <br><br>'.$body;
                 
                $mail->AltBody = 'This is a plain-text message body';
                //Attach an image file
                // $mail->addAttachment('images/phpmailer_mini.png');
                //send the message, check for errors
                if (!$mail->send()) {
                    // echo "Mailer Error: " . $mail->ErrorInfo;
                }
}
