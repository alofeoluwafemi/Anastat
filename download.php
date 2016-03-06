<?php

require 'vendor/autoload.php';

	if(isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['filename']))
		{
			$file = $_GET['filename'];

			$filepath = config('storage_path_survey').$file;

			// dd($filepath);
			
			downloadfile($filepath);
		} 

?>