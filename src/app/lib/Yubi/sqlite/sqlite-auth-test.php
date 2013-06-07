<?
/*
 * sqlite-auth-test.php
 * Test for the SQLiteAuth class
 * Verifies users against the database.
 * NOTE: Please uncomment the call to setupDatabase() in sqlite-auth.php if
 * this is your first time running the script.  setupDatabase() takes care
 * of creating the tables and populating them with data.
 */
 
	require("sqlite-auth.php");
	
	$auth = new SQLiteAuth("test.db");	// new authenticator

	// Users to authenticate
	$users = array();
	$users[0] = "Mr. Good";
	$users[1] = "Mr. Bad PubID";
	$users[2] = "Mr. Bad PrivID";
	$users[3] = "Mr. Bad Counter";
	$users[4] = "Mr. Bad Time";
	$users[5] = "Mr. Does Not Exist";
	
	$otp = "";
	if ($argv[1] == "" )
		$otp = "ltfulhbrrnjgbcjlgibvuvrffjjctucgeucvdlrknbjn";// "";
	else
		$otp = $argv[1];

	foreach ($users as $user) {
		
		print "\n-- CHECKING user $user ---------------------------------------\n";
		$result = $auth->authenticate_user($user, $otp);
		if ($result == AuthResult::SUCCESS ) {
			print "User $user has been authenticated\n";
		}
		
		// if it fails, tell us why
		else { print "ERROR: Unable to authenticate user $user !\n"; 
			switch ( $result ) {
				case AuthResult::FAILED_PUBLIC_ID:
					print "Bad public ID\n";
					break;
				case AuthResult::FAILED_PRIVATE_ID:
					print "Bad Private ID\n";
					break;
				case AuthResult::FAILED_COUNTER:
					print "Bad counter\n";
					break;
				case AuthResult::FAILED_TIMESTAMP:
					print "Bad timestamp\n";
					break;
				case AuthResult::FAILED_DECODE:
					print "Couldn't decode\n";
					break;
			}
		}
	}
		
	
?>