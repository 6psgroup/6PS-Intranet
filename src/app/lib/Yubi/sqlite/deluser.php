<?
// Deletes a user from the database
require_once("sqlite-auth.php");

if ($argc < 3) {
	print "Too few arguments.\nPlease supply:\n\n\tDatabase path\n\tUsername (quoted if necessary)\n";
	die("* deluser.php will now end *\n ");
}

$sa = new SQLiteAuth($argv[1]);
if ( $sa->delete_user($argv[2]) ) {
	print "User ".$argv[2]." successfully deleted.\n";
}
else {
	print "Error deleting ".$argv[2]." from database.\n";
}
?>