<?php
require_once __SITE_FS_ROOT . '/app/lib/IPv4/ipv4.php';

require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';

require_once __SITE_FS_ROOT . '/app/usr/models/IBillingSystem.php';
require_once __SITE_FS_ROOT . '/app/usr/models/Billing.php';

class Engineering extends AdminCP_adminMenu implements IModule {
	/*
	* Smarty object ("view")
	*/
	var $smarty;
	
	/*
	* Section ID
	*/
	var $sectionID	= 4;
	
	/*
	* POST variable
	*/
	var $post;
	
	function setSmarty($smarty) {
		global $msg;
		
		$smarty->clear_all_assign();
		$this->smarty	= $smarty;
		
		$this->smarty->assign('page_title','6PS Intranet :: Engineering');
		$this->smarty->assign('msg',$msg);
		$this->generateSubMenu(3);
		$this->smarty->assign('menuMainSel','Dashboard');
		$this->smarty->assign('headerUsername',$_SESSION['username']);
		
		// Verify access to section
		if($this->permissionCheck($this->sectionID) !== true) {
			echo 'Access denied.';
			die;
		}
	}
	
	function setPost($post) {
		$this->post		= $post;
	}
	
	function setObjDA(&$objDA) {
		$this->objDA	= $objDA;
	}
	
	/*
	* Class constructor
	*/
	function __construct() {
		parent::__construct();
	}
	
	/*
	* Main
	*/
	function main() {
		$this->smarty->assign('menuSubSel','Dashboard');
		$this->smarty->assign('block_title','Engineering Dashboard');
		
		$rtgStart	= strtotime('16 hours ago');
		$rtgEnd		= time();
		
		$this->smarty->assign('rtgStart',$rtgStart);
		$this->smarty->assign('rtgEnd',$rtgEnd);
		
		$this->smarty->display('engineering/main.tpl');
	}
	
	function devices() {
		$this->smarty->assign('menuSubSel','Devices');
		$this->smarty->assign('block_title','Devices');
		
		$q	= "SELECT id,name FROM inventory ORDER BY name";
		
		$this->objDA->query($q);
		$r			= $this->objDA->returnArray();
		$devices	= array();
		
		foreach($r as $key=>$value) {
			$devices[$value['id']]	= $value['name'];
		}
		
		$this->smarty->assign('devicelist', $devices);
		
		$this->smarty->display('admin/testdb.tpl');
		
	}
	
	function getDevice() {
		$q=$_GET["q"];
		
		$this->smarty->assign('block_title',$q);
		$this->smarty->assign('device_title',$q);
		
		$sql="SELECT * FROM inventory WHERE id='".$q."'";
		
		
		$this->objDA->query($sql);
		
		$listSelectedDevice = $this->objDA->returnArray();
		
		$this->smarty->assign('listSelectedDevice', $listSelectedDevice);
		$this->smarty->display('admin/devices_smarty.tpl');
	}

	/*
	* Method to list subnets
	*/
	function ipList() {
		global $msg;
		
		$this->smarty->assign('menuSubSel','IP Subnets');
		$this->smarty->assign('block_title','IP Subnets');
		
		$subnets	= array();
		$ipv4		= new ipv4();
		
		$q	= "SELECT * FROM ip_types WHERE active = 1 ORDER BY type";
		$this->objDA->query($q);
		
		$types	= $this->objDA->returnArray();
		
		foreach($types as $type) {
			$q	= "	SELECT
						id,
						address AS raw_address,
						mask AS raw_mask,
						INET_NTOA(address) AS address,
						INET_NTOA(mask) AS mask,
						type,
						billing_system,
						billing_package
					FROM
						ip_addresses
					WHERE
						type	= '".$type['id']."'
					ORDER BY
						raw_address";
			$this->objDA->query($q);
			
			$addresses				= $this->objDA->returnArray();
			$networks				= array();
			
			foreach($addresses as $address) {
				$ipv4->setAddress($address['address'],$address['mask']);
				$net		= $ipv4->network();
				$cidr		= $ipv4->netbits();
				$mask		= $ipv4->netmask();
				$inverted	= $ipv4->inverse();
				
				if($this->in_arrayr($net,$networks) === false) {
					$network				= array();
					$network['network']		= $net;
					$network['cidr']		= $cidr;
					$network['mask']		= $mask;
					$network['inverted']	= $inverted;
					$network['raw_address']	= $address['raw_address'];
					$network['raw_mask']	= $address['raw_mask'];
					
					array_push($networks,$network);
				}
			}
			
			$r				= array();
			$r['type']		= $type['type'];
			$r['subnets']	= $networks;

			array_push($subnets,$r);
		}
		
		$this->smarty->assign('subnets',$subnets);
		
		$this->smarty->display('engineering/ipList.tpl');
	}

	
	/*
	* Method to add IP addresses
	*/
	function ipAdd() {
		$this->smarty->assign('menuSubSel','IP Subnets');
		$this->smarty->assign('block_title','Add Subnet');
		
		$this->smarty->assign('action','ipAddProcess.php');
		
		$q	= "SELECT * FROM ip_types WHERE active = 1 ORDER BY type";
		$this->objDA->query($q);
		
		$r		= $this->objDA->returnArray();
		$types	= array();
		
		foreach($r as $key=>$value) {
			$types[$value['id']]	= $value['type'];
		}
		
		$this->smarty->assign('types',$types);
		
		$this->smarty->display('engineering/ipAdd.tpl');
	}
	
