<?php

require "IObservable.php";

require "EmailValidator.php";

require "IObserver.php";

class DatabaseWriter implements IObserver
{
  public function notify( IObservable $objSource, $strEventType )
   {
     if( $strEventType == EmailValidator::EVENT_EMAIL_VALID && $objSource instanceof EmailValidator )
     {
       printf( "Email address %s is valid and was stored in database.",$objSource->getEmailAddress() );
     }
   }

}