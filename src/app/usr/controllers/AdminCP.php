<?php
require_once __SITE_FS_ROOT . '/app/lib/SmartyPaginate/SmartyPaginate.class.php';
require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';
require_once __SITE_FS_ROOT . '/app/lib/YubiAuth/Yubico.php';
require_once __SITE_FS_ROOT . '/app/usr/models/IBillingSystem.php';
require_once __SITE_FS_ROOT . '/app/usr/models/Billing.php';

class AdminCP extends AdminCP_adminMenu implements IModule {
	/*
	* Smarty object ("view")
	*/
	var $smarty;
	
	/*
	* POST variable
	*/
	var $post;
	
	function setSmarty($smarty) {
		global $msg;
		
		$smarty->clear_all_assign();
		$this->smarty	= $smarty;

		$this->smarty->assign('msg',$msg);
		$this->generateSubMenu(1);
		$this->smarty->assign('menuMainSel','Home');
		$this->smarty->assign('headerUsername',$_SESSION['username']);
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
		if(isset($_COOKIE['PHPSESSID'])) {
			session_start($_COOKIE['PHPSESSID']);
			
			if(count($_SESSION) < 1) {
				// invalid session
				session_destroy();
				unset($_SESSION);
			}
		}
	}
	
	/*
	* Main page (either login or welcome)
	*/
	function main() {
		global $msg;
		
		if(isset($_SESSION)) {
			if($this->post) {
				header('Location: '.__SITE_WWW_ROOT);
			} else {
				$this->generateSubMenu(1); // regenerate sub menu now that session exists
				
				// logged in, show welcome page
				$this->smarty->assign('page_title','6PS Intranet :: Home');
				$this->smarty->assign('menuSubSel','Home');
				$this->smarty->assign('headerUsername',$_SESSION['username']); // reload username
				
				$this->smarty->assign('block_title','Bem-vindo!');
				
				$this->smarty->display('admin/adminHome.tpl');
			}
		} else {
			// not logged in
			$this->smarty->assign('page_title','6PS Intranet :: Login');
			
			$this->smarty->display('admin/adminLogin.tpl');
		}
	}
	
	/*
	* Method to process login request
	*/
	function loginProcess() {
		global $msg;
		
		/*
		$username	= $this->post['username'];
		$password	= $this->post['password'];
		*/
		
		$pin		= $this->post['pin'];
		$yubikey	= $this->post['yubikey'];
		
		/*
		if($username != '' && $password != '') {
			// login with legacy username and password
			$q = "SELECT * FROM users_users WHERE username = '".$username."' AND enabled = 1";
			
			$this->objDA->query($q);
			
			$user_info	= $this->objDA->returnArray();
			
			if($this->objDA->numRows() > 0) {
				$password	= md5($password);
				
				if($user_info[0]['password'] == $password) {
					session_start();
					
					$_SESSION['username']   = $username;
					$_SESSION['userid']	    = $user_info[0]['id'];
					
					$this->main();
				} else {
					$msg	= 'Invalid Login';
					$this->main();
				}
				
			} else {
				$msg	= 'Invalid Login';
				$this->main();
			}
		} elseif { **yubi auth here** }
		*/
		
		if($pin != '' && $yubikey != '') {
			$user	= $this->yubiAuth($yubikey,$pin);
			
			if($user !== false && is_array($user)) {
				session_start();
					
				$_SESSION['username']   = $user['username'];
				$_SESSION['userid']	    = $user['id'];
			}
			
			$this->main();
		} else {
			$this->main();
		}
	}
	
	/*
	* Method to process logout
	*/
	function logout() {
		if(isset($_SESSION)) {
			unset($_SESSION);
			
			if (isset($_COOKIE[session_name()])) {
			   setcookie(session_name(), '', time()-42000, '/');
			}
			
			session_destroy();
		}
		
		$msg	= 'Logged out.';
		$this->main();
	}
	
