<?php
class AdminCP_adminMenu {
	/*
	* Intilization vector for crypto
	*/
	var $iv = '5534063890133238';
	
	function __construct() {
		if(isset($_COOKIE['PHPSESSID'])) {
			session_start($_COOKIE['PHPSESSID']);
	
			if(count($_SESSION) < 1) {
				// invalid session
				session_destroy();
				unset($_SESSION);
				header('Location: '.__SITE_WWW_ROOT.'/');
				die;
			}
				
		} else {
			// no session, redirect to root
			header('Location: '.__SITE_WWW_ROOT.'/');
			die;
		}
	}
	
	/* Main menu generation
	*/
	private function generateMainMenu() {
		$userid		= $_SESSION['userid'];
		$menuMain	= array();
		
		if($userid > 0) {
			$q	= "	SELECT
						s.url,
						s.name,
						s.newwindow
					FROM
						users_sections s
						INNER JOIN users_permissions p ON s.id = p.section_id
					WHERE
						p.user_id = '".$userid."'
					ORDER BY
						s.sort";
			$this->objDA->query($q);
			
			$sections	= $this->objDA->returnArray();
			
			foreach($sections as $section) {
				$r	= array($section['url'],$section['name'],$section['newwindow']);
				array_push($menuMain,$r);
			}
			
			$this->smarty->assign('menuMain',$menuMain);
		}
	}
	
	/*
	* Sub menu generation
	*/
	protected function generateSubMenu($id=0) {
		$this->generateMainMenu();
		
		switch($id) {
			case 1: // home
				$menuSub	= array (
									array('main.php','Home'),
									array('taskList.php','Tasks'),
									array('timeList.php','Time Entries')
								);
				break;
			case 2: // system
				$menuSub	= array (
									array('settings.php','Settings'),
									array('users.php','Users')
								);
				break;
			case 3: // engineering
				$menuSub	= array (
									array('main.php','Dashboard'),
									array('ipList.php','IP Subnets')
								);
				break;
			case 4: // passwords
				$menuSub	= array (
									array('passList.php','Passwords'),
									array('catList.php','Categories')
								);
				break;
			case 5: // finance
				$menuSub	= array (
									array('main.php','Dashboard'),
									array('chartList.php','Chart of Accounts'),
									array('ledgerList.php','General Ledger'),
									array('journalEntry.php','Journal Entry'),
									array('reportList.php','Reports')
								);
				break;
			case 6: // assets
				$menuSub	= array(
									array('dashboard.php','Dashboard'),
									array('assetList.php','Assets'),
									array('catList.php','Categories')
								);
				break;
			case 7: // customers
				$menuSub	= array(
									array('custList.php','Customers'),
									array('invoiceList.php','Invoices'),
									array('paymentList.php','Payments')
								);
				break;
			case 10: // HR
				$menuSub	= array(
									array('timeList.php','Employee Time Entries')
								);
				break;
			case 11: // Vendors
				$menuSub	= array(
									array('vendorList.php','Vendors'),
									array('billList.php','Enter Bills'),
									array('billPay.php','Pay Bills'),
									array('poList.php','Purchase Orders')
								);
				break;
			default:
				$menuSub	= '';
				break;
		}
		
		$this->smarty->assign('menuSub',$menuSub);
		
		$this->generateFooter();
	}
	
	/*
	* Method to generate footer
	*/
	private function generateFooter() {
		if(file_exists('/usr/bin/svnversion')) {
			$buildVersion	= shell_exec('/usr/bin/svnversion '.__SITE_FS_ROOT);
		} else {
			$buildVersion	= 'UNKNOWN';
		}
		
		$this->smarty->assign('buildVersion',$buildVersion);
	}
	
	/*
	* Method to find user's permission to access specified section
	*/
	protected function permissionCheck($id=0) {
		if($id > 0) {
			$q	= "	SELECT
						*
					FROM
						users_permissions
					WHERE
						section_id 	= '".$id."' AND
						user_id		= '".$_SESSION['userid']."'";
			$this->objDA->query($q);
			
			if($this->objDA->numRows() > 0)
				return true;
		}
		
		return false;
	}
	
	/*
	* Method to scale an image
	*/
	protected function imageScale($image_path,$image_name,$max_width = 80,$max_height = 60) {
		$src_img = imagecreatefromjpeg($image_path.'/'.$image_name); 
		
		$img_height = imagesy($src_img); 
		$img_width  = imagesx($src_img); 
		
		if ($height <= $max_height && $width <= $max_width) 
			return true; 
		
		//first, we need to figure out which dimension is the largest 
		$width_change = $max_width/$img_width; 
		$height_change = $max_height/$img_height;
		
		if($width_change < $height_change) { 
			$new_w = $max_width; 
			$new_h = $img_height * $width_change; 
		} else {
			$new_h = $max_height; 
			$new_w = $img_width * $height_change; 
		}
		
		
		$dst_img = imagecreatetruecolor($new_w,$new_h); 
		
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$new_w,$new_h,$img_width,$img_height); 
		
