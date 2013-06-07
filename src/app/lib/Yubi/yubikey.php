<?
/*
	yubikey.php
	Written by John E. Woltman IV
	Released under the LGPL v2.
	Based on code from Yubico's C and Java server samples.
	
	WARNING: I have not tested this with an actual yubikey yet, only with
	the sample output included in yubico-c's README file.
	
	NOTICE: This class DOES NOT track or log a Yubikey's counters and
	timestamps.  You can use this code to integrate Yubikey authentication
	into your own backend authentication system and keep track of the necessary
	information yourself.
	
	Please see the file yubitest.php for a test scenario.
	
*/
 require_once("AES128.php");
 
 /* 
 * Class ModHex
 * Encapsulates encoding/decoding text with the ModHex encoding from Yubico.
 * ModHex::Decode decodes a ModHex string
 * ModHex::Encode encodes a regular string into ModHex
 *
 */
class ModHex {

	static $TRANSKEY = "cbdefghijklnrtuv"; // translation key used to ModHex a string
	
	// ModHex encodes the string $src
	static function Encode($src) {
		$encoded = "";
		$i = 0;
		$srcLen = strlen($src);
		for ($i = 0; $i < $srcLen; $i++) {
			$bin = (ord($src[$i]));
			$encoded .= ModHex::$TRANSKEY[((int)$bin >> 4) & 0xf];
			$encoded .= ModHex::$TRANSKEY[ (int)$bin & 0xf];
		}
		return $encoded;		
	}
	
	// ModHex decodes the string $token.  Returns the decoded string if successful,
	// or zero if an encoding error was found.
	static function Decode($token) {
		$tokLen = strlen($token);	// length of the token
		$decoded = "";				// decoded string to be returned
		
		// strings must have an even length
		if ( $tokLen % 2 != 0 ) { return FALSE; }
		
		for ($i = 0; $i < $tokLen; $i=$i+2 ) {
			$high = strpos(ModHex::$TRANSKEY, $token[$i]);
			$low = strpos(ModHex::$TRANSKEY, $token[$i+1]);

			// if there's an invalid character in the encoded $token, fail here.
			if ( $high === FALSE || $low === FALSE ) 
				return FALSE;

			$decoded .= chr(($high << 4) | $low);
		}
		return $decoded;
	}
	

}

/*
 * Class Yubikey
 * This class does most of the hard work to give you useable data.
 *
 * Please refer to the documentation for further information on usage.
 *
 */

class Yubikey {

	// Some magic numbers for processing the keys
	const OTP_STRING_LENGTH = 32;	// # of characters in the encrypted token of the OTP
	const UID_SIZE = 12; // # of characters in the private ID
	const CRC_OK_RESIDUE = 0xf0b8;
	
	// Error codes
	const ERROR_TOO_SHORT = 1;
	const ERROR_BAD_CRC = 2;
	const ERROR_MODHEX_FAILED = 3;
	
	// Decrypts a ModHexed one-time-password $ModHexOTP with the given $key
	static function Decrypt($ModHexOTP, $key) {
		$aes = new AES128(true);
		$decoded = ModHex::Decode($ModHexOTP);
		if ( $decoded === FALSE )
			return FALSE;
		return $aes->blockDecrypt($decoded, $aes->makeKey($key));
	}
	
	static function CalculateCrc($token) {
		
		$length = strlen($token);
		$crc = 0xffff;

 		for ($i = 0; $i < 16; $i++ ) {
			$b = hexdec($token[$i*2].$token[($i*2)+1]);
			
			$crc = $crc ^ ($b & 0xff);
			
			for ($j = 0; $j < 8; $j++) {
				$n = $crc & 1;
				$crc = $crc >> 1;
				if ( $n != 0) { $crc = $crc ^ 0x8408; }
			}
		
		}
		return $crc;
	}
	
	static function CrcIsGood($token) {
		return Yubikey::CalculateCrc($token) == Yubikey::CRC_OK_RESIDUE;
	}
	
