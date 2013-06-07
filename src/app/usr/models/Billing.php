<?php
require_once __SITE_FS_ROOT . '/app/usr/models/IBillingSystem.php';
require_once __SITE_FS_ROOT . '/app/usr/models/WHMCSv3.php';
require_once __SITE_FS_ROOT . '/app/usr/models/WHMCSv4.php';
require_once __SITE_FS_ROOT . '/app/usr/models/AWBS.php';

class Billing {
	function __construct() {
		$this->us	= new AWBS();
		$this->us->connect(__DA_AWBS_HOST,__DA_AWBS_DATABASE,__DA_AWBS_USER,__DA_AWBS_PASS);
		
		$this->br	= new WHMCSv4();
		$this->br->connect(__DA_WHMCS_HOST,__DA_WHMCS_DATABASE,__DA_WHMCS_USER,__DA_WHMCS_PASS);
	}
	
	/*
	* Method to list data of packages;
	*/
	function getPackages($type='') {
		$usData	= array();
		$brData	= array();
		
		switch($type) {
			case 'shared':
				$usData	= array_merge($usData,$this->us->usersPackagesList(1));
				$usData	= array_merge($usData,$this->us->usersPackagesList(23));
				
				$brData	= array_merge($brData,$this->br->usersPackagesList(8));
				$brData	= array_merge($brData,$this->br->usersPackagesList(9));
			break;
			
			case 'reseller':
				$usData	= array_merge($usData,$this->us->usersPackagesList(10));
				$usData	= array_merge($usData,$this->us->usersPackagesList(24));
				
				$brData	= array_merge($brData,$this->br->usersPackagesList(7));
			break;
			
			case 'vps':
				$usData	= array_merge($usData,$this->us->usersPackagesList(25));
				$usData	= array_merge($usData,$this->us->usersPackagesList(26));
				
				$brData	= array_merge($brData,$this->br->usersPackagesList(6));
			break;
			
			case 'dedicated':
				$usData	= array_merge($usData,$this->us->usersPackagesList('dedicated'));
				
				$brData	= array_merge($brData,$this->br->usersPackagesList(5));
			break;
			
			default:
				return false;
			break;
		}
		
		$us	= array();
		$br	= array();
		
		foreach($usData as $r) {
			$us[trim($r['domain'])]	= trim($r['domain']).' (User: '.trim($r['username']).')';
		}
		
		foreach($brData as $r) {
			$br[trim($r['domain'])]	= trim($r['domain']).' (User: '.trim($r['username']).')';
		}
		
		$data					= array();
		$data['Brazil (WHMCS)']	= $br;
		$data['US (AWBS)']		= $us;
		
		return $data;
	}
	
	/*
	* Method to search for a domain and return the billing system and package ID
	* 	1 - Brazil (WHMCS)
	*	2 - USA (AWBS)
	*/
	function findPackage($domain='') {
		if($domain != '') {
			// first, look in US
			$system		= 2;
			$package	= $this->us->usersPackagesFind($domain);
			
			if($package === false || count($package) < 1) {
				// next, look in BR
				$system		= 1;
				$package	= $this->br->usersPackagesFind($domain);
				
				if($package === false || count($package) < 1)
					return false;
			}
			
			$data						= array();
			$data['billing_system']		= $system;
			$data['billing_package']	= $package['orderid'];
			
			return $data;
		}
	}
	
	/*
	* Method to get package info based on system and package
	* 	1 - Brazil (WHMCS)
	*	2 - USA (AWBS)
	*	2.1 - USA [Dedicated] (AWBS)
	*/
	function getPackage($type=0,$package=0) {
		if($type == 1 && $package > 0) {
			return $this->br->getPackage($package);
		} elseif($type == 2 && $package > 0) {
			return $this->us->getPackage($package,false); // shared, reseller, vps
		} elseif($type == '2.1' && $package > 0) {
			return $this->us->getPackage($package,true); // dedicated
		}
	}
	
	/*
	* Method to list customer user accounts
	*/
	function getUsers() {
		$usData	= array();
		$brData	= array();
		
		$usData	= $this->us->usersList();
		$brData	= $this->br->usersList();
		
		
		$us	= array();
		$br	= array();
		
		foreach($usData as $r) {
			$us['1-'.$r['userid']]	= trim($r['username']);
		}
		
		foreach($brData as $r) {
			$br['2-'.$r['userid']]	= trim($r['username']);
		}
		
		$data					= array();
		$data['Brazil (WHMCS)']	= $br;
		$data['US (AWBS)']		= $us;
		
		return $data;
	}
	
	/*
	* Method to parse user account information
	* 	1 - Brazil (WHMCS)
	*	2 - USA (AWBS)
	*/
	function parseUser($user=NULL) {
		if($user !== NULL) {
			$user	= explode('-',$user);
			
			if($user !== false && count($user) == 2) {			
				$data						= array();
				$data['billing_system']		= $user[0];
				$data['billing_userid']		= $user[1];			
				
				return $data;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}
?>