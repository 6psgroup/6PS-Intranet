<?php
class ipv4
{
	var $address;
	var $netbits;
	
	//--------------
	// Create new class
	function setAddress($address,$mask) {
		$this->address = $address;
		
		$format = '';
		if(preg_match("/[0-9].[0-9].[0-9].[0-9]/",$mask)){
			$format = "long";
		} else {
			if($mask<=32) {
				$format = "short";
			} else {
				return false;
			}
		}
		
		switch($format){
			case 'long';
				$mask = decbin(ip2long($mask));
			break;
	
			case 'short':
				for($i=0; $i < $mask ;$i++) {
					$tmp.= 1;
				}
		
				for($j=0; $j < (32 - $mask);$j++) {
					$tmp.= 0;
				}
		
				$mask = $tmp;
			break;
		}
		
		// count number of bits
		for($i=0;true;$i++) {
			if(substr($mask,$i,1) == 0)
				break;
		}
		
		$netbits	= $i;
		
		$this->netbits = $netbits;
	}
	
	//--------------
	// Return the IP address
	function address() {
		return ($this->address);
	}
	
	//--------------
	// Return the netbits
	function netbits() {
		return ($this->netbits);
	}
	
	//--------------
	// Return the netmask
	function netmask() {
		return (long2ip(ip2long("255.255.255.255") << (32-$this->netbits)));
	}
	
	//--------------
	// Return the network that the address sits in
	function network() {
		return (long2ip((ip2long($this->address)) & (ip2long($this->netmask()))));
	}
	
	//--------------
	// Return the broadcast that the address sits in
	function broadcast() {
		return (long2ip(ip2long($this->network()) | (~(ip2long($this->netmask())))));
	}
	
	//--------------
	// Return the inverse mask of the netmask
	function inverse() {
		return (long2ip(~(ip2long("255.255.255.255") << (32-$this->netbits))));
	}

	/*
	
	$ipv4 = new ipv4("192.168.2.1",24);
	print "Address: ".$ipv4->address()."<br />";
	print "Netbits: ".$ipv4->netbits()."<br />";
	print "Netmask: ".$ipv4->netmask()."<br />";
	print "Inverse: ".$ipv4->inverse()."<br />";
	print "Network: ".$ipv4->network()."<br />";
	print "Broadcast: ".$ipv4->broadcast()."<br />";
	
	*/
}
?>