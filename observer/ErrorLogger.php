<?php

require "IObservable.php";

require "IObserver.php";

class ErrorLogger implements IObserver
{

 public function notify( IObservable $objSource, $strEventType )
  {

    if($strEventType == EmailValidator::EVENT_EMAIL_INVALID && $objSource instanceof EmailValidator)
    {

      printf( "Error: %s is not a valid email address.",$objSource-&gt;getEmailAddress() );

    }

  }

}