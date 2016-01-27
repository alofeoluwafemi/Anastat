<?php
//221Gorr362   africa23   http://africanphilosophicalinquiry.com/cpanel
return array(

		"sitename" => "ANASTAT",

		"site-email" => "oluwafemialofe@yahoo.com",

		"base_url" => $_SERVER['HTTP_HOST'].'/',

		"uri" => $_SERVER['REQUEST_URI'],

		"base_path" => basename(__DIR__).'/',

		"storage_path"  => dirname(__DIR__).'/storage/',

		"storage_path_survey"  => dirname(__DIR__).'/storage/survey/',

		"view_path" => basename(__DIR__).'/views/',

		"url" => $_GET['rqpage'],

		"database" => "anastat",

		"db_user" => "oluwaslim",

		"db_pass" => "oluwaslim"
);