	/*
	* Method to display user profile
	*/
	function myProfile() {
		parent::__construct();
		
		$this->main();
	}
	
////////////////////////////////
// TASKS					  //
////////////////////////////////
	/*
	* Method to todo list items
	*/
	function taskList($showDisabled=false,$userid=0) {
		parent::__construct();
		
		if($showDisabled == '1')
			$showDisabled	= true;
		else
			$showDisabled	= false;
		
		$this->smarty->assign('menuSubSel','Tasks');
		$this->smarty->assign('block_title','My Tasks');
		
		// list users
		$q		= "SELECT * FROM users_users WHERE enabled = 1 ORDER BY lastname,firstname";
		$this->objDA->query($q);
		$r		= $this->objDA->returnArray();
		$users	= array();
		foreach($r as $key=>$value) {
			$users[$value['id']]	= $value['lastname'].', '.$value['firstname'].' ('.$value['username'].')';
		}
		$this->smarty->assign('users',$users);
		
		if($this->post) {
			$user	= $this->post['user'];
		} elseif($userid > 0) {
			$user	= $userid;
		} else {
			$user	= $_SESSION['userid'];
		}
		
		$tasks	= $this->getTasks($showDisabled,$user);
		$this->smarty->assign('tasks',$tasks);
		
		$this->smarty->assign('currentUser',$_SESSION['userid']);
		$this->smarty->assign('user',$user);
		$this->smarty->assign('selUser',$user);
		$this->smarty->assign('showDisabled',$showDisabled);
		
		$this->smarty->display('admin/taskList.tpl');
	}
	
	/*
	* Method to edit a chart of accounts account
	*	param		int		id			ID of task to edit (0 for new)
	*	param		int		userid		UserID of task to add new account for
	*/
	function taskEdit($id=0,$userid=0) {
		parent::__construct();
		
		$this->smarty->assign('menuSubSel','Tasks');
		
		if($userid == 0)
			$userid	= $_SESSION['userid'];
		
		// list users
		$q		= "SELECT * FROM users_users WHERE enabled = 1 ORDER BY lastname,firstname";
		$this->objDA->query($q);
		$r		= $this->objDA->returnArray();
		$users	= array();
		foreach($r as $key=>$value) {
			$users[$value['id']]	= $value['lastname'].', '.$value['firstname'].' ('.$value['username'].')';
		}
		$this->smarty->assign('users',$users);
		
		// tasks (parents)
		$t		= $this->getTasks(false,$userid);
		$tasks	= array(0 => '-- ROOT --');
		foreach($t as $r) {
			if($id > 0 && $r['id'] == $id)
				continue; // don't include current account
			
			$tasks[$r['id']]	= $r['name'];
		}
		$this->smarty->assign('tasks',$tasks);

		if($id > 0) {			
			// edit
			$this->smarty->assign('block_title','Edit Task');
			
			$q	= "SELECT * FROM tasks_tasks WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$r	= $this->objDA->returnArray();
			$r	= $r[0];
			
			if($r['private'] == 1 && $_SESSION['userid'] != $r['user']) {
				// access denied
				$this->taskList();
				return;
			}
			
			$this->smarty->assign('id',$r['id']);
			$this->smarty->assign('name',$r['name']);
			$this->smarty->assign('parent',$r['parent']);
			$this->smarty->assign('user',$r['user']);
			$this->smarty->assign('notes',$r['notes']);
			$this->smarty->assign('private',$r['private']);
			$this->smarty->assign('enabled',$r['enabled']);
			$this->smarty->assign('sort',$r['sort']);
		} else {
			// add
			$this->smarty->assign('id',0);
			$this->smarty->assign('block_title','Add Task');
			
			$this->smarty->assign('user',$userid);
			$this->smarty->assign('enabled',1);
		}
		
		$this->smarty->display('admin/taskEdit.tpl');
	}
	
