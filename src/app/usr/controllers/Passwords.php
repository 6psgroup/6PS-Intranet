<?php
require_once __SITE_FS_ROOT . '/app/usr/controllers/AdminCP_adminMenu.php';
require_once __SITE_FS_ROOT . '/app/lib/YubiAuth/Yubico.php';

class Passwords extends AdminCP_adminMenu implements IModule {
	/*
	* Smarty object ("view")
	*/
	var $smarty;
	
	/*
	* POST variable
	*/
	var $post;
	
	/*
	* Section ID
	*/
	var $sectionID	= 5;
	
	function setSmarty($smarty) {
		global $msg;
		
		$smarty->clear_all_assign();
		$this->smarty	= $smarty;
		
		$this->smarty->assign('page_title','6PS Intranet :: Passwords');
		$this->smarty->assign('msg',$msg);
		$this->generateSubMenu(4);
		$this->smarty->assign('menuMainSel','Passwords');
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
		$this->passList();
	}
	
////////////////////////////////
// PASSWORDS                  //
////////////////////////////////
	
	/*
	* Method to list passwords in a category
	*/
	function passList() {
		global $msg;
		
		$this->smarty->assign('menuSubSel','Passwords');
		$this->smarty->assign('block_title','Passwords');
		
		$r			= $this->catBuildChildrenRoot('-',true,1);
		$catTree	= array();
		
		foreach($r as $key=>$value) {
			$catTree[$value['id']]	= $value['cat'];
		}
			
		$this->smarty->assign('catTree',$catTree);
		
		if($_POST['cat'] != NULL) {
			$root	= $_POST['cat'];
		} else {
			$root	= '1';
		}
		
		$this->smarty->assign('selCat',$root);
		
		$q	= "SELECT * FROM pass_passwords WHERE cat = '".$root."'";
		$this->objDA->query($q);
		
		$passwords	= $this->objDA->returnArray();
		$this->smarty->assign('passwords',$passwords);
		
		$this->smarty->display('passwords/passList.tpl');
	}
	
	/*
	* Method to add or edit a password
	*/
	function passEdit($id=0,$selCat=NULL) {
		$this->smarty->assign('menuSubSel','Passwords');
		
		if($this->post)
			$id		= $this->sanitize($this->post['id']);
		
		$this->smarty->assign('id',$id);
		
		if($id > 0) {
			// edit
			if($_POST) {
				// decrypt
				$verify_pin		= $this->sanitize($this->post['verify_pin']);
				$verify_yubi	= $this->sanitize($this->post['verify_yubi']);
		
				$this->smarty->assign('block_title','Decrypted Data');
				
				$r			= $this->catBuildChildrenRoot();
				$catTree	= array();
				foreach($r as $key=>$value) {
					$catTree[$value['id']]	= $value['cat'];
				}
				$this->smarty->assign('catTree',$catTree);
				
				
				// verify yubikey
				if($verify_pin != '' && $verify_yubi != '') {
					$user	= $this->yubiAuth($verify_yubi,$verify_pin);
					
					if($user !== false && is_array($user)) {
						$userID		= $_SESSION['userid'];
						$yubiID		= substr($verify_yubi,0,12);
						$keyUser	= $yubiID.$verify_pin;
						$keyUser	= md5($keyUser);
						
						$q	= "SELECT `key` FROM pass_keys WHERE user_id = '".$userID."'";
						$this->objDA->query($q);
						
						$r	= $this->objDA->returnArray();
						$r	= $r[0];
						
						// decrypt master key
						$keyMaster	= $this->passDecrypt($r['key'],$keyUser);
					} else {
						$this->smarty->assign('msg','Unable to verify');
						$this->passList();
						return false;
					}
				} else {
					$this->smarty->assign('msg','Unable to verify');
					$this->passList();
					return false;
				}
				
				$q	= "SELECT * FROM pass_passwords WHERE id = '".$id."'";
				$this->objDA->query($q);
				
				$r	= $this->objDA->returnArray();
				$r	= $r[0];
				
				if($r['username'] != '')
					$dUsername	= $this->passDecrypt($r['username'],$keyMaster);
				else
					$Username	= '';
				
				if($r['password'] != '')
					$dPassword	= $this->passDecrypt($r['password'],$keyMaster);
				else
					$dPassword		= '';
				
				if($r['notes'] != '')
					$dNotes		= $this->passDecrypt($r['notes'],$keyMaster);
				else
					$dNotes		= '';
				
				$dUsername	= stripslashes(rtrim($dUsername, "\0"));
				$dPassword	= stripslashes(rtrim($dPassword, "\0"));
				$dNotes		= stripslashes(rtrim($dNotes, "\0"));
				
				$name		= $this->unsanitize($r['name']);
				$dUsername	= $this->unsanitize($dUsername);
				$dPassword	= $this->unsanitize($dPassword);
				$dNotes		= $this->unsanitize($dNotes);
				
				
				$this->smarty->assign('name',$name);
				$this->smarty->assign('selCat',$r['cat']);
				$this->smarty->assign('username',$dUsername);
				$this->smarty->assign('password',$dPassword);
				$this->smarty->assign('notes',$dNotes);
				
				$this->smarty->display('passwords/passEdit.tpl');
			
			} else {
				// prompt for PIN and yubikey
				$this->smarty->assign('block_title','Decrypt Data');
				
				$this->smarty->display('passwords/passUnlock.tpl');
			}
		} else {
			// add
			$this->smarty->assign('block_title','Add New Password');
			
			$r		= $this->catBuildChildrenRoot();
			$catTree	= array();
			
			foreach($r as $key=>$value) {
				$catTree[$value['id']]	= $value['cat'];
			}

                        $this->smarty->assign('selCat',$selCat);
			$this->smarty->assign('catTree',$catTree);
			$this->smarty->assign('id',0);
			
			
			$this->smarty->display('passwords/passEdit.tpl');
		}
	}
	
