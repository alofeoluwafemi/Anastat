<?php

require "EmailValidator.php";

require "DatabaseWriter.php";

require "ErrorLogger.php";

$objValidator = new EmailValidator( "valid@email.com" );

$objValidator->addObserver( new ErrorLogger(), EmailValidator::EVENT_EMAIL_INVALID );

$objValidator->addObserver( new DatabaseWriter(), EmailValidator::EVENT_EMAIL_VALID );

$objValidator->validate();

$objValidator->setEmailAddress( "not_a_valid_address" );

$objValidator->validate();