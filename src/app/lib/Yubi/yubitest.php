<?
/*
	yubitest.php
	Written by John E. Woltman IV
	Released under the LGPL v2.
	
	Test case using sample data from Yubico.
	The following functionality of Yubikey and ModHex class are tested:
	 1) ModHex encoding
	 2) ModHex decoding
	 3) Yubikey one-time-password decoding
	
	This program will decrypt a sample one-time-password with a sample
	AES key, and check the results against Yubico's results.
	
	
*/
	require_once("yubikey.php");
	
	print "---------- Yubikey PHP Basic Tests ----------\n";
	print "> Note: Using test data from Yubico-c\n\n";
	/***
	 * Test the ModHex encoding and decoding class
	 *
	 */
	print "> ModHex encoding/decoding tests:\n";
	
	$fromString = "test";
	$toString = "ifhgieif";
	
	$encoded = ModHex::Encode($fromString);
	if ($encoded === "ifhgieif" ) {
		print "\t-> Encoding OK\n";
	} else { print "ENCODING FAILED, $encoded\n"; }
	
	$decoded = ModHex::Decode($toString);
	if ($decoded === "test") {
		print "\t-> Decoding OK\n";
	} else { print "DECODING FAILED, $decoded\n"; }
	
	
	/***
	 * Test the Yubikey class
	 *
	 */
	 print "> Yubikey OTP tests:\n";
	
	// The key provided by Yubico in the sample is ModHexed, so we need to
	// decode it first.
	$key= pack('H*', "f840a8af9f1575dca834aadb419759da");//
	//$key = ModHex::Decode("urtubjtnuihvntcreeeecvbregfjibtn");
	
	$token="ltfulhbrrnjgeibcfcvflvdgnbbtkglhchftcfubdchh";//
	//$token = "dteffujedcflcindvdbrblehecuitvjkjevvehjd"; // working OTP
	//$token = "brblehecuitvjkjevvehjd"; // test the TOO_SHORT error.
	//$token = "dteffujedcflcindvdbrblohecuitvjkjevvehjd"; // test the MH failure error
	//$token = "dteffujedcflcindvdbrblohecuittjkjevvehjd"; // test the CRC error
	
	$decoded_token = Yubikey::Decode($token, $key);

	if ( ! is_array($decoded_token) ) { 
		die( "DECODING FAILED, $decoded_token\n");
	}
	else {
		// Uncomment to see contents of the decoded array.
		//print_r($decoded_token);
	}
	
	// Check the public ID
	if ( $decoded_token["public_id"] === "dteffuje" ) {
		print "\t-> Public ID OK (".$decoded_token["public_id"].")\n";
	} else { print "PUBLIC ID FAILED, ".$decoded_token["public_id"]."\n"; }
	
	// Check the decoded token
	if ( $decoded_token["token"] === "8792ebfe26cc1300a8c00010b4086f5b" ) {
		print "\t-> Token OK (".$decoded_token["token"].")\n";
	} else { print " TOKEN FAILED,".$decoded_token["token"]."\n"; }
	
	// Check the private ID
	if ( $decoded_token["private_id"] === "8792ebfe26cc" ) {
		print "\t-> Private ID OK (".$decoded_token["private_id"].")\n";
	} else { print "PRIVATE ID FAILED, ".$decoded_token["private_id"]."\n";	}

	// Check the counter
	if ( $decoded_token["counter"] == 4880 ) {
		print "\t-> Counter OK (".$decoded_token["counter"].")\n";
	} else { print "COUNTER FAILED, ".$decoded_token["counter"]."\n"; }
	
	// Check the random data
	if ( $decoded_token["random"] == 2228 ) {
		print "\t-> Random data OK (".$decoded_token["random"].")\n";
	} else { print "RANDOM DATA FAILED, ".$decoded_token["random"]."\n"; }
	
	// Check the timestamp
	if ( $decoded_token["timestamp"] == 49320 ) {
		print "\t-> Timestamp OK (".$decoded_token["timestamp"].")\n";
	} else {print "TIMESTAMP FAILED, ".$decoded_token["timestamp"]."\n"; }
	
	// Check the crc checksum
	if ( $decoded_token["crc"] == 23407 ) {
		print "\t-> CRC value OK (".$decoded_token["crc"].")\n";
	} else { print "CRC VALUE FAILED, ".$decoded_token["crc"]."\n"; }
	
	print ("\n====== Finished ====== \n");
	

?>
