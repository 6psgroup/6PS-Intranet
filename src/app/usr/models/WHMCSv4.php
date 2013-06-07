<?php
class WHMCSv4 implements IBillingSystem {
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
			$where	= "WHERE status = 'Active'";
		else
			$where	= '';
		
		$q	= "SELECT * FROM tblclients ".$where." ORDER BY email";
		$this->objDA->query($q);
		
		$accounts	= $this->objDA->returnArray();
		$users		= array();
		
		foreach($accounts as $account) {
			$user					= array();
			$user['userid']			= $account['id'];
			$user['username']		= $account['email'];
			$user['firstname']		= $account['firstname'];
			$user['lastname']		= $account['lastname'];
			$user['email']			= $account['email'];
			$user['address1']		= $account['address1'];
			$user['address2']		= $account['address2'];
			$user['city']			= $account['city'];
			$user['state']			= $account['state'];
			$user['company']		= $account['companyname'];
			$user['zip']			= $account['postcode'];
			$user['country']		= $account['country'];
			$user['fax']			= '';
			$user['phone']			= $account['phonenumber'];
			$user['taxexempt']		= $account['taxexempt'];
			$user['signupdate']		= strtotime($account['datecreated']);
			$user['lastlogin']		= strtotime($account['lastlogin']);
			
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
		$q	= "SELECT id FROM tblproducts WHERE gid = '".$group."'";
		$this->objDA->query($q);
		
		$ra		= $this->objDA->returnArray();
		$where	= '';
		
		foreach($ra as $r) {
			$where	.= '\''.$r['id'].'\',';
		}
		$where	= substr($where,0,-1);
		
		$q	= "	SELECT
					h.orderid,
					h.regdate,
					h.domain,
					h.userid,
					u.email,
					p.name as product_name
				FROM
					tblhosting h
					LEFT JOIN tblclients u ON h.userid = u.id
					LEFT JOIN tblproducts p ON h.packageid = p.id
				WHERE
					h.packageid IN(".$where.") AND
					domainstatus = 'Active'
				ORDER BY 
					domain";
		$this->objDA->query($q);
		
		$packages	= $this->objDA->returnArray();
		$data		= array();
		
		foreach($packages as $package) {
			$data1							= array();
			$data1['orderid']				= $package['orderid'];
			$data1['orderdate']				= strtotime($package['regdate']);
			$data1['domain']				= $package['domain'];
			$data1['userid']				= $package['userid'];
			$data1['username']				= $package['email'];
			$data1['package_name']			= $package['product_name'];
			
			
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
			$q	= "	SELECT
						h.orderid,
						h.regdate,
						h.domain,
						h.userid,
						u.email,
						p.name as product_name
					FROM
						tblhosting h
						LEFT JOIN tblclients u ON h.userid = u.id
						LEFT JOIN tblproducts p ON h.packageid = p.id
					WHERE
						h.domain = '".$domain."' AND
						status = 'Active'";
			$this->objDA->query($q);
				
			if($this->objDA->numRows() < 1)
				return false; // not found
			
			$package	= $this->objDA->returnArray();
			$package	= $package[0];
	
			$data		= array();
	
			$data['orderid']		= $package['orderid'];
			$data['orderdate']		= strtotime($package['regdate']);
			$data['domain']			= $package['domain'];
			$data['userid']			= $package['userid'];
			$data['username']		= $package['email'];
			$data['package_name']	= $package['product_name'];
		}
		
		return $data;
	}
	
	/*
	* Method to return package data
	*	@param	int			package		ID pof package
	*	@return	array					Array of data
	*/
	function getPackage($package=0) {
		if($package > 0) {
			$q	= "	SELECT
						h.orderid,
						h.regdate,
						h.domain,
						h.userid,
						u.email,
						h.domainstatus,
						p.name as product_name
					FROM
						tblhosting h
						LEFT JOIN tblclients u ON h.userid = u.id
						LEFT JOIN tblproducts p ON h.packageid = p.id
					WHERE
						h.orderid = '".$package."'";
			$this->objDA->query($q);
			
			if($this->objDA->numRows() < 1)
				return false; // not found
			
			$package	= $this->objDA->returnArray();
			$package	= $package[0];
	
			$data		= array();
	
			$data['orderid']		= $package['orderid'];
			$data['orderdate']		= strtotime($package['regdate']);
			$data['domain']			= $package['domain'];
			$data['userid']			= $package['userid'];
			$data['username']		= $package['email'];
			$data['package_name']	= $package['product_name'];
			
			if($package['domainstatus'] == 'Active')
				$data['status']		= '1';
			else
				$data['status']		= '0';
			
		} else {
			return false;
		}
		
		return $data;
	}
}
?>