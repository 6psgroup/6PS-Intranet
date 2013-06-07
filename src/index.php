<?php
//error_reporting(6143);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$start	= date('h:i:s A');

require 'app/etc/configs/settings.php';

require __SITE_FS_ROOT . 'app/lib/MySQL/DA_mysql.class.php';
require __SITE_FS_ROOT . 'app/usr/models/MySQL.php';

$msg	= '';

$objDA	= new CMS_mysql();
	
$objDA->host		= __DA_HOST;
$objDA->database	= __DA_DATABASE;
$objDA->user		= __DA_USER;
$objDA->pass		= __DA_PASS;
$objDA->connect();

if (php_sapi_name() == 'cli') {
	// command-line
	chdir(__SITE_FS_ROOT);
	echo "----------------START: ".date('d/m/y h:i:s A')."----------------\n";
	if(count($argv) < 3) {
		echo "SYNTAX: <module_name> <method_name>\r\n\r\n";
		die;
	}
	
	if(file_exists(__SITE_FS_ROOT . 'app/usr/controllers/'.$argv[1].'.php')) {
		require __SITE_FS_ROOT . 'app/usr/controllers/IModule.php';
		require __SITE_FS_ROOT . 'app/usr/controllers/'.$argv[1].'.php';
	} else {
		echo 'Module does not exist.';
		die;
	}
	
	$objModule	= new $argv[1]();

	$objModule->setObjDA($objDA);
	
	if(method_exists($objModule,$argv[2])) {
		$objModule->$argv[2]($argv[3]);
	}
	
	echo "----------------END: ".date('d/m/y h:i:s A')."----------------\n\n";
} else {
	require __SITE_FS_ROOT . 'app/lib/Smarty/Smarty.class.php';
	
	$smarty 				= new Smarty;
	
	$smarty->compile_check	= true;
	$smarty->debugging 		= false;
	$smarty->caching    	= false;
	
	$smarty->template_dir	= __SITE_FS_ROOT . 'app/usr/views';
	$smarty->compile_dir	= __SITE_FS_ROOT . 'app/var/views_compile';
	$smarty->config_dir		= __SITE_FS_ROOT . 'app/etc/configs';
	$smarty->cache_dir		= __SITE_FS_ROOT . 'app/var/views_cache';
	
	// Take the URI and scrub it to prepare for parsing	
	$request		= array();
	$request_uri	= explode('/', $_SERVER['REQUEST_URI']);
	
	$params			= explode('?', $_SERVER['REQUEST_URI']);
	if(is_array($params) && count($params) > 1) {
		$params			= explode('&',$params[1]);
	}	
	
	foreach($request_uri as $r) {
		$clean_request	= explode('?', $r);
		$clean_request	= $clean_request[0];
		$clean_request	= explode('.',$clean_request);
		array_push($request,$clean_request[0]);
	}
	
	array_shift($request);
	array_shift($request); // get rid of /intranet/
	
	if($request[0] == 'intranet' || $request[0] == '') {
		header('Location: '.__SITE_WWW_ROOT.'/module/AdminCP/');
		die;
	}
	
	if($request[0] == 'module') {
		// load module (e.g. -- /module/AdminCP)
		if(file_exists(__SITE_FS_ROOT . 'app/usr/controllers/'.$request[1].'.php')) {

			require __SITE_FS_ROOT . 'app/usr/controllers/IModule.php';
			require __SITE_FS_ROOT . 'app/usr/controllers/'.$request[1].'.php';
		
			if(class_exists($request[1])) {
				$objModule			= new $request[1]();

				$objModule->setObjDA($objDA);
				$objModule->setSmarty($smarty);
	
				if(isset($_POST))
					$objModule->setPost($_POST);
				else
					$objModule->setPost(NULL);
				
				if($request[2] != '') {
					if(method_exists($objModule,$request[2])) {
						if(is_array($params) && count($params) > 0) {
							switch(count($params)) {
								case 1:
									$objModule->$request[2]($params[0]);
									break;
								case 2:
									$objModule->$request[2]($params[0],$params[1]);
									break;
								case 3:
									$objModule->$request[2]($params[0],$params[1],$params[2]);
									break;
								case 4:
									$objModule->$request[2]($params[0],$params[1],$params[2],$params[3]);
									break;
								case 5:
									$objModule->$request[2]($params[0],$params[1],$params[2],$params[3],$params[4]);
									break;
								default:
									$request[0]	= '';
									break;
							}
						} elseif((isset($request[3]) && $request[3] != '')) {
								$objModule->$request[2]($request[3]);
						} else {
							$objModule->$request[2]();
						}
					} else {
						$request[0]	= ''; // default to home page
					}
				} else {
					$objModule->main();
				}
			} else {
				$request[0]	= ''; // default to home page
			}
		} else {
			$request[0]	= ''; // default to home page
		}
	}
	
	if(substr($_SERVER['REMOTE_ADDR'],0,7) == '10.18.0') {
		echo '<p>&nbsp;</p>';
		echo '<p>&nbsp;</p>';
		echo '<p>&nbsp;</p>';
		echo 'START: '.$start;
		echo 'DEBUG: '.$objDA->debug;
		
		echo 'START: '.$start;
		echo '<br />END: &nbsp;&nbsp;&nbsp;&nbsp;'.date('h:i:s A');
		echo '<p>&nbsp;</p>';
	}
}

?>