<?
// Adds a user to the database
require_once("sqlite-auth.php");

if ($argc < 6) {
	print "Too few arguments.\nPlease supply:\n\tDatabase path\n\tUsername (quoted if necessary)\n";
	print "\tPublic Yubikey ID (ModHex)\n\tPrivate Yubikey ID (Hex)\n\tAES Key (Hex)\n";
	die("* adduser.php will now end *\n ");
}

$sa = new SQLiteAuth($argv[1]);
if ( $sa->add_user($argv[2], $argv[3], $argv[4], $argv[5]) ) {
	print "User ".$argv[2]." successfully added to database.\n";
}
else {
	print "Error adding ".$argv[2]." to database.\n";
}
?>