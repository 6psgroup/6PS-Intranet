<?php
require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';

require_once __SITE_FS_ROOT . '/app/usr/models/IBillingSystem.php';
require_once __SITE_FS_ROOT . '/app/usr/models/Billing.php';

class HR extends AdminCP_adminMenu implements IModule {
	/*
	* Smarty object ("view")
	*/
	var $smarty;
	
	/*
	* Section ID
	*/
	var $sectionID	= 10;
	
	/*
	* POST variable
	*/
	var $post;
	
	function setSmarty($smarty) {
		global $msg;
		
		$smarty->clear_all_assign();
		$this->smarty	= $smarty;
		
		$this->smarty->assign('page_title','6PS Intranet :: HR');
		$this->smarty->assign('msg',$msg);
		$this->generateSubMenu(10);
		$this->smarty->assign('menuMainSel','HR');
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
		$this->timeList();
	}

////////////////////////////////
// HR			              //
////////////////////////////////
	/*
	*  Method to list time entries
	*/
	function timeList() {
		global $msg;
		
		$selUser		= $this->post['selUser'];
		$startMonth		= $this->post['startMonth'];
		$startDay		= $this->post['startDay'];
		$startYear		= $this->post['startYear'];
		$endMonth		= $this->post['endMonth'];
		$endDay			= $this->post['endDay'];
		$endYear		= $this->post['endYear'];
		
		$this->smarty->assign('menuSubSel','Employee Time Entries');
		$this->smarty->assign('block_title','Employee Time Entries');
		
		$q	= "SELECT * FROM users_users WHERE enabled = 1 ORDER BY lastname,firstname";
		$this->objDA->query($q);
		
		$users		= $this->objDA->returnArray();
		$userTree	= array();
		
		foreach($users as $r) {
			$userTree[$r['id']]	= $r['lastname'].' '.$r['firstname'].' ('.$r['username'].')';
		}
		
		$this->smarty->assign('users',$userTree);
		
		if($_POST['selUser'] != NULL) {
			$selUser	= $_POST['selUser'];
		} else {
			$selUser	= array_keys($userTree);
			$selUser	= $selUser[0];
		}
		
		$this->smarty->assign('selUser',$selUser);
		
		
		// start date
		if($startMonth > 0 && $startDay > 0 && $startYear > 0)
			$start		= mktime(0,0,0,$startMonth,$startDay,$startYear);
		else
			$start		= strtotime('12am 1 ' .date('F Y'));
		
		// end date
		if($endMonth > 0 && $endDay > 0 && $endYear > 0)
			$end			= mktime(23,59,59,$endMonth,$endDay,$endYear);
		else
			$end			= time(); // now
		
		$this->smarty->assign('startDate',$start);
		$this->smarty->assign('endDate',$end);
		
		// get entries
		$q	= "	SELECT
					*
				FROM
					time_entries t
				WHERE
					user_id		= '".$selUser."' AND
					bill_date 	BETWEEN ".$start." AND ".$end."
				ORDER BY
					bill_date ASC";

		$this->objDA->query($q);
		
		$entries	= $this->objDA->returnArray();
		$this->smarty->assign('entries',$entries);
		
		// total time entries, after hours fees, etc.
		$totalHours			= 0.0;
		$totalAfterHours	= 0.0;
		$totalBilledHours	= 0.0;

		foreach($entries as $r) {
			$totalHours		+= $r['duration'];
			
			if($r['afterhours'] != 0)
				$totalAfterHours	+= $r['duration'];
			
			if($r['billing_system'] != 0)
				$totalBilledHours	+= $r['duration'];
		}
		
		if($totalBilledHours > 0)
			$totalBilledPercent	= ( $totalBilledHours / $totalHours ) * 100;
		else
			$totalBilledPercent	= 0;
		
		$this->smarty->assign('totalHours',$totalHours);
		$this->smarty->assign('totalAfterHours',$totalAfterHours);
		$this->smarty->assign('totalBilledHours',$totalBilledHours);
		$this->smarty->assign('totalBilledPercent',$totalBilledPercent);
		
		$this->smarty->display('hr/timeList.tpl');
	}
	
	/*
	* Method to edit time entry
	*/
	function timeEdit($id=0) {
		$this->smarty->assign('menuSubSel','Employee Time Entries');
		
		$q	= "SELECT * FROM users_users WHERE enabled = 1 ORDER BY lastname,firstname";
		$this->objDA->query($q);
		
		$users		= $this->objDA->returnArray();
		$userTree	= array();
		
		foreach($users as $r) {
			$userTree[$r['id']]	= $r['lastname'].' '.$r['firstname'].' ('.$r['username'].')';
		}
		
		$this->smarty->assign('employees',$userTree);
		
		$objBilling	= new Billing();
		$users		= $objBilling->getUsers();
		array_unshift($users,'-- Non-Billable (Administrative) --');
		
		$this->smarty->assign('users',$users);


		if($id > 0) {			
			// edit
			$this->smarty->assign('block_title','Edit Time Entry');
			
			$q	= "SELECT * FROM time_entries WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$r	= $this->objDA->returnArray();
			$r	= $r[0];
			
			if($r['billed'] == 1) {
				$this->smarty->assign('readonly',1);
				$this->smarty->assign('msg','Customer billed; Time entry read-only');
			}
			
			if($r['priceoverride'] == 0)
				$priceoverride	= '15.00';
			else
				$priceoverride	= $r['priceoverride'];
				
			if($r['rate'] == 0)
				$rate	= '';
			else
				$rate	= $r['rate'];
			
			$this->smarty->assign('id',$id);
			$this->smarty->assign('selEmployee',$r['user_id']);
			$this->smarty->assign('selUser',$r['billing_system'].'-'.$r['billing_userid']);
			$this->smarty->assign('bill_date',$r['bill_date']);
			$this->smarty->assign('duration',$r['duration']);
			$this->smarty->assign('rate',$rate);
			$this->smarty->assign('description',$r['description']);
			$this->smarty->assign('afterhours',$r['afterhours']);
			$this->smarty->assign('afterhoursfee',$r['afterhoursfee']);
			$this->smarty->assign('priceoverride',$priceoverride);
		} else {
			return $this->timeList();
		}
		
		$this->smarty->display('hr/timeEdit.tpl');
	}
	
	/*
	* Method to process time entry
	*/
	function timeEditProcess() {
		if($this->post) {
			$id				= $this->post['id'];
			$employee		= $this->post['employee'];
			$userid			= $_SESSION['userid'];
			$customer		= $this->post['users'];
			$entry_date		= time();
			$billMonth		= $this->post['billMonth'];
			$billDay		= $this->post['billDay'];
			$billYear		= $this->post['billYear'];
			$duration		= $this->post['duration'];
			$rate			= $this->post['rate'];
			$description	= $this->post['description'];
			$afterhours		= $this->post['afterhours'];
			$afterhoursfee	= $this->post['afterhoursfee'];
			$priceoverride	= $this->post['priceoverride'];
			
			if($billMonth > 0 && $billDay > 0 && $billYear > 0) {
				$bill_date			= mktime(date('G'),date('i'),date('s'),$billMonth,$billDay,$billYear);
			} else {
				$this->smarty->assign('msg','Invalid bill date');
				$this->timeList();
				return;
			}
			
			if($afterhours == 'checked')
				$afterhours	= 1;
			else
				$afterhours	= 0;
				
			if($afterhoursfee == 'checked') {
				$afterhoursfee	= 1;
				if($priceoverride == '' || $priceoverride == 0)
					$priceoverride	= 15; // default after-hours fee
			} else {
				$afterhoursfee	= 0;
				$priceoverride	= 0;
			}
			
			if($rate == 0 || $rate == '')
				$rate	= 65; // default hourly rate
			
			$objBilling		= new Billing();
			$billingUser	= $objBilling->parseUser($customer);
			$billing_system	= $billingUser['billing_system'];
			$billing_userid	= $billingUser['billing_userid'];
			
			if($id > 0) {
				// edit
				$q	= "	UPDATE
							time_entries
						SET
							user_id			= '".$employee."',
							billing_system	= '".$billing_system."',
							billing_userid	= '".$billing_userid."',
							bill_date		= '".$bill_date."',
							duration		= '".$duration."',
							rate			= '".$rate."',
							description		= '".$description."',
							afterhours		= '".$afterhours."',
							afterhoursfee	= '".$afterhoursfee."',
							priceoverride	= '".$priceoverride."'
						WHERE
							id			= '".$id."'";
				$this->objDA->query($q);
				
				$this->smarty->assign('id',$id);
				
				$this->smarty->assign('msg','Task Edited');
			}
		}
		
		header('Location: '.__SITE_WWW_ROOT.'/module/HR/timeList.php');
		return;
	}
	
}
?>