	/*
	* Method to process IP Add
	*/
	function ipAddProcess() {
		$network	= $this->post['network'];
		$subnet		= $this->post['subnet'];
		$type		= $this->post['type'];
		
		if(substr($subnet,0,1) == '/')
			$subnet	= substr($subnet,1);
		
		$ipv4	= new ipv4();
		
		if($ipv4->setAddress($network,$subnet) === false) {
			$this->smarty->assign('msg','Invalid IP Address or Subnet Mask');
			$this->ipList();
			return;
		}
		
		$network	= $ipv4->network();
		$broadcast	= $ipv4->broadcast();
		$mask		= $ipv4->netmask();	
		

		
		$start		= ip2long($network);
		$end		= ip2long($broadcast);
		
		$address	= $network;
		
		for($i=$start;$i<=$end;$i++) {
			$q	= "	INSERT INTO
						ip_addresses
					VALUES
						(
							'',
							INET_ATON('".long2ip($i)."'),
							INET_ATON('".$mask."'),
							".$type.",
							'',
							'',
							''
						)";
			$this->objDA->query($q);
		}
		
		$this->smarty->assign('msg','Subnet Added');
		
		$this->ipList();
	}
	
	/*
	* Method to delete a subnet
	*/
	function ipDelete($network='',$subnet='') {
		if($network != '' && $subnet != '') {
			$ipv4	= new ipv4();
			
			if($ipv4->setAddress($network,$subnet) === false) {
				$this->ipList();
				return;
			}
			
			$network	= $ipv4->network();
			$broadcast	= $ipv4->broadcast();
			$netbits	= $ipv4->netbits();
			
			
			$q	= "	DELETE FROM
						ip_addresses
					WHERE
						address BETWEEN INET_ATON('".$network."') AND INET_ATON('".$broadcast."')";
			$this->objDA->query($q);
		}
		
		$this->ipList();
	}
	
