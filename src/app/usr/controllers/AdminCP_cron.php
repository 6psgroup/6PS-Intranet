<?php
require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';

require_once __SITE_FS_ROOT . '/app/usr/models/NNTP.php';

class AdminCP_cron extends AdminCP_adminMenu implements IModule {
	function setObjDA(&$objDA) {
		$this->objDA	= $objDA;
	}
	
	/*
	* Class constructor
	*/
	function __construct() {
	
	}
	
	/*
	* Main
	*/
	function main() {
		return;
	}
	
	/*
	* Method to process nightly cron job items
	*/
	function cronNightly() {
		echo 'Start nightly cron'."\n\r";
		
		$this->processTimeEntries();
		
		echo 'End nightly cron'."\n\r";
	}
	
	
	/*
	* Method to process billable time entries
	*/
	function processTimeEntries() {
		
	}
}
?>