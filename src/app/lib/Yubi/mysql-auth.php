<?
require("yubikey.php");
/*
	MySQL Authentication EXAMPLE
	
	Note: 
*/

class MySQLAuthData extends AuthData {
		var $user;	// 
	}


class MySQLAuth extends YubiAuthenticator
{
	protected $conn;

	/*
	 * Adds a user to the database
	 * $user is the username
	 * $public_id is the yubikey public ID in ModHex
	 * $private_id is the private ID in hex (no preceding "0x")
	 * $aes_key is the AES key in hex.
	 */
	public function add_user($user, $public_id, $private_id, $aes_key) {

		$suser = mysql_real_escape_string($user, $this->conn);
		$spublic = mysql_real_escape_string($public_id, $this->conn);
		$sprivate = mysql_real_escape_string($private_id, $this->conn);
		$saes = mysql_real_escape_string($aes_key, $this->conn);
		
		$base = "INSERT INTO yubikeys (username, yu_public_id, yu_private_id, yu_aes_key) VALUES (";
		
		$result = mysql_query($base."'".$suser."', '".$spublic."', '".$sprivate."', '".$saes."');", $this->conn);
		
		return $result;
	}

	/*
	 * Deletes a user from the database
	 * $user is the username
	 * NOTE - UNTESTED
	 */
	public function delete_user($user) {
		$suser = mysql_real_escape_string($user, $this->conn);
		$result = mysql_query($db, "DELETE FROM yubikeys WHERE username='".$suser."';", $this->conn);
		return $result;
	}

	/*
	 * Constructor requires an open connection to a MySQL database
	 */
	public function __construct($open_connection) {
		$this->conn = $open_connection;
	}
	
	/*
	 * Authenticate user $username with the token $otp.
	 */
	public function authenticate_user($username, $otp) {
		//$username = mysql_real_escape_quotes($username, $this->conn);
		$username = mysql_escape_string($username );
		$otp = mysql_escape_string($otp);
		$r = mysql_query("SELECT yu_public_id, yu_aes_key, yu_private_id, yu_counter, yu_timestamp, yu_server_timestamp FROM yubikeys WHERE username='".$username."';", $this->conn);
	
		if ( $row = mysql_fetch_assoc($r) ) {
			
			// This example looks for everything: private id/public id/counter/timestamp must
			// all be correct.
			$ai = new MySQLAuthData();
			$ai->user = $username;
			
			// By setting these fields of $ai, we're telling authenticate() to check
			// them all.
			$ai->public_id =  $row['yu_public_id'];
			$ai->aes_key = pack("H*",$row['yu_aes_key']);
			$ai->private_id = $row['yu_private_id'];
			$ai->counter = $row['yu_counter'];
			$ai->timestamp = $row['yu_timestamp'];
			
			$ai->server_timestamp = $row['yu_server_timestamp'];
			
			return $this->authenticate($otp, $ai);
		}
		return false;
	}
	
	protected function update_auth_data($newCounter, $newTimestamp, $newServerStamp, $oldAuthData) {
		return mysql_query("UPDATE yubikeys SET yu_counter='".$newCounter."', yu_timestamp='".$newTimestamp."', yu_server_timestamp='".$newServerStamp."' WHERE username='".$oldAuthData->user."'", $this->conn);
	}
	
		
}

?>