	/*
	* Method to process editing or adding a password
	*/
	function passEditProcess() {
		$id				= $this->sanitize($this->post['id']);
		$cat			= $this->sanitize($this->post['cat']);
		$name			= $this->sanitize($this->post['name']);
		$username		= $this->sanitize($this->post['username']);
		$password		= $this->sanitize($this->post['password']);
		$password2		= $this->sanitize($this->post['password2']);
		$password3		= $this->sanitize($this->post['password3']);
		$notes			= $this->sanitize($this->post['notes']);
		$verify_pin		= $this->sanitize($this->post['verify_pin']);
		$verify_yubi	= $this->sanitize($this->post['verify_yubi']);
		
		// verify yubikey
		if($verify_pin != '' && $verify_yubi != '') {
			$user	= $this->yubiAuth($verify_yubi,$verify_pin);
			
			if($user !== false && is_array($user)) {
				$userID		= $_SESSION['userid'];
				$yubiID		= substr($verify_yubi,0,12);
				$keyUser	= md5($yubiID.$verify_pin);
				
				$q	= "SELECT `key` FROM pass_keys WHERE user_id = '".$userID."'";
				$this->objDA->query($q);
				
				$r	= $this->objDA->returnArray();
				$r	= $r[0];
				
				// decrypt master key
				$keyMaster	= $this->passDecrypt($r['key'],$keyUser);
			} else {
				$this->smarty->assign('msg','Unable to verify');
				$this->passList();
				return false;
			}
		} else {
			$this->smarty->assign('msg','Unable to verify');
			$this->passList();
			return false;
		}
		
		if($id > 0) {
			// edit
			if($password2 != '' && $password2 == $password3) {
				$password	= $password2;
				$ePassword	= $this->passEncrypt($password,$keyMaster);
				$ePassword	= $this->objDA->real_escape_string($ePassword);
				
				$password	= "password = '".$ePassword."',";
			} else {
				$password	= '';
			}
			
			if($username != '') {
				$eUsername	= $this->passEncrypt($username,$keyMaster);
				$eUsername	= $this->objDA->real_escape_string($eUsername);
			} else {
				$eUsername	= '';
			}
			
			if($notes != '') {
				$eNotes		= $this->passEncrypt($notes,$keyMaster);
				$eNotes		= $this->objDA->real_escape_string($eNotes);
			} else {
				$eNotes		= '';
			}
			
			$q	= "	UPDATE
						pass_passwords
					SET
						cat			= '".$cat."',
						name		= '".$name."',
						username	= '".$eUsername."',
						".$password."
						notes		= '".$eNotes."'
					WHERE
						id			= '".$id."'";
			$this->objDA->query($q);
			
			$this->smarty->assign('msg','Password edited.');
		} else {
			// add
			if($password2 == $password3) {
				$password	= $password2;
			} else {
				$this->smarty->assign('msg','Passwords do not match');
				$this->passList();
				return false;
			}
			
			if($username != '') {
				$eUsername	= $this->passEncrypt($username,$keyMaster);
				$eUsername	= $this->objDA->real_escape_string($eUsername);
			} else {
				$eUsername	= '';
			}
			
			if($password != '') {
				$ePassword	= $this->passEncrypt($password,$keyMaster);
				$ePassword	= $this->objDA->real_escape_string($ePassword);
			} else {
				$ePassword	= '';
			}
			
			if($notes != '') {
				$eNotes		= $this->passEncrypt($notes,$keyMaster);
				$eNotes		= $this->objDA->real_escape_string($eNotes);
			} else {
				$eNotes		= '';
			}
			
			$q	= "	INSERT INTO
						pass_passwords
					VALUES
						(
							'',
							'".$cat."',
							'".$name."',
							'".$eUsername."',
							'".$ePassword."',
							'".$eNotes."'
						)";
			$this->objDA->query($q);
			
			$this->smarty->assign('msg','Password added.');
		}
		
		$this->passList();
	}
	
