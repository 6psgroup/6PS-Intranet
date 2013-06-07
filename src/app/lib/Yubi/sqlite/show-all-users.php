<?
require_once("sqlite-auth.php");
if ($argc < 2) {
	print "Too few arguments.\nPlease supply:\n\tDatabase path\n";
	die("* show-all-users.php will now end *\n ");
}
$db = sqlite_open($argv[1]);

if ( $db ) {
	$r = sqlite_query($db, "SELECT username FROM yubikeys;");
	
	if ( $row = sqlite_fetch_array($r) ) {
		print $row[0]."\n";
	}
}

?>