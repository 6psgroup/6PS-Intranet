<?php
	require_once 'ModHexTest.php';
	require_once 'YubikeyTest.php';
	require_once 'PHPUnit/Framework.php';
	require_once 'PHPUnit/Framework/TestSuite.php';
	require_once 'PHPUnit/TextUI/TestRunner.php';
	
	class AllTests {
		public static function main() {
			PHPUnit_TextUI_TestRunner::run(self::suite());
		}
		
		public static function suite() {
			$suite = new PHPUnit_Framework_TestSuite("All Tests for Yubikey Library");
			$suite->addTestSuite( "YubikeyTest" );
			$suite->addTestSuite( "ModHexTest" );
			return $suite;
		}
	}
?>