	/*
	* Method to delete password
	*/
	function passDelete($id=0) {
		if($id > 0) {
			$q	= "DELETE FROM pass_passwords WHERE id = '".$id."' LIMIT 1";
			$this->objDA->query($q);
			
			$this->smarty->assign('msg','Password deleted.');
		}
		
		$this->passList();
	}

////////////////////////////////
// CATEGORIES                 //
////////////////////////////////
	
	/*
	* Method to list password categories
	*/
	function catList() {
		global $msg;
		
		$this->smarty->assign('menuSubSel','Categories');
		$this->smarty->assign('block_title','Categories');
		
		$catTree	= $this->catBuildChildrenRoot(' ',false);
		$this->smarty->assign('catTree',$catTree);
		
		$this->smarty->display('passwords/catList.tpl');
	}
	
	/*
	* Method to sort navigation node up one
	*/
	function catSortUp($id=0) {
		$q		= "SELECT * FROM pass_cats WHERE id = '".$id."'";
		$this->objDA->query($q);
		$node	= $this->objDA->returnArray();
		$node	= $node[0];
		
		if($node['sort'] == 1) {
			// error
			$this->smarty->assign('msg','ERROR: Navigation NOT Moved Up');
			$this->navigation();
			return;
		}
		
		$q			= "SELECT * FROM pass_cats WHERE parent = '".$node['parent']."' AND sort = '".($node['sort'] - 1)."'";
		$this->objDA->query($q);
		$nodeAbove	= $this->objDA->returnArray();
		$nodeAbove	= $nodeAbove[0];
		
		// move node above down one
		$q	= "UPDATE pass_cats SET sort = sort + 1 WHERE id = '".$nodeAbove['id']."' LIMIT 1";
		$this->objDA->query($q);
		
		// move this node up one
		$q	= "UPDATE pass_cats SET sort = sort - 1 WHERE id = '".$id."' LIMIT 1";
		$this->objDA->query($q); 
		
		$this->smarty->assign('msg','Category Moved Up');
		$this->catList();
	}
	
	/*
	* Method to sort navigation node down one
	*/
	function catSortDown($id=0) {
		$q		= "SELECT * FROM pass_cats WHERE id = '".$id."'";
		$this->objDA->query($q);
		$node	= $this->objDA->returnArray();
		$node	= $node[0];
		
		$q			= "SELECT * FROM pass_cats WHERE parent = '".$node['parent']."' AND sort = '".($node['sort'] + 1)."'";
		$this->objDA->query($q);
		$nodeBelow	= $this->objDA->returnArray();
		
		// since there is nothing below us, cannot move down
		if(count($nodeBelow) < 1) {
			$this->smarty->assign('msg','ERROR: Navigation NOT Moved Down');
			$this->navigation();
			return;
		}
		
		$nodeBelow	= $nodeBelow[0];
		

		$q	= "UPDATE pass_cats SET sort = sort - 1 WHERE id = '".$nodeBelow['id']."' LIMIT 1";
		$this->objDA->query($q);
		
		// move this node up one
		$q	= "UPDATE pass_cats SET sort = sort + 1 WHERE id = '".$id."' LIMIT 1";
		$this->objDA->query($q); 
		
		$this->smarty->assign('msg','Category Moved Down');
		$this->catList();
	}
	
	/*
	* Method to add or edit a category
	*/
	function catEdit($id=0) {
		global $msg;
		
		$this->smarty->assign('menuSubSel','Categories');
		
		$r			= $this->catBuildChildrenRoot();
		$catTree	= array();
		
		foreach($r as $key=>$value) {
			$catTree[$value['id']]	= $value['cat'];
		}
		
		if($id > 0) {
			// edit
			$q	= "SELECT * FROM pass_cats WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$r	= $this->objDA->returnArray();
			$r	= $r[0];
			
			// Remove node being edited from parent list (can't make a node it's own parent)
			foreach($catTree as $key=>$value) {
				if($key == $id)
					unset($catTree[$key]);
			}
			
			$this->smarty->assign('block_title','Edit Category');
			$this->smarty->assign('selParent',$r['parent']);
			$this->smarty->assign('cat',$r['cat']);
		} else {
			// add
			$this->smarty->assign('block_title','Add Category');
		}
		
		$this->smarty->assign('catTree',$catTree);
		$this->smarty->assign('id',$id);
		
		$this->smarty->display('passwords/catEdit.tpl');
	}
	
