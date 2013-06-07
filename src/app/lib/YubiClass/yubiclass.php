<?PHP
	/*
		Yubico OTP authenticator v0.6 by Alex Skov Jensen (10/6-2008)
		Released under lgpl (see www.gnu.org/licenses/lgpl.html for more information)
		
		Use this class to authenticate yubikey OTP, preventing replay attacks.
		Or it can just be used to decode an OTP from a yubikey if you know the secret AES-key.

		Please look in the 3 examples for information on usage.
		Idea of class-abstraction came from John Woltman
	*/

	class YubikeyMysqlAuthenticator extends YubikeyAuthenticator 
	{
		protected $mysql_host;
		protected $mysql_db;
		protected $mysql_user;
		protected $mysql_pass;
		protected $mysql_table;
	
		public function __construct($mysql_host, $mysql_db, $mysql_user, $mysql_pass, $mysql_table)
		{
			// Setup the database parameters because we are going to authenticate via MYSQL
			$this->mysql_host=$mysql_host;
			$this->mysql_db=$mysql_db;
			$this->mysql_user=$mysql_user;
			$this->mysql_pass=$mysql_pass;
			$this->mysql_table=$mysql_table;
			$this->auth_type="mysql";
		}
		
		protected function keystore_get($publicID)
		{
			// Lookup the previous counter, counter_session, timer, secret aes key from the public ID - using MYSQL
			$mhndl = mysql_connect($this->mysql_host, $this->mysql_user, $this->mysql_pass);
			if ($mhndl) 
			{
				$db_selected = mysql_select_db($this->mysql_db, $mhndl);
				if ($db_selected)
				{
					$result = mysql_query("SELECT * FROM ".$this->mysql_table." WHERE publicID=\"".mysql_real_escape_string($publicID)."\" LIMIT 1");
					if ($result) 
					{
						$arr=mysql_fetch_assoc($result);
						if (!$arr) return ("PublicID of the Yubikey not found");
						return($arr);
					}
					return("PublicID of the Yubikey not found");
				}
			}
			return("Could not get data from mysql");
		}
		
		protected function keystore_put($publicID, $counter, $counter_session, $timer)
		{
			// Store the new counter, counter_session, timer in MYSQL-database
			$mhndl = mysql_connect($this->mysql_host, $this->mysql_user, $this->mysql_pass);
			if ($mhndl) 
			{
				$db_selected = mysql_select_db($this->mysql_db, $mhndl);
				if ($db_selected)
				{
					$query ="UPDATE ".$this->mysql_table." SET counter=$counter,counter_session=$counter_session,";
					$query.="tstamp=$timer WHERE publicID=\"$publicID\" LIMIT 1";
					$result = mysql_query($query);
					if (!$result) return("Could not connect and get data from mysql database");
					return(true);
				}
			}
		}
	}

	class YubikeyFileAuthenticator extends YubikeyAuthenticator 
	{
		protected $auth_filename;
		
		public function __construct($auth_filename)
		{
			$this->auth_filename=$auth_filename;
			$this->auth_type="file";
		}
		
		protected function keystore_get($publicID)
		{
			// Lookup the previous counter, counter_session, timer, secret aes key from the public ID - using INI-file
			if (is_file($this->auth_filename))
			{
				$ini=parse_ini_file($this->auth_filename,true);
				$thiskey=$ini[$publicID];
				if (!$thiskey) return("PublicID of the Yubikey not found");
				return ($thiskey);
			} else return ("Inifile does not exist");
		}
		
		protected function keystore_put($publicID, $counter, $counter_session, $timer)
		{
			// Store the new counter, counter_session, timer in the INI-file
			$ini=parse_ini_file($this->auth_filename,true);
			$ini[$publicID]["counter_session"]=$counter_session;
			$ini[$publicID]["counter"]=$counter;				
			$ini[$publicID]["tstamp"]=$timer;

			//Now save the inifile again.
			$success=$this->save_ini($this->auth_filename,$ini);
			return($success);
		}
		
		protected function save_ini($filename, $iniarr)
		{
			//Traverse the ini-array and build the entire ini-text in $fcont
			$fcont="";
			while (list($publicID, $settings) = each($iniarr)) 
			{
				$fcont.="[$publicID]\n";
				while (list($item, $value) = each($settings))
					$fcont.="$item=$value\n";
				$fcont.="\n";
			}

			//Write the ini-file
			if (is_writable($filename)) 
			{
				if (!$handle = fopen($filename, 'w')) 
					return ("Cannot open ini-file ($filename)");
				if (fwrite($handle, $fcont) === FALSE) 
					return ("Cannot write to file ($filename)");
				fclose($handle);
			} else { return "The ini-file ($filename) is not writable";}
			return(true);
		}
	}

	class YubikeyValidator extends YubikeyAuthenticator 
	{
		protected function keystore_get($publicID)
		{
			return("You can only use method isValidOTP in this class YubikeyValidator");
		}
		
		protected function keystore_put($publicID, $counter, $counter_session, $timer)
		{
			return("You can only use method isValidOTP in this class YubikeyValidator");
		}
	}
	
	abstract class YubikeyAuthenticator 
	{
		public $yk=array();
		public $yk_auth_err="";

		public function isValidOTP($yubikeyOTP,$secret_aes_key)
		{
			// Decrypts every part of the OTP using the secret AES key. All essential values are stored in $this->yk as an array.
			require('AES128.php');
			$aes=new AES128();
			$key=$aes->makeKey(pack('H*',$secret_aes_key));

			if (strlen($yubikeyOTP)>=32)
			{
				$this->yk["token"]=substr($yubikeyOTP,-32);
				$this->yk["token_bin"]=$this->modhex_decode($this->yk["token"]);
				$this->yk["token_hex"]=bin2hex($this->yk["token_bin"]);
				$this->yk["aeskey_bin"]=pack('H*',$secret_aes_key);
				$this->yk["aeskey_hex"]=$secret_aes_key;
				$this->yk["token_decoded_bin"]=$aes->blockDecrypt($this->yk["token_bin"], $key);
				$this->yk["token_decoded_hex"]=bin2hex($this->yk["token_decoded_bin"]);
				$this->yk["secretID_bin"]=substr($this->yk["token_decoded_bin"],0,6);
				$this->yk["secretID_hex"]=bin2hex($this->yk["secretID_bin"]);
				$this->yk["counter"]=ord($this->yk["token_decoded_bin"][7])*256+ord($this->yk["token_decoded_bin"][6]);
				$this->yk["counter_session"]=ord($this->yk["token_decoded_bin"][11]);
				$this->yk["timestamp"]=ord($this->yk["token_decoded_bin"][10])*65536+ord($this->yk["token_decoded_bin"][9])*256+ord($this->yk["token_decoded_bin"][8]);
				$this->yk["random"]=ord($this->yk["token_decoded_bin"][13])*256+ord($this->yk["token_decoded_bin"][12]);
				$this->yk["crc"]=ord($this->yk["token_decoded_bin"][15])*256+ord($this->yk["token_decoded_bin"][14]);
				$this->yk["crc_ok"]=$this->crc_check($this->yk["token_decoded_bin"]);
			}
			// Return that the decoded token seems ok - at this moment we only know that by looking at the CRC.
			return($this->yk["crc_ok"]);
		}
		
		public function isAuthenticatedOTP($yubistring)
		{
			// Get the public ID of the youbikey and look it up in the database.
			$publicID=$this->get_publicID_from_yubikey($yubistring);
			$db=$this->keystore_get($publicID);

			// If the public ID is not found in database or ini-file, then OTP is not authenticated
			if (is_string($db)) 
			{ 
				$this->yk_auth_err=$db; 
				return(false); 
			}

			// Decode the yubikey with the AES key found in the mysql  database (or ini-file)  belonging to the public ID
			$valid=$this->isValidOTP($yubistring, $db["AES_key"]);

			// If the CRC is bad, the OTP is not authenticated
			if (!$valid) 
			{
				$this->yk_auth_err="CRC in OTP failed"; 
				return(false); 
			}

			// Is the secret encrypted ID the same as the one in the database?
			if (!stristr($this->yk["secretID_hex"],$db["secretID"])) 
			{
				$this->yk_auth_err="Secret decoded ID mismatch"; 
				return(false); 
			}

			// Is the counter and the sessioncounter higher than last time the key was authenticated?
			if ($db["counter"]*256+$db["counter_session"]<$this->yk["counter"]*256+$this->yk["counter_session"])
			{
				// Yes it was, store the new counters in mysql. If not succeded, do not authenticate!
				$saved=$this->keystore_put($publicID, $this->yk["counter"], $this->yk["counter_session"], $this->yk["timestamp"]);
				if (is_string($saved))
				{
					$this->yk_auth_err=$saved;
					return(false);
				}
				// New values saved in inifile or database - autheticated!
				return(true);
			} else
			{
				$this->yk_auth_err="OTP replay";
				return(false);
			}
		}

		private function get_publicID_from_yubikey($ystring)
		{
			// Gets the public ID from the OTP
			$this->yk["publicID"]=substr($ystring,0,strlen($ystring)-32);
			return($this->yk["publicID"]);
		}

		private function crc_check($buffer)
		{
			// Do crc check on the binary input
			$m_crc=0xffff;
			for($bpos=0; $bpos<16; $bpos++)
			{
				$m_crc ^= ord($buffer[$bpos]) & 0xff;
				for ($i=0; $i<8; $i++)
				{
					$j=$m_crc & 1;
					$m_crc >>= 1;
					if ($j) $m_crc ^= 0x8408;
				}
			}
			return $m_crc==0xf0b8;
		}

		private function modhex_decode($mstring)
		{
			// Yubico's modhex function rewritten from their original C-sourse
			$cset="cbdefghijklnrtuv";
			$decoded="";
			$hbyte=0;
			for ($i=0; $i<strlen($mstring);$i++)
			{
				$pos=strpos($cset,$mstring[$i]);
				if ($i/2-round($i/2))
				{
					$decoded.=chr($hbyte+$pos);
					$hbyte=0;
				} else $hbyte=$pos*16;
			}
			return $decoded;
		}
	}
?>