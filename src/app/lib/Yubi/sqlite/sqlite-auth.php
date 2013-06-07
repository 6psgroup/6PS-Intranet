<?
require("../yubikey.php");
/*
	SQLite Authentication Example

	To use from the command line:
	Use the init-database.php script to initialize a database.
	Use adduser.php & deluser.php to administer users.
	Use show-all-users.php to show all the usernames in the database.
	
	To use from another script:
	Create a new SQLiteAuth object with the path to the database as the only paramter.
	Use setup_database() to create the right table (called yubikeys)
	Use add_user to add a user to the database
	Use delele_user to delete a user from the database
	
*/

// Subclass AuthData to contain information about user's name
class SQLiteAuthData extends AuthData {
		var $user;	// 
	}

class SQLiteAuth extends YubiAuthenticator
{
	var $db_path;	// path to the database
	
	// Sets up the database with a "yubikeys" table to hold username and
	// yubikey information
	public function setup_database()
	{
		$db = sqlite_open($this->db_path);
		sqlite_exec($db, "CREATE TABLE yubikeys (username TEXT, yu_public TEXT, yu_private TEXT, yu_aeskey TEXT, yu_counter INTEGER, yu_timestamp INTEGER, serverstamp INTEGER);");
		$base = "INSERT INTO users (username, yu_public, yu_private, yu_aeskey, yu_counter, yu_timestamp, serverstamp) VALUES (";
		sqlite_close($db);
	}
	
	/*
	 * Adds a user to the database
	 * $user is the username
	 * $public_id is the yubikey public ID in ModHex
	 * $private_id is the private ID in hex (no preceding "0x")
	 * $aes_key is the AES key in hex.
	 */
	public function add_user($user, $public_id, $private_id, $aes_key) {

		$suser = sqlite_escape_string($user);
		$spublic = sqlite_escape_string($public_id);
		$sprivate = sqlite_escape_string($private_id);
		$saes = sqlite_escape_string($aes_key);
		
		$db = sqlite_open($this->db_path);
		$base = "INSERT INTO yubikeys (username, yu_public, yu_private, yu_aeskey) VALUES (";
		
		$result = sqlite_exec($db, $base."'".$suser."', '".$spublic."', '".$sprivate."', '".$saes."');");
		sqlite_close( $db );
		
		return $result;
	}

	/*
	 * Deletes a user from the database
	 * $user is the username
	 */
	public function delete_user($user) {
		$suser = sqlite_escape_string($user);
		$db = sqlite_open("$this->db_path");
		$result = sqlite_exec($db, "DELETE FROM yubikeys WHERE username='".$suser."';");
		sqlite_close($db);
		return $result;
	}

	/*
	 * Constructor requires a $path to a SQLite database
	 */
	public function __construct($path) {
		$this->db_path = $path;
	}
	
	/*
	 * Authenticate user $username with the token $otp.
	 */
	public function authenticate_user($username, $otp) {
		$db = sqlite_open($this->db_path);
		$r = sqlite_query($db, "SELECT yu_public, yu_aeskey, yu_private, yu_counter, yu_timestamp, serverstamp FROM yubikeys WHERE username='".$username."';");
		if ( $row = sqlite_fetch_array($r) ) {
			
			// This example looks for everything: private id/public id/counter/timestamp must
			// all be correct.
			$ai = new SQLiteAuthData();
			$ai->user = $username;
			
			// By setting these fields of $ai, we're telling authenticate() to check
			// them all.
			$ai->public_id =  $row['yu_public'];
			$ai->aes_key = pack("H*",$row['yu_aeskey']);
			$ai->private_id = $row['yu_private'];
			$ai->counter = $row['yu_counter'];
			$ai->timestamp = $row['yu_timestamp'];
			
			$ai->server_timestamp = $row['serverstamp'];
			
			$result = $this->authenticate($otp, $ai);
			sqlite_close($db);
			return $result;
			
		}
		sqlite_close($db);
		return false;
	}
	
	protected function update_auth_data($newCounter, $newTimestamp, $newServerStamp, $oldAuthData) {
		$db = sqlite_open($this->db_path);
		$result = sqlite_exec($this->db, "UPDATE yubikeys SET yu_counter='".$newCounter."', yu_timestamp='".$newTimestamp."', serverstamp='".$newServerStamp."' WHERE username='".$oldAuthData->user."'");
		sqlite_close($db);
		return $result;
	}
	
		
}

?>