	/*
	* Method to process editing a chart of accounts account
	*/
	function taskEditProcess() {
		parent::__construct();
		
		if($this->post) {
			$id				= $this->post['id'];
			$name			= $this->post['name'];
			$parent			= $this->post['parent'];
			$user			= $this->post['user'];
			$notes			= $this->post['notes'];
			$private		= $this->post['private'];
			$enabled		= $this->post['enabled'];
			
			if($enabled == 'checked')
				$enabled	= 0; // marked as "completed"
			else
				$enabled	= 1;
				
			if($private == 'checked')
				$private	= 1;
			else
				$private	= 0;
				
			if($id > 0) {
				// edit
				$q		= "SELECT * FROM tasks_tasks WHERE id = '".$id."'";
				$this->objDA->query($q);
				$task	= $this->objDA->returnArray($q);
				$task	= $task[0];
				
				if($task['private'] == 1 && $task['user'] != $_SESSION['userid']) {
					// access denied
					$this->smarty->assign('msg','Access denied.');
					$this->taskList();
					return;
				}
				
				if($task['user'] != $user) {
					// user changed, reset parent to 0
					$parent		= 0;
					
					// adjust sort to move to top
					$q			= "UPDATE tasks_tasks SET sort = sort+1 WHERE user = '".$user."' AND parent = 0";
					$this->objDA->query($q);
					$sort		= 1;
					
					$notifyUser	= true;
				} else {
					$sort	= $task['sort'];
				}
				
				$q	= "	UPDATE
							tasks_tasks
						SET
							name		= '".$name."',
							parent		= '".$parent."',
							user		= '".$user."',
							notes		= '".$notes."',
							private		= '".$private."',
							enabled		= '".$enabled."',
							sort		= '".$sort."'
						WHERE
							id			= '".$id."'";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Task Edited');
			} else {
				// add
				$q		= "SELECT * FROM tasks_tasks WHERE user = '".$user."' AND parent = '".$parent."' ORDER BY sort DESC LIMIT 1";
				$this->objDA->query($q);
				$r		= $this->objDA->returnArray();
				$sort	= $r[0]['sort']+1;
				
				
				$q	= "	INSERT INTO
							tasks_tasks
						VALUES
							(
								'',
								'".$name."',
								'".$parent."',
								'".$user."',
								'".$notes."',
								'".$private."',
								'".$enabled."',
								'".$sort."'
							)";
				$this->objDA->query($q);
				
				if($user != $_SESSION['userid']) {
					$notifyUser	= true;
				} else {
					$notifyUser	= false;
				}
				
				$this->smarty->assign('msg','Task Added');
			}
			
			if($notifyUser === true) {
				// send email to user to notify of new task
				// assigned by
				$q			= "SELECT * FROM users_users WHERE id = '".$_SESSION['userid']."'";
				$this->objDA->query($q);
				$fromUser	= $this->objDA->returnArray();
				$fromUser	= $fromUser[0];
				$fromEmail	= $fromUser['email'];
				
				// assigned to
				$q			= "SELECT * FROM users_users WHERE id = '".$user."'";
				$this->objDA->query($q);
				$toUser		= $this->objDA->returnArray();
				$toUser		= $toUser[0];
				$toEmail	= $toUser['email'];
				
				$subject 	= '6PS :: New Task Assigned';
				$headers 	= 'From: '.$fromEmail.'' . "\r\n";
				
				$this->smarty->assign('name',$name);
				$this->smarty->assign('notes',$notes);
				$assignedBy	= $fromUser['firstname'].' '.$fromUser['lastname'].' ('.$fromUser['username'].')';
				$this->smarty->assign('assignedBy',$assignedBy);
				$taskURL	= __SITE_WWW_ROOT.'/module/AdminCP/taskEdit.php?'.$id;
				$this->smarty->assign('taskURL',$taskURL);
				
				$message	= $this->smarty->fetch('admin/taskEmailNotify.tpl');
				
				mail($toEmail, $subject, $message, $headers);
			}
		}
		
		header('Location: '.__SITE_WWW_ROOT.'/module/AdminCP/taskList.php');
		return;
	}
	
	/*
	* Method to mark tasks completed from taskList()
	*/
	function taskMarkCompleted() {
		parent::__construct();
		
		if($this->post) {
			$tasks			= $this->sanitize($this->post['tasks']);
			$taskids			= $this->sanitize($this->post['taskids']);
			$user			= $this->sanitize($this->post['user']);
			$showDisabled	= $this->sanitize($this->post['showDisabled']);
			
			if($showDisabled == '1' || $showDisabled == true) {
				$showDisabled	= true;
			} else {
				$showDisabled	= false;
			}
			
			foreach($taskids as $taskid) {
				if($tasks[$taskid] == 'checked') {
					// task completed
					$enabled	= 0;
				} else {
					// task incomplete
					$enabled	= 1;
				}
				
				$q	= "	UPDATE
							tasks_tasks
						SET
							enabled 	= '".$enabled."'
						WHERE
							id			= '".$taskid."'";
				$this->objDA->query($q);
			}
			
		} else {
			$userid			= 0;
			$showDisabled	= false;
		}
		
		$this->taskList(false,$userid);
	}
	
