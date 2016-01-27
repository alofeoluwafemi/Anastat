<?php

  require "IObservable.php";

  class EmailValidator implements IObservable
  {

	    const EVENT_EMAIL_VALID = 1;
	    const EVENT_EMAIL_INVALID = 2;

	    protected $strEmailAddress;

	    protected $aryObserversArray;

	    public function __construct( $strEmailAddress )
	    {
	      $this->strEmailAddress = $strEmailAddress;
	      $this->aryObserversArray = array( array() );
	    }

	    public function setEmailAddress( $strEmailAddress )
	    {
	      $this->strEmailAddress = $strEmailAddress;
	    }

	    public function getEmailAddress()
	    {
	      return $this->strEmailAddress;
	    }

	    public function validate()
	    {
	      if( preg_match( "/^[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/",$this->strEmailAddress ) )
	      {
	        $this->fireEvent( EmailValidator::EVENT_EMAIL_VALID );
	      }
	      else
	      {
	        $this->fireEvent( EmailValidator::EVENT_EMAIL_INVALID );
	      }
	    }

	    public function addObserver( IObserver $objObserver, $strEventType )
	    {
	      $this->aryObserversArray[$strEventType][] = $objObserver;
	    }

	    public function fireEvent( $strEventType )
	    {
	      if( is_array( $this->aryObserversArray[$strEventType] ) )
	      {
	        foreach ( $this->aryObserversArray[$strEventType] as $objObserver )
	        {
	          $objObserver->notify( $this, $strEventType );
	        }
	      }
	    }

    }