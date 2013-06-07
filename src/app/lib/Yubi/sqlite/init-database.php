<?
// Initializes the database
require_once("sqlite-auth.php");

if ($argc < 2) {
	print "Too few arguments.\nPlease supply:\n\tDatabase path\n";
	die("* init-database.php will now end *\n ");
}

$sa = new SQLiteAuth($argv[1]);
$sa->setup_database();

?>