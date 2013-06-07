<?
/*
 * Decodes and prints a Yubikey
 */
require("yubikey.php");
 
if ( $argc < 3 ) {
	print "Too few arguments. Please supply the AES key (ModHex) and OTP (ModHex)\n";
	die("* print-token.php will now end *\n");
}

$d = Yubikey::Decode($argv[2], ModHex::Decode($argv[1]));
if (is_array($d) ) { print_r($d); } else { print "Error - $d\n"; }

?>