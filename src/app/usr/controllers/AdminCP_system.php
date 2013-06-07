<?php
require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';
require_once __SITE_FS_ROOT . '/app/lib/YubiAuth/Yubico.php';

class AdminCP_system extends AdminCP_adminMenu implements IModule {
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
		
		$this->smarty->assign('page_title','6PS Intranet :: System');
		$this->smarty->assign('msg',$msg);
		$this->generateSubMenu(2);
		$this->smarty->assign('menuMainSel','System');
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
		parent::__construct();
	}
	
	/*
	* Main
	*/
	function main() {
		$this->settings();
	}
	
	/*
	* Settings
	*/
	function settings() {
		global $msg;
		
		// logged in, show welcome page
		$this->smarty->assign('menuSubSel','Settings');

		$this->smarty->assign('block_title','Settings');
		
		$this->smarty->display('admin/adminSystem_Home.tpl');
	}
	
	/*
	* Method to process login request
	*/
	function loginProcess() {
		global $msg;
		
		$username	= $this->post['username'];
		$password	= $this->post['password'];
		
		$q = "SELECT * FROM users_users WHERE username = '".$username."'";
		
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
		$this->main();
	}
	
	/*
	* Method to display users
	*/
	function users() {
		// logged in, show welcome page
		$this->smarty->assign('menuSubSel','Users');
		$this->smarty->assign('block_title','Users');
		
		$q	= "SELECT * FROM users_users WHERE enabled = 1 ORDER BY id";
		$this->objDA->query($q);
		$users	= $this->objDA->returnArray();
		
		$this->smarty->assign('users',$users);
		
		$this->smarty->display('admin/adminSystem_Users.tpl');
	}
	
	/*
	* Method to edit a user
	*/
	function userEdit($id=0) {
		if($id > 0) {
			$this->smarty->assign('menuSubSel','Users');
			$this->smarty->assign('block_title','Edit User');
		
			$q	= "SELECT * FROM users_users WHERE id = '".$id."'";
			$this->objDA->query($q);
			$user	= $this->objDA->returnArray();
			$user	= $user[0];
			
			$this->smarty->assign('action','userEditProcess.php');
			
			$this->smarty->assign('id',$user['id']);
			$this->smarty->assign('username',$user['username']);
			$this->smarty->assign('lastname',$user['lastname']);
			$this->smarty->assign('firstname',$user['firstname']);
			$this->smarty->assign('email',$user['email']);
			
			if($user['enabled'] == true)
				$enabled	= 'checked';
			else
				$enabled	= '';
			
			$this->smarty->assign('enabled',$enabled);
			
			// user permissions
			$q	= "	SELECT
						s.id AS section_id,
						s.url,
						s.name,
						s.newwindow
					FROM
						users_sections s
					ORDER BY
						s.sort";
			$this->objDA->query($q);
			
			$sections		= $this->objDA->returnArray();
			$permissions	= array();
			
			foreach($sections as $section) {
				$permissions[$section['section_id']]	= ' '.$section['name'];
			}
			
			$q	= "	SELECT
						section_id
					FROM
						users_permissions p
					WHERE
						p.user_id = '".$id."'";
			$this->objDA->query($q);
			
			$sections		= $this->objDA->returnArray();
			$selPermissions	= array();
			
			foreach($sections as $section) {
				array_push($selPermissions,$section['section_id']);
			}			
			
			$this->smarty->assign('permissions',$permissions);
			$this->smarty->assign('selPermissions',$selPermissions);
			
			$this->smarty->display('admin/adminSystem_UserEdit.tpl');
		} else {
			$this->users();
		}
	}
	
	/*
	* Method to process edit user submission
	*/
	function userEditProcess() {
		if($this->post) {
			$verify	= $this->yubiAuth($this->post['verify_yubi'],$this->post['verify_pin']);
			
			if($yubi != '')
				$user	= $this->yubiAuth($this->post['yubi'],$this->post['pin']);
			else
				$user	= array();
			
			if(($verify !== false && is_array($verify)) && ($user !== false && is_array($user))) {
				if($this->post['enabled'] == 'checked')
					$enabled	= true;
				else
					$enabled	= false;
				
				if($this->post['password1'] != '') {
					if($this->post['password1'] == $this->post['password2']) {
						// passwords set and match
						$password	= 'password	= MD5(\''.$this->post['password1'].'\'),';
					} else {
						$this->smarty->assign('msg','Passwords do not match.');
						$this->userEdit($this->post['id']);
						return;
					}
				} else {
					$password	= '';
				}
				
				if($this->post['pin1'] != '') {
					if($this->post['pin1'] == $this->post['pin2']) {
						// passwords set and match
						$yubi_pin	= 'yubi_pin = MD5(\''.$this->post['pin1'].'\'),';
					} else {
						$this->smarty->assign('msg','PINs do not match.');
						$this->userEdit($this->post['id']);
						return;
					}
				} else {
					$yubi_pin	= '';
				}
				
				$yubi_id	= substr($this->post['yubi'],0,12);
				
				if($yubi_id != '') {
					$yubi_id	= "yubi_id = '".$yubi_id."',";
				}
				
				$q	= "	UPDATE
							users_users
						SET
							username	= '".$this->post['username']."',
							".$password."
							".$yubi_id."
							".$yubi_pin."
							firstname	= '".$this->post['firstname']."',
							lastname	= '".$this->post['lastname']."',
							email		= '".$this->post['email']."',
							enabled		= '".$enabled."'
						WHERE
							id			= '".$this->post['id']."'
						LIMIT 1";
				
				$this->objDA->query($q);
				
				// update permissions
				$q	= "DELETE FROM users_permissions WHERE user_id = '".$this->post['id']."'";
				$this->objDA->query($q);
				
				$permissions	= $this->post['permissions'];
				
				foreach($permissions as $permission) {
					$q	= "INSERT INTO users_permissions VALUES ('','".$permission."','".$this->post['id']."')";
					$this->objDA->query($q);
				}
				
				if($this->post['yubi'] != '' || $this->post['pin1'] != '') {
					// decrypt user's master password key hash and re-encrypt with new PIN and Yubi
					$verify_pin		= $this->post['verify_pin'];
					$verify_yubi	= $this->post['verify_yubi'];
					
					$userID		= $_SESSION['userid'];
					$yubiID		= substr($verify_yubi,0,12);
					$keyUser	= $yubiID.$verify_pin;
					$keyUser	= md5($keyUser);
					
					$q	= "SELECT `key` FROM pass_keys WHERE user_id = '".$userID."'";
					$this->objDA->query($q);
					
					if($this->objDA->numRows() > 0) {
						$r	= $this->objDA->returnArray();
						$r	= $r[0];
						

						$keyMaster	= $this->passDecrypt($r['key'],$keyUser);
						
						$yubiID		= substr($this->post['yubi'],0,12);
						$keyUser	= $yubiID.$this->post['pin1'];
						$keyUser	= md5($keyUser);
						
						$keyUserE	= $this->passEncrypt($keyMaster,$keyUser);
						
						$q	= "SELECT * FROM pass_keys WHERE user_id = '".$this->post['id']."'";
						$this->objDA->query($q);
						
						$q	= "	REPLACE INTO
									pass_keys
								VALUES
									(
										'',
										'".$this->post['id']."',
										'".$keyUserE."'
									)";
						$this->objDA->query($q);
					}
				}
				
				$this->smarty->assign('msg','User edited.');
			} else {
				$this->smarty->assign('msg','Unable to verify user');
			}
		}
		
		$this->generateSubMenu(2); // regenerate menu if changing current user
		$this->users();
	}
	
	/*
	* Method to add a user
	*/
	function userAdd() {
		$this->smarty->assign('menuSubSel','Users');
		$this->smarty->assign('block_title','Add User');

		$this->smarty->assign('action','userAddProcess.php');
		$this->smarty->assign('username',''); // smarty bug -- using global variable
		
		$q	= "	SELECT
					s.id AS section_id,
					s.url,
					s.name,
					s.newwindow
				FROM
					users_sections s
				ORDER BY
					s.sort";
		$this->objDA->query($q);
		
		$sections		= $this->objDA->returnArray();
		$permissions	= array();
		
		foreach($sections as $section) {
			$permissions[$section['section_id']]	= ' '.$section['name'];
		}
		
		$this->smarty->assign('permissions',$permissions);
		
		$this->smarty->display('admin/adminSystem_UserEdit.tpl');
	}
	
	/*
	* Method to process add user submission
	*/
	function userAddProcess() {
		if($this->post) {
			$verify	= $this->yubiAuth($this->post['verify_yubi'],$this->post['verify_pin']);
			
			if($verify !== false && is_array($verify))  {
				if($this->post['username'] == '') {
					$this->smarty->assign('msg','Username must be set.');
					$this->userAdd();
					return;
				}
				
				/*
				if($this->post['password1'] != '') {
					if($this->post['password1'] != $this->post['password2']) {
						$this->smarty->assign('msg','Passwords do not match.');
						$this->userAdd();
						return;
					}
				} else {
					$this->smarty->assign('msg','Password must be set.');
					$this->userAdd();
					return;
				}
				*/
				
				if($this->post['pin1'] != '') {
					if($this->post['pin1'] != $this->post['pin2']) {
						$this->smarty->assign('msg','PINs do not match.');
						$this->userAdd();
						return;
					}
				} else {
					$this->smarty->assign('msg','PIN must be set.');
					$this->userAdd();
					return;
				}
				
				if($this->post['enabled'] == 'checked')
					$enabled	= true;
				else
					$enabled	= false;
				
				$yubi_id	= substr($this->post['yubi'],0,12);
				
				
				$q	= "	INSERT INTO
							users_users
						VALUES
							(
								'',
								'".$this->post['username']."',
								MD5('".$this->post['password1']."'),
								'".$yubi_id."',
								MD5('".$this->post['pin1']."'),
								'".$this->post['firstname']."',
								'".$this->post['lastname']."',
								'".$this->post['email']."',
								'".$enabled."'
							)";
				
				$this->objDA->query($q);
				
				$userid			= $this->objDA->insertID();
				$permissions	= $this->post['permissions'];
				
				foreach($permissions as $permission) {
					$q	= "INSERT INTO users_permissions VALUES ('','".$permission."','".$userid."')";
					$this->objDA->query($q);
				}
				
				$this->smarty->assign('msg','User added.');
			}
		}
			
		$this->users();
	}
}
?>