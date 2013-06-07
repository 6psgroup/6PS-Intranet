<?php
class AWBS implements IBillingSystem {
	function __constructor() {
	
	}
	
	/*
	* Method to connect to billing system db
	*/
	function connect($host,$database,$username,$password) {
		$this->objDA			= new CMS_mysql();
		
		$this->objDA->host		= $host;
		$this->objDA->database	= $database;
		$this->objDA->user		= $username;
		$this->objDA->pass		= $password;
		
		return $this->objDA->connect();
	}
	
	/*
	* Method to list users
	*	@param	bool	active		If false, lists all clients; If true, lists active clients
	*	@return	array				Array of users
	*/
	function usersList($active=true) {
		if($active === true)
			$where	= "WHERE active = '1'";
		else
			$where	= '';
		
		$q	= "SELECT * FROM users ".$where." ORDER BY username";
		$this->objDA->query($q);
		
		$accounts	= $this->objDA->returnArray();
		$users		= array();
		
		foreach($accounts as $account) {
			$user					= array();
			$user['userid']			= $account['id'];
			$user['username']		= $account['username'];
			$user['firstname']		= $account['fname'];
			$user['lastname']		= $account['lname'];
			$user['email']			= $account['email'];
			$user['address1']		= $account['add1'];
			$user['address2']		= $account['add2'];
			$user['city']			= $account['city'];
			$user['state']			= $account['state'];
			$user['company']		= $account['org'];
			$user['zip']			= $account['zip'];
			$user['country']		= $account['country'];
			$user['fax']			= $account['fax'];
			$user['phone']			= $account['phone'];
			$user['taxexempt']		= $account['taxexempt'];
			$user['signupdate']		= $account['signup_date'];
			$user['lastlogin']		= $account['lastlogin'];
			
			array_push($users,$user);
		}
		
		return $users;
	}
	
	/*
	* Method to list users
	*	@param	int			group		Product Group ID of packages to list
	*	@return	array					Array of data
	*/
	function usersPackagesList($group='') {
		if($group != 'dedicated') {
			// shared, reseller, vps
			$q	= "SELECT planname FROM hosting_plans WHERE type = '".$group."'";
			$this->objDA->query($q);
			
			$ra		= $this->objDA->returnArray();
			$where	= '';
			
			foreach($ra as $r) {
				$where	.= '\''.$r['planname'].'\',';
			}
			$where	= substr($where,0,-1);
			
			$q	= "	SELECT
						h.id,
						h.start_date,
						h.domain,
						h.ownerid,
						u.id AS userid,
						h.plan AS package_name
					FROM
						hostinglist h
						LEFT JOIN users u ON h.ownerid = u.username
					WHERE
						h.plan IN(".$where.") AND
						status = 1
					ORDER BY 
						domain";
			$this->objDA->query($q);
		} else {
			// dedicated
			$q	= "	SELECT
						s.id,
						s.start_date,
						s.domain,
						s.ownerid,
						u.id AS userid,
						s.plan AS package_name
					FROM
						serverlist s
						LEFT JOIN users u ON s.ownerid = u.username
					WHERE
						status = 1
					ORDER BY 
						domain";
			$this->objDA->query($q);
		}
		
		$packages	= $this->objDA->returnArray();
		$data		= array();
		
		foreach($packages as $package) {
			$data1							= array();
			$data1['orderid']				= $package['id'];
			$data1['orderdate']				= $package['start_date'];
			$data1['domain']				= $package['domain'];
			$data1['userid']				= $package['userid'];
			$data1['username']				= $package['ownerid'];
			$data1['package_name']			= $package['package_name'];
			
			
			array_push($data,$data1);
		}
		
		return $data;
	}
	
	/*
	* Method to find a user and package data based on domain
	*	@param	string		domain		Domain to search
	*	@return	array					Array of data
	*/
	function usersPackagesFind($domain='') {
		if($domain != '') {
			// shared, reseller, vps
			$q	= "	SELECT
						h.id,
						h.start_date,
						h.domain,
						h.ownerid,
						u.id AS userid,
						h.plan AS package_name
					FROM
						hostinglist h
						LEFT JOIN users u ON h.ownerid = u.username
					WHERE
						h.domain = '".$domain."' AND
						status = 1";
			$this->objDA->query($q);
			
			if($this->objDA->numRows() < 1) {
				// dedicated
				$q	= "	SELECT
							s.id,
							s.start_date,
							s.domain,
							s.ownerid,
							u.id AS userid,
							s.plan AS package_name
						FROM
							serverlist s
							LEFT JOIN users u ON s.ownerid = u.username
						WHERE
							s.domain = '".$domain."' AND
							status = 1";
				$this->objDA->query($q);
				
				if($this->objDA->numRows() < 1)
					return false; // not found
			}
			
			$package	= $this->objDA->returnArray();
			$package	= $package[0];
	
			$data		= array();
	
			$data['orderid']		= $package['id'];
			$data['orderdate']		= $package['start_date'];
			$data['domain']			= $package['domain'];
			$data['userid']			= $package['userid'];
			$data['username']		= $package['ownerid'];
			$data['package_name']	= $package['package_name'];
		}
		
		return $data;
	}
	
	/*
	* Method to get a package
	*	@param	int			package		Package ID
	*	@param	bool		dedicated	If true, return dedicated package info
	*	@return	array					Array of data
	*/
	function getPackage($package=0,$dedicated=false) {
		if($package > 0 && $dedicated == true) {
			// dedicated
			$q	= "	SELECT
						s.id,
						s.start_date,
						s.domain,
						s.ownerid,
						s.status,
						u.id AS userid,
						s.plan AS package_name
					FROM
						serverlist s
						LEFT JOIN users u ON s.ownerid = u.username
					WHERE
						s.id = '".$package."'";
		} elseif($package > 0) {
			// shared, reseller, vps
			$q	= "	SELECT
						h.id,
						h.start_date,
						h.domain,
						h.ownerid,
						h.status,
						u.id AS userid,
						h.plan AS package_name
					FROM
						hostinglist h
						LEFT JOIN users u ON h.ownerid = u.username
					WHERE
						h.id = '".$package."'";
		} else {
			return false;
		}
		
		$this->objDA->query($q);

		$package	= $this->objDA->returnArray();
		$package	= $package[0];

		$data		= array();

		$data['orderid']		= $package['id'];
		$data['orderdate']		= $package['start_date'];
		$data['domain']			= $package['domain'];
		$data['userid']			= $package['userid'];
		$data['username']		= $package['ownerid'];
		$data['package_name']	= $package['package_name'];
		
		if($package['status'] == 1)
			$data['status']			= '1'; // enabled
		else
			$data['status']			= '0'; // disabled
		
		return $data;
	}
}
?>