		imagejpeg($dst_img, $image_path/$image_name, 70); 
		return true;
	}
	
	/*
	* Method to get scaled image height based on width
	*/
	protected function imageHeightFromWidth($width,$height,$new_w) {
		if($width == 0 || $height == 0)
			return 0;
		return ($new_w / $width) * $height;
	}
	
	/*
	* Method to get scaled image height based on height
	*/
	protected function imageWidthFromHeight($width,$height,$new_h) {
		if($width == 0 || $height == 0)
			return 0;
		
		return ($new_h / $height) * $width;
	}
	
	/*
	* Method to recursively search for a key in a nested array and return the value
	*/
	protected function array_key_search($needle='',$haystack=array()) {
		foreach($haystack as $key=>$value) {
			if(is_array($value)) {
				$return	= $this->array_key_search($needle,$value);
				if($return != false)
					return $return;
			} elseif($key	== $needle) {
				return $value;
			}
		}
		
		return false;
	}
	
	/*
	* Method to clean user input
	*/
	protected function sanitize($input) {
		if(is_array($input)) {
			$r	= array();
			
            foreach($input as $n=>$v){
                $r[$n]	= $this->sanitize($v);
            }
        } else {
			if(get_magic_quotes_gpc())
				$r	= $input;
			else
				$r	= addslashes($input);
        }
		
		return $r;
	}
	
	/*
	* Method to undo cleaning of user input
	*/
	protected function unsanitize($input) {
		if(is_array($input)) {
			$r	= array();
			
            foreach($input as $n=>$v){
                $r[$n]	= $this->unsanitize($v);
            }
			
            return $r;
        } else {
            return stripslashes($input);
        }
	}
	
	/*
	* Search nested array
	*/
	protected function in_arrayr($needle, $haystack, &$found = false) { 
		foreach ($haystack as $v) { 
			if ($needle == $v) { 
				$found = true; 
				return true; 
			} elseif (is_array($v)) { 
				$this->in_arrayr($needle, $v, $found); 
			} 
		} 
	
		return $found; 
	} 
	
	/*
	* Method to search in_array with wildcard (*) support
	*/
	protected function in_arrayw($needle, $haystack, $case_sensitive=true) {
		$is_wild = (strpos($needle,"*")===true)? true : false;
		$needles = ($is_wild)? explode("*", $needle) : array();
		$needle = ($case_sensitive)? $needle : strtolower($needle);
		for($i=0;$i<count($haystack);$i++) {
			$haystack_str = ($case_sensitive)? $haystack[$i] : strtolower($haystack[$i]);
			if ($is_wild) {
				$found = false;
				for($x=0;$x<count($needles);$x++) {
					$needle_part = trim($needles[x]);
					$needle_index = strpos($haystack_str, $needle_part);
					if ($needle_index===false) {
						$found = false;
						break; //break out of the loop, because string part is not found in the haystack string
					} else {
						$found = true;
						//chop off the start of the string to the needle_index
						//so we can be sure that the found items are in the correct order
						//and we are avoiding the potential of finding duplicate characters
						$haystack_str = substr($haystack_str, 0, $needle_index);
					}
				}
				if ($found) { return true; }
			} elseif (!$is_wild && $haystack_str == $needle) {
				return true;
			}
		}
		return false;
	}
	
	/*
	* Method to search array keys recurrsively
	*/
	protected function array_key_exists_r($needle, $haystack) {
		$result = array_key_exists($needle, $haystack);
		if ($result)
			return $result;
		foreach ($haystack as $v)
		{
			if (is_array($v) || is_object($v))
				$result = $this->array_key_exists_r($needle, $v);
			if ($result)
			return $result;
		}
		return $result;
	}
	
	/*
	* Method to authenticate a Yubikey/PIN
	*/
	protected function yubiAuth($yubikey,$pin) {
		$pin		= md5($pin);
		$yubi_id	= substr($yubikey,0,12);
		
		
		$q	= "SELECT * FROM users_users WHERE yubi_id = '".$yubi_id."' AND enabled = 1";
		$this->objDA->query($q);
		
		if($this->objDA->numRows() > 0) {
			$user	= $this->objDA->returnArray();
			$user	= $user[0];
			
			if($pin == $user['yubi_pin']) {
				// PIN valid; Verify yubi
				require_once __SITE_FS_ROOT . '/app/lib/YubiAuth/Yubico.php';
			
				// login with pin and yubikey
				$yubi = &new Auth_Yubico('419','');
				$auth = $yubi->verify($yubikey);
				
				if (!PEAR::isError($auth))
					return $user;
			}
		}
		
		return false;
	}
	
	/*
	* Method to encrypt a text (assuming 256 bit key)
	*/
	protected function passEncrypt($text, $key) {
		$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		
		if(strlen($key) != mcrypt_enc_get_key_size($cipher))
			return false; // invalid key
		
		// The mcrypt_generic_init function initializes the cipher by specifying both
		// the key and the IV.  The length of the key determines whether we're doing
		// 128-bit, 192-bit, or 256-bit encryption.  
		// Let's do 256-bit encryption here:
		
		$generic	= mcrypt_generic_init($cipher, $key, $this->iv);
		if ($generic != -1 || $generic != -3 || $generic != -4 || $generic !== false)
		{
			// PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
			$cipherText = mcrypt_generic($cipher,$text );
			mcrypt_generic_deinit($cipher);
			
			return $cipherText;
		} else {
			return false;
		}
	}
	
	/*
	* Method to decrypt text
	*/
	protected function passDecrypt($text, $key) {
		$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
		
		if(strlen($key) != mcrypt_enc_get_key_size($cipher))
			return false; // invalid key
		
		// The mcrypt_generic_init function initializes the cipher by specifying both
		// the key and the IV.  The length of the key determines whether we're doing
		// 128-bit, 192-bit, or 256-bit encryption.  
		// Let's do 256-bit encryption here:
		
		$generic	= mcrypt_generic_init($cipher, $key, $this->iv);
		if ($generic != -1 || $generic != -3 || $generic != -4 || $generic !== false)
		{
			// PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
			$cipherText = mdecrypt_generic($cipher,$text );
			mcrypt_generic_deinit($cipher);
			
			return $cipherText;
		} else {
			return false;
		}
	}
}
?>