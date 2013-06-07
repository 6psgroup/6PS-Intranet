<?

require_once("PHPUnit/Framework.php");
require_once("../yubikey.php");
require_once("ModHexTest.php");

/* Information for generating key with Alex Skov Jensen's YubiSimulator
Public ID: gjlgjlgjlgjl
AES: 10102323454567678989ababcdcdefef
Private ID: a55b00b33101
Counter: 365
Session Counter: 3
Timer: 40344
Random: 43838
CRC: 63281
Initial OTP will be gjlgjlgjlgjlufvtluibcvkcbjdfvrdndlvjvliguhgd
*/
class YubikeyTest extends PHPUnit_Framework_TestCase {
	
	protected $d;
	var $token = "ndgtriluugnghggtkhvrbbrrgtdfeivgklbkviteggcu";
	public function testFailure() { $this->assertTrue(0); }	
	protected function setUp() {
		$this->d = Yubikey::Decode($this->token, pack("H*","0682952d3363f225654b58a73b131259"));
	}
	
	protected function tearDown() {
		unset($this->d);
	}
	
	public function testGetPublicId() {
		$this->assertEquals( Yubikey::GetPublicId($this->token), "ndgtriluugng" );
	}
	
	public function testDecode() {
		$this->assertTrue( is_array($this->d) );
	}
	
	public function testPublicId() {
		$this->assertEquals( $this->d["public_id"], "ndgtriluugng");
	}
	
	public function testPrivateId() {
		$this->assertEquals( $this->d["private_id"], "c5fc55c3767f");
	}
	
	public function testCounter() {
		$high = 13;
		$t = ($high << 8) + 1;
		$this->assertEquals( $this->d["counter"], $t );
	}
	
	public function testTimestamp() {
		$this->assertEquals( $this->d["timestamp"], 22);
	}
	
	public function testRandom() {
		$this->assertEquals( $this->d["random"], 38751);
	}
	
	public function testCrc() {
		$this->assertEquals( $this->d["crc"], 17931 );
	}
	
	public function testValidCrc() {
		$this->assertTrue( Yubikey::CrcIsGood($this->d["token"]) );
	}
	
	public function testCrcError() {
		$d = Yubikey::Decode("ndgtriluugnghggtkhvrbbrrgtdfeivgklbkviteggcg", pack("H*","0682952d3363f225654b58a73b131259"));
		$this->assertEquals( $d, Yubikey::ERROR_BAD_CRC );
	}
	
	public function testModHexError() {
		// GOOD : $d = Yubikey::Decode("ndgtriluugnghggtkhvrbbrrgtdfeivgklbkviteggcu", pack("H*","0682952d3363f225654b58a73b131259"));
		$d = Yubikey::Decode("ndgtriluugnwhggtkhvrbbrrgtdfeivgkwlbkviteggcu", pack("H*","0682952d3363f225654b58a73b131259"));
		$this->assertEquals( $d, Yubikey::ERROR_MODHEX_FAILED );
	}
	
	public function testLengthError() {
		$d = Yubikey::Decode("ivgklbkviteggcu", pack("H*","0682952d3363f225654b58a73b131259"));
		$this->assertEquals($d, Yubikey::ERROR_TOO_SHORT );
	}
}
?>