	/**
	 * Returns the public ID of the given ModHexed one-time-password
	 */
	static function GetPublicId($otp) {
		return substr($otp, 0, strlen($otp) - Yubikey::OTP_STRING_LENGTH);
	}
	
	/**
	 * Decode
	 */
	static function Decode( $otp, $key ) {
		$otpLen = strlen($otp);

		$decoded = array();	// hold our return values
		
		// if the $otp is longer than 32, assume the extra characters
		// are the yubikey's unchanging public ID.  If the $otp is too short,
		// return immediately.
		if ($otpLen > Yubikey::OTP_STRING_LENGTH) {
			$decoded["public_id"] = substr($otp, 0, $otpLen - Yubikey::OTP_STRING_LENGTH);
		} elseif ( $otpLen < Yubikey::OTP_STRING_LENGTH ) {
			return Yubikey::ERROR_TOO_SHORT;
		}
		
		// Decrypt the token (the last 32 characters of the $otp)
		$decoded["token"] = Yubikey::Decrypt(substr($otp, 0-Yubikey::OTP_STRING_LENGTH), $key);
		if ( $decoded["token"] === FALSE ) {
			return Yubikey::ERROR_MODHEX_FAILED;
		}
		
		// Private ID
		$start = 0;
		$decoded["private_id"] = substr($decoded["token"], $start, Yubikey::UID_SIZE);
		$start += Yubikey::UID_SIZE;
		
		// Counter - this portion not stored in array.
		$counter = hexdec(substr($decoded["token"], $start+2, 2).substr($decoded["token"], $start, 2)) & 0x7fff;
		$start += 4;
		
		// Time stamp LOW - not stored in array.
		$timelow = hexdec(substr($decoded["token"], $start+2, 2).substr($decoded["token"], $start, 2));
		$start += 4;
		
		// Full time stamp
		$decoded["timestamp"] = (hexdec(substr($decoded["token"], $start, 2)) << 16) + $timelow;
		$start += 2;
		
		// Session Counter
		$decoded["counter"] = ($counter<<8) + hexdec(substr($decoded["token"], $start, 2));
		$start += 2;
		
		// Randomness - Do we need to store this?
		$decoded["random"] = hexdec(substr($decoded["token"], $start+2, 2).substr($decoded["token"], $start, 2));
		$start += 4;
		
		// CRC
		$decoded["crc"] = hexdec(substr($decoded["token"], $start+2, 2).substr($decoded["token"], $start, 2));
	
		if ( Yubikey::CrcIsGood($decoded["token"]) ) {
			return $decoded;
		}
		
		return Yubikey::ERROR_BAD_CRC;
	}
	
}

/*
 * Class AuthData
 * ABOUT: basic data container.  Fill it with your requirements, for example with
 * user information from the database.
 * Subclass it to add your own details if you need, such as a username.
 */
class AuthData {
	var $public_id;		// unchanging public ID
	var $private_id;	// the hidden private ID
	var $counter;		// the counter
	var $timestamp;		// timestamp
	var $aes_key;		// secret AES key used to decode an OTP.
	var $server_timestamp;	// last server timestamp

	const SKIP_CHECK = -1;	// set the above fields to SKIP_CHECK and they will
	                        // be skipped.  Does not apply to $server_timestamp
							// or aes_key
							
	public function __construct() {
		$this->public_id = AuthData::SKIP_CHECK;
		$this->private_id = AuthData::SKIP_CHECK;
		$this->counter = AuthData::SKIP_CHECK;
		$this->timestamp = AuthData::SKIP_CHECK;
	}
}

class AuthResult {
	const SUCCESS = 1;
	const FAILED_PUBLIC_ID = -1;
	const FAILED_PRIVATE_ID = -2;
	const FAILED_COUNTER = -3;
	const FAILED_TIMESTAMP = -4;
	const FAILED_DECODE = -5;
	const FAILED_DATA_UPDATE = -6;
}

/*
 * Class YubiAuthenticator
 * ABOUT: Interface to design your own classes from.  The authenticate() method
 * will be updated to make a basic authentication routine.
 * Implement UpdateAuthData() to update your data store.
 */