	/*
	* Method to sort task node up one
	*/
	function taskSortUp($id=0) {
		parent::__construct();
		
		$this->fixTaskSort(); // fix any sorting issues
		
		$q		= "SELECT * FROM tasks_tasks WHERE id = '".$id."'";
		$this->objDA->query($q);
		$node	= $this->objDA->returnArray();
		$node	= $node[0];
		
		if($node['sort'] == 1) {
			// error
			$this->smarty->assign('msg','An error occured (001)');
			$this->taskList();
			return;
		}
		
		$q			= "SELECT * FROM tasks_tasks WHERE parent = '".$node['parent']."' AND sort = '".($node['sort'] - 1)."'";
		$this->objDA->query($q);
		$nodeAbove	= $this->objDA->returnArray();
		$nodeAbove	= $nodeAbove[0];
		
		// move node above down one
		$q	= "UPDATE tasks_tasks SET sort = sort + 1 WHERE id = '".$nodeAbove['id']."' LIMIT 1";
		$this->objDA->query($q);
		
		// move this node up one
		$q	= "UPDATE tasks_tasks SET sort = sort - 1 WHERE id = '".$id."' LIMIT 1";
		$this->objDA->query($q); 
		
		$this->smarty->assign('msg','Task Moved Up');
		$this->taskList();
	}
	
	/*
	* Method to sort task node down one
	*/
	function taskSortDown($id=0) {
		parent::__construct();
		
		$this->fixTaskSort(); // fix any sorting issues
		
		$q		= "SELECT * FROM tasks_tasks WHERE id = '".$id."'";
		$this->objDA->query($q);
		$node	= $this->objDA->returnArray();
		$node	= $node[0];
		
		$q			= "SELECT * FROM tasks_tasks WHERE parent = '".$node['parent']."' AND sort = '".($node['sort'] + 1)."'";
		$this->objDA->query($q);
		$nodeBelow	= $this->objDA->returnArray();
		
		// since there is nothing below us, cannot move down
		if(count($nodeBelow) < 1) {
			$this->smarty->assign('msg','An error occured (002)');
			$this->taskList();
			return;
		}
		
		$nodeBelow	= $nodeBelow[0];
		

		$q	= "UPDATE tasks_tasks SET sort = sort - 1 WHERE id = '".$nodeBelow['id']."' LIMIT 1";
		$this->objDA->query($q);
		
		// move this node up one
		$q	= "UPDATE tasks_tasks SET sort = sort + 1 WHERE id = '".$id."' LIMIT 1";
		$this->objDA->query($q); 
		
		$this->smarty->assign('msg','Task Moved Down');
		$this->taskList();
	}

//////////////////////////////////
// TIME ENTRIES					//
//////////////////////////////////
	/*
	* Method to list time entries
	*/
	function timeList($userid=0) {
		parent::__construct();
		
		$this->smarty->assign('menuSubSel','Time Entries');
		$this->smarty->assign('block_title','Time Entries');
		
		if($userid == 0) {
			$userid	= $_SESSION['userid'];
		}
		
		// paginate object
		$paginate	= new SmartyPaginate();
		$paginate->connect();
		$paginate->setLimit(10);
		$paginate->setURL('timeList.php');
		
		$start		= $paginate->getCurrentIndex();
		$limit		= $paginate->getLimit();
		
		// get total
		$q	= "SELECT * FROM time_entries WHERE user_id = '".$userid."' ORDER BY bill_date DESC, id DESC";
		$this->objDA->query($q);
		$paginate->setTotal($this->objDA->numRows());
		
		// get subset for display
		$q	= "SELECT * FROM time_entries WHERE user_id = '".$userid."' ORDER BY bill_date DESC, id DESC LIMIT ".$start.",".$limit;
		$this->objDA->query($q);
		$entries	= $this->objDA->returnArray();
		$this->smarty->assign('entries',$entries);
		
		$paginate->assign($this->smarty);
		$this->smarty->display('admin/timeList.tpl');
	}
	
	/*
	* Method to edit time entry
	*/
	function timeEdit($id=0) {
		parent::__construct();
		
		$this->smarty->assign('menuSubSel','Time Entries');
		
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
				$priceoverride	= '0.00';
			else
				$priceoverride	= $r['priceoverride'];
				
			if($r['rate'] == 0)
				$rate	= '';
			else
				$rate	= $r['rate'];
			
			$this->smarty->assign('id',$id);
			$this->smarty->assign('selUser',$r['billing_system'].'-'.$r['billing_userid']);
			$this->smarty->assign('bill_date',$r['bill_date']);
			$this->smarty->assign('duration',$r['duration']);
			$this->smarty->assign('rate',$rate);
			$this->smarty->assign('description',$r['description']);
			$this->smarty->assign('afterhours',$r['afterhours']);
			$this->smarty->assign('afterhoursfee',$r['afterhoursfee']);
			$this->smarty->assign('priceoverride',$priceoverride);
		} else {
			// add
			$this->smarty->assign('id',0);
			$this->smarty->assign('block_title','Add Time Entry');
			
			$this->smarty->assign('user',$userid);
			$this->smarty->assign('afterhours',0);
			$this->smarty->assign('afterhoursfee',0);
			$this->smarty->assign('priceoverride','0.00');
		}
		