	/*
	* Method to list addresses in a subnet
	*	param	string		network		Network address to view
	*	param	string		subnet		CIDR of subnet mask (25 for /25)
	*/
	function ipSubnetList($network='',$subnet='') {
		if($network != '' && $subnet != '') {
			$ipv4	= new ipv4();
			
			if($ipv4->setAddress($network,$subnet) === false) {
				$this->ipList();
				return;
			}
			
			$network	= $ipv4->network();
			$broadcast	= $ipv4->broadcast();
			$netbits	= $ipv4->netbits();
			$netmask	= $ipv4->netmask();
			
			$this->smarty->assign('menuSubSel','IP Subnets');
			$this->smarty->assign('block_title','Subnet: '.$network.'/'.$netbits);
			
			$q	= "	SELECT
						id,
						address AS raw_address,
						mask AS raw_mask,
						INET_NTOA(address) AS address,
						INET_NTOA(mask) AS mask,
						type,
						billing_system,
						billing_package
					FROM
						ip_addresses
					WHERE
						address BETWEEN INET_ATON('".$network."') AND INET_ATON('".$broadcast."')
					ORDER BY
						raw_address";
			$this->objDA->query($q);
			
			$ra			= $this->objDA->returnArray();
			$addresses	= array();
			
			foreach($ra as $r) {
				$address				= array();
				
				if($r['address'] == $network) {
					$address['address']		= $r['address'].' (Network)';
				} elseif($r['address'] == $broadcast) {
					$address['address']		= $r['address'].' (Broadcast)';
				} else {
					$address['address']		= $r['address'];
				}
				
				
				$address['id']				= $r['id'];
				$address['billing_system']	= $r['billing_system'];
				
				if($r['billing_package'] > 0) {
					$objBilling	= new Billing();
					$package	= $objBilling->getPackage($r['billing_system'],$r['billing_package']);
					
					$address['billing_userid']		= $package['userid'];
					$address['billing_username']	= $package['username'];
					$address['billing_package']		= $package['domain'];
					$address['billing_status']		= $package['status'];
					
					switch($r['billing_system']) {
						case 1: // brazil WHMCS
							$address['system']			= 'Brazil (WHMCS)';
							$address['billing_userurl']	= __WWW_WHMCS_USER.$package['userid'];
						break;
						case 2:
						case 2.1: // us AWBS
							$address['system']	= 'USA (AWBS)';
							$address['billing_userurl']	= __WWW_AWBS_USER.$package['userid'];
						break;
						default: // none
							$address['system']	= '';
						break;
					}
				}
				
				array_push($addresses,$address);
			}
			
			$this->smarty->assign('addresses',$addresses);
			
			
			
			// Load data for multi-edit fields
			$objBilling	= new Billing();
			
			$packagesShared		= $objBilling->getPackages('shared');
			$packagesReseller	= $objBilling->getPackages('reseller');
			$packagesVPS		= $objBilling->getPackages('vps');
			$packagesDedicated	= $objBilling->getPackages('dedicated');
			
			array_unshift($packagesShared,'--NONE--');
			array_unshift($packagesReseller,'--NONE--');
			array_unshift($packagesVPS,'--NONE--');
			array_unshift($packagesDedicated,'--NONE--');
			
			$this->smarty->assign('packagesShared',$packagesShared);
			$this->smarty->assign('packagesReseller',$packagesReseller);
			$this->smarty->assign('packagesVPS',$packagesVPS);
			$this->smarty->assign('packagesDedicated',$packagesDedicated);
			
			
			$this->smarty->display('engineering/ipSubnetList.tpl');
		} else {
			$this->ipList();
		}
	}
	
	/*
	* Method to assign/edit an IP address
	*/
	function ipEdit($address='') {
		if($address != '') {
			$this->smarty->assign('menuSubSel','IP Subnets');
			$this->smarty->assign('block_title','IP Address: '.$address);
			
			$q	= "SELECT * FROM ip_addresses WHERE address = INET_ATON('".$address."')";
			$this->objDA->query($q);
			$ip	= $this->objDA->returnArray();
			$ip	= $ip[0];
			
			$id	= $ip['id'];
			
			$objBilling	= new Billing();
			
			$packagesShared		= $objBilling->getPackages('shared');
			$packagesReseller	= $objBilling->getPackages('reseller');
			$packagesVPS		= $objBilling->getPackages('vps');
			$packagesDedicated	= $objBilling->getPackages('dedicated');
			
			array_unshift($packagesShared,'--NONE--');
			array_unshift($packagesReseller,'--NONE--');
			array_unshift($packagesVPS,'--NONE--');
			array_unshift($packagesDedicated,'--NONE--');
			
			$package	= $objBilling->getPackage($ip['billing_system'],$ip['billing_package']);
			$value		= $package['domain'];
			
			if($this->array_key_exists_r($value,$packagesShared) == true) {
				// shared
				$this->smarty->assign('selShared',$package['domain']);
			} elseif($this->array_key_exists_r($value,$packagesReseller) == true) {
				// reseller
				$this->smarty->assign('selReseller',$package['domain']);
			} elseif($this->array_key_exists_r($value,$packagesVPS) == true) {
				// VPS
				$this->smarty->assign('selVPS',$package['domain']);
			} elseif($this->array_key_exists_r($value,$packagesDedicated) == true) {
				// Dedicated
				$this->smarty->assign('selDedicated',$package['domain']);
			}
			
			$this->smarty->assign('notes',$ip['notes']);
			
			$this->smarty->assign('packagesShared',$packagesShared);
			$this->smarty->assign('packagesReseller',$packagesReseller);
			$this->smarty->assign('packagesVPS',$packagesVPS);
			$this->smarty->assign('packagesDedicated',$packagesDedicated);
			$this->smarty->assign('id',$id);
			$this->smarty->assign('address',$address);
			
			$this->smarty->display('engineering/ipEdit.tpl');
		} else {
			$this->ipList();
		}
	}
	