	/*
	* Method to process a category node
	*/
	function catEditProcess() {
		if($this->post) {
			$cat	= $this->sanitize($this->post['cat']);
			$parent	= $this->sanitize($this->post['parent']);
			$id		= $this->sanitize($this->post['id']);
			
			if($id > 0) {
				// edit
				$q	= "	UPDATE
							pass_cats
						SET
							cat		= '".$cat."',
							parent	= '".$parent."'
						WHERE
							id		= '".$id."'";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Category edited.');
			} else {
				// add
				// Find highest sort value, then add one for current sort
				$q	= "SELECT sort FROM pass_cats WHERE parent = '".$parent."' ORDER BY sort DESC";
				$this->objDA->query($q);
				
				$r		= $this->objDA->returnArray();
				$r		= $r[0];
				$sort	= $r['sort'] + 1;
				
				$q	= "INSERT INTO pass_cats VALUES ('','".$parent."','".$cat."','".$sort."',1)";
				$this->objDA->query($q);
				
				$this->smarty->assign('msg','Category added.');
			}
		}
		
		$this->catList();
	}
	
	/*
	* Method to delete (disable) a category
	*/
	function catDelete($id=0) {
		if($id > 0) {
			$q	= "UPDATE pass_cats SET active = 0 WHERE id = '".$id."'";
			$this->objDA->query($q);
			
			$this->smarty->assign('msg','Category deleted.');
		}
		
		$this->catList();
	}
	
////////////////////////////////
// INTERNAL                   //
////////////////////////////////
	/*
	* Methdo to build array of children nodes from root
	*/
	protected function catBuildChildrenRoot($spacer='-',$includeRoot=true,$iteration=0) {
		$q	= "SELECT * FROM pass_cats WHERE parent = '0' ORDER BY sort";
		$this->objDA->query($q);
		
		$nodes		= $this->objDA->returnArray();
		$navTree	= array();
		
		$count		= count($nodes);
		
		for($i=0;$i<$count;$i++) {
			if($nodes[$i]['id'] == $id)
				continue;
				
			if($i == 0)
				$nodes[$i]['navUp']	= false;
			else
				$nodes[$i]['navUp']	= true;
			
			if(($i+1)<$count)
				$nodes[$i]['navDown']	= true;
			else
				$nodes[$i]['navDown']	= false;
			
			if($includeRoot === true) {
				$nodes[$i]['cat']	= $spacer.'&nbsp;'.$nodes[$i]['cat'];
				
				array_push($navTree,$nodes[$i]);
			}
			
			$this->catBuildChilden($nodes[$i]['id'],$navTree,$iteration,$spacer);
		}
		
		
		return $navTree;
	}
	/*
	* Method to build array of children nodes to $navTree (passed as reference)
	*/
	protected function catBuildChilden($parent,&$navTree,&$iteration=1,$spacer='-') {
		$q	= "SELECT * FROM pass_cats WHERE parent = '".$parent."' AND active = 1 ORDER BY sort";
		$this->objDA->query($q);
		
		$nodes		= $this->objDA->returnArray();
		
		for($i=0;$i<$iteration;$i++) {
				$indent	.= '&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		
		for($i=0;$i<$iteration;$i++) {
			$indent	.= $spacer;
		}
		
		$indent	.= '&nbsp;';
		
		$count	= count($nodes);
		
		for($i=0;$i<$count;$i++) {
			if($i == 0)
				$nodes[$i]['navUp']	= false;
			else
				$nodes[$i]['navUp']	= true;
				
			if(($i+1)<$count)
				$nodes[$i]['navDown']	= true;
			else
				$nodes[$i]['navDown']	= false;
				
			$nodes[$i]['cat']		= $indent . $nodes[$i]['cat'];
			
			array_push($navTree,$nodes[$i]);
			
			$iteration++;
			$this->catBuildChilden($nodes[$i]['id'],$navTree,$iteration,$spacer);
			$iteration--;
		}
		
		return;
	}
	
	/*
	* Method to delete nested children under the specicifed node
	*/
	protected function catDeleteChildren($parent=0) {
		if($parent == 0)
			return;
			
		$q	= "SELECT * FROM pass_cats WHERE parent = '".$parent."'";
		$this->objDA->query($q);
		$nodes	= $this->objDA->returnArray();
		
		foreach($nodes as $node) {
			$this->catDeleteChildren($node['id']);
			$q	= "DELETE FROM pass_cats WHERE id = '".$node['id']."' LIMIT 1";
			$this->objDA->query($q);
		}
		
		return;
	}
}
?>