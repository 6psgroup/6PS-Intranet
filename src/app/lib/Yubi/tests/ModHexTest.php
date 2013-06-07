<?

require_once("PHPUnit/Framework.php");
require_once("../yubikey.php");

class ModHexTest extends PHPUnit_Framework_TestCase {

	// Test ModHex::Decode()
	public function testEncodeEnglish() {
		$this->assertEquals( ModHex::Encode("hello"), "hjhghrhrhv" );
	}
	
	public function testDecodeEnglish() {
		$this->assertEquals( ModHex::Decode("hjhghrhrhv"), "hello" );
	}
}
?>