	/*
	* Method to process editing of an IP address
	*/
	function ipEditProcess() {
		if($this->post) {
			$this->smarty->assign('menuSubSel','IP Subnets');
			$this->smarty->assign('block_title','IP Address: '.$address);
			
			$id			= $this->post['id'];
			$address	= $this->post['address'];
			$shared		= $this->post['packagesShared'];
			$reseller	= $this->post['packagesReseller'];
			$vps		= $this->post['packagesVPS'];
			$dedicated	= $this->post['packagesDedicated'];
			$notes		= addslashes($this->post['notes']);
			
			if($shared != '0') {
				$domain	= $shared;
			} elseif($reseller != '0') {
				$domain	= $reseller;
			} elseif($vps != '0') {
				$domain	= $vps;
			} elseif($dedicated != '0') {
				$domain	= $dedicated;
			} else {
				// revoke address
				$package['billing_system']	= 0;
				$package['billing_package']	= 0;
				$notes						= '';
				$domain						= '';
			}
			
			if($domain != '') {
				$objBilling	= new Billing();
				$package	= $objBilling->findPackage($domain);
			} else {
				$package	= true;
			}
			
			if($dedicated != '0' && $package['billing_system'] == 2) {
				$package['billing_system']	= 2.1;
			}
			
			if($package !== false) {
				$q	 = "UPDATE
							ip_addresses
						SET
							billing_system	= '".$package['billing_system']."',
							billing_package	= '".$package['billing_package']."',
							notes			= '".$notes."'
						WHERE
							id				= '".$id."'";
				$this->objDA->query($q);
			} else {
				$this->smarty->assign('msg','ERROR: Package not found.');
				$this->ipList();
				return;
			}
			
			$q	= "SELECT mask FROM ip_addresses WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$r		= $this->objDA->returnArray();
			$mask	= $r[0]['mask'];
			$mask	= long2ip($mask); // convert to address
			
			$this->ipSubnetList($address,$mask);
		} else {
			$this->ipList();
		}
	}
	
	/*
	* Method to process editing of multiple IP addresses
	*/
	function ipEditProcessMulti() {
		if($this->post) {
			$addresses	= $this->post['addresses'];
			
			$shared		= $this->post['packagesShared'];
			$reseller	= $this->post['packagesReseller'];
			$vps		= $this->post['packagesVPS'];
			$dedicated	= $this->post['packagesDedicated'];
			$notes		= addslashes($this->post['notes']);
			
			if($shared != '0') {
				$domain	= $shared;
			} elseif($reseller != '0') {
				$domain	= $reseller;
			} elseif($vps != '0') {
				$domain	= $vps;
			} elseif($dedicated != '0') {
				$domain	= $dedicated;
			} else {
				// revoke address
				$package['billing_system']	= 0;
				$package['billing_package']	= 0;
				$notes						= '';
				$domain						= '';
			}
			
			if($domain != '') {
				$objBilling	= new Billing();
				$package	= $objBilling->findPackage($domain);
			} else {
				$package	= true;
			}
			
			if($dedicated != '0' && $package['billing_system'] == 2) {
				$package['billing_system']	= 2.1;
			}
			
			// Loop through each adress and set values
			if($package !== false) {
				foreach($addresses as $addressID) {
					$q	= "	UPDATE
								ip_addresses
							SET
								billing_system	= '".$package['billing_system']."',
								billing_package	= '".$package['billing_package']."',
								notes			= '".$notes."'
							WHERE
								id				= '".$addressID."'";
					$this->objDA->query($q);
				}
			} else {
				$this->smarty->assign('msg','ERROR: Package not found.');
				$this->ipList();
				return;
			}
			
			$q	= "SELECT address,mask FROM ip_addresses WHERE id = '".$addresses[0]."'";
			$this->objDA->query($q);
			
			$r			= $this->objDA->returnArray();
			$mask		= long2ip($r[0]['mask']);
			$address	= long2ip($r[0]['address']);
			
			$this->ipSubnetList($address,$mask);
		} else {
			$this->ipList();
		}
	}
}
?>