abstract class YubiAuthenticator {
	
	/*
	 * authenticate()
	 * Basic routine that checks:
	 * 1) Did the OTP decode (required)
	 * 2) Do the OTP and AuthData public IDs match (optional)
	 * 3) Do the OTP and AuthData private IDs match (optional)
	 * 4) Did the counter increment from the last time? (optional)
	 * 5) Is the timestamp within allowable limits? (optional)
	 * 6) Did we successfully update the back-end data store with the new
	 *    details? (required)
	 */
	public function Authenticate($otp, $authdata) {
		$debug = 1;	// print some extra things when =1.
		
		// TODO: Add class checking on $authdata
		
		// Decode the OTP
		$decoded = Yubikey::Decode($otp, $authdata->aes_key);
		
		$serverNow = 0;		// will hold the current time on the backend.
		$passed = false;	// will be returned at the end and be true if all requested checks were passed.
		
		if ( is_array($decoded) ) {
			
			$passed = true;	// the basic check is passed, so we've passed the authentication
			if ( $authdata->public_id != AuthData::SKIP_CHECK ) {
				if ( $authdata->public_id != $decoded["public_id"] ) {
					return AuthResult::FAILED_PUBLIC_ID;
				}
			}
	
			if ( $authdata->private_id != AuthData::SKIP_CHECK ) {
				if ( $authdata->private_id != $decoded["private_id"] ) {
					return AuthResult::FAILED_PRIVATE_ID;
				}
			}
			
			// TODO: Add optional "missed OTP checking"??
			if ( $authdata->counter != AuthData::SKIP_CHECK ) {
				if ( $authdata->counter >= $decoded["counter"] ) {
					return AuthResult::FAILED_COUNTER;
				}
			}

			// The timestamp check is a bit more complicated than the others.
			if ( $authdata->timestamp != AuthData::SKIP_CHECK ) {
				// Can't realistically compare time values without counter data.
				if ( $authdata->counter == AuthData::SKIP_CHECK ) {
					error_log("AUTHENTICATION ERROR: Requested timestamp check.  Timestamp checks require both counter and timestamp data.  Please set counter to something other than AuthData::\SKIP_CHECK.", 0);
					return AuthResult::FAILED_TIMESTAMP;
				}
				else {
					$insert_counter = $decoded["counter"] >> 8;		// extract insertion counter from counter
					$serverNow = time();							// get current time
					
					// We'll only run through the check if the insertion counter is the same.
					if ( $insert_counter == ($authdata->counter >> 8) ) {
						if ( $debug ) { print "TIME CHECK: Insertion counters match ($insert_counter), checking time as requested.\n"; }
						
						$yubiDiff = abs(($decoded["timestamp"] - $authdata->timestamp)/8);
						$serverDiff = abs($serverNow - $authdata->server_timestamp);
						$deviation = abs(($yubiDiff - $serverDiff) - ($yubiDiff*0.20));
						
						// check the deviation
						if ( $deviation > 15 ) { return AuthResult::FAILED_TIMESTAMP; }

					}
					else {
						if ( $debug ) { print "TIME CHECK: Skipping the timestamp check, insertion counter is different\n"; }
					}
				}
				
			}
			
		} else { return AuthResult::FAILED_DECODE; }
		
		// If everything checks out then store the new counter and timestamp in the database.
		if ( $this->UpdateAuthData($decoded["counter"], $decoded["timestamp"], time(), $authdata) ) {
			return AuthResult::SUCCESS;
		}
			
		return AuthResult::FAILED_DATA_UPDATE;
	}
	
	// Parameters: $newCounter is the new counter that was decoded.
	//             $newTimestamp is the new Yubikey timestamp that was decoded.
	//             $newServerStamp is the server's current time after authentication.
	//             $oldAuthData is the data that was supplied to authenticate().
	abstract protected function update_auth_data($newCounter, $newTimestamp, $newServerStamp, $oldAuthData);
}
?>