		$this->smarty->display('admin/timeEdit.tpl');
	}
	
	/*
	* Method to process time entry
	*/
	function timeEditProcess() {
		parent::__construct();
		
		if($this->post) {
			$id				= $this->post['id'];
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
							user_id			= '".$userid."',
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
			} else {
				// add
				$q	= "	INSERT INTO
							time_entries
						VALUES
							(
								'',
								'".$userid."',
								'".$billing_system."',
								'".$billing_userid."',
								'".$entry_date."',
								'".$bill_date."',
								'".$duration."',
								'".$rate."',
								'".$description."',
								'".$afterhours."',
								'".$afterhoursfee."',
								'".$priceoverride."',
								0
							)";
				$this->objDA->query($q);
				
				
				
				$this->smarty->assign('msg','Task Added');
			}
		}
		
		header('Location: '.__SITE_WWW_ROOT.'/module/AdminCP/timeList.php');
		return;
	}
	
	/*
	* Method to delete a time entry
	*/
	function timeDelete($id=0) {
		parent::__construct();
		
		if($id > 0) {
			$q	= "SELECT * FROM time_entries WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$r	= $this->objDA->returnArray();
			$r	= $r[0];
			
			if($r['user_id'] == $_SESSION['userid']) {
				$q	= "DELETE FROM time_entries WHERE id = '".$id."' LIMIT 1";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Entry deleted');
			} else {
				$this->smarty->assign('msg','Access denied.');
			}
		}
		
		$this->timeList();
	}


//////////////////////////////////
// INTERNAL						//
//////////////////////////////////
	/*
	* Method to build a array of chart of accounts
	*/
	protected function getTasks($showDisabled=false,$user=0,$parent=0,$indent=0,&$nodes=array()) {
		$pad	= '';
		for($i=0;$i<$indent;$i++)
			$pad	.= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		
		$where	= '';
		
		if($showDisabled != true)
			$where	.= 'AND enabled = 1 ';
		
		if($_SESSION['userid'] != $user)
			$where	.= 'AND private = 0';
		
		
		$q	= "SELECT * FROM tasks_tasks WHERE user = '".$user."' ".$where." AND parent = '".$parent."' ORDER BY sort";
		$this->objDA->query($q);

		$r		= $this->objDA->returnArray();
		$count	= count($r);
		
		for($i=0;$i<$count;$i++) {
			$node	= array();
			
			$node['id']				= $r[$i]['id'];
			$node['name']			= $pad.$r[$i]['name'];
			$node['parent']			= $r[$i]['parent'];
			$node['user']			= $r[$i]['user'];
			$node['notes']			= $r[$i]['notes'];
			$node['enabled']		= $r[$i]['enabled'];
			$node['sort']			= $r[$i]['sort'];
			
			if($i == 0)
				$node['navUp']	= false;
			else
				$node['navUp']	= true;
			
			if(($i+1)<$count)
				$node['navDown']	= true;
			else
				$node['navDown']	= false;
			
			array_push($nodes,$node);

			$this->getTasks($showDisabled,$user,$r[$i]['id'],($indent+1),$nodes);
		}
		
		return $nodes;
	}
	
	/*
	* Method to re-sort task lists
	*/
	protected function fixTaskSort($root=0) {
		$q	= "SELECT * FROM tasks_tasks WHERE parent = '".$root."' AND user = '".$_SESSION['userid']."' ORDER BY `sort`";
		$this->objDA->query($q);
		
		$data	= $this->objDA->returnArray();
		$count	= count($data);
		
		if($count > 0) {
			for($i=0;$i<$count; $i++) {
				$q	= "UPDATE tasks_tasks SET `sort` = '".($i+1)."' WHERE id = '".$data[$i]['id']."'";
				$this->objDA->query($q);
				
				$this->fixTaskSort($data[$i]['id']);
			}
		}
	}
}
?>