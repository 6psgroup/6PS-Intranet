<?php
abstract class DA_mysql {
	var $host		= "localhost";	// hostname of our MySQL server.
	var $database	= ""; 			// Logical database name on that server.
	var $user		= ""; 			// user for login.
	var $pass		= "";			// pass for login
	var $linkID		= 0;  			// Result of mysql_connect().
	var $queryID	= 0;  			// Result of most recent mysql_query().
	var $record		= array();  	// current mysql_fetch_array()-result.
	var $row;						// current row number.
	var $errorNum	= 0;			// error state of query...
	var $error		= "";			// error text
	var $queryCount	= 0;
	var $debug		= '';
	
	function connect() {
		if ( 0 == $this->linkID ) {
			$this->linkID=mysql_connect($this->host, $this->user, $this->pass);
			
			if (!$this->linkID) {
				$this->exception("Link-ID == false, connect failed");
				return false;
			} else {	
				if (!mysql_query(sprintf("use %s",$this->database),$this->linkID)) {
					$this->exception("cannot use database ".$this->database);
					return false;
				}
			}
		}
	}

	function query($Query_String) {
		$this->debug	.= '('.date('h:i:s A').') '.$Query_String.' <p>';
		
		
		$this->connect();
		$this->queryID 		= mysql_query($Query_String,$this->linkID);
		$this->row   		= 0;
		$this->errorNum 	= mysql_errno();
		$this->error 		= mysql_error();

		if (!$this->queryID) {
			$this->exception("Invalid SQL: ".$Query_String);
			return false;
		}
			
		$this->queryCount++;
		return $this->queryID;
	}

	function nextRecord() {
		$this->record = mysql_fetch_array($this->queryID);
		$this->row   += 1;
		$this->errorNum = mysql_errno();
		$this->error = mysql_error();
		
		$stat = is_array($this->record);
		if (!$stat) {
			mysql_free_result($this->queryID);
			$this->queryID = 0;
		}
		return $stat;
	}

	function seek($pos) {
		$status = mysql_data_seek($this->queryID, $pos);
		
		if ($status)
			$this->row = $pos;
		
		return;
	}
	
	/*
	* Method to return results of query as assoc array
	*	@return	array		Results of query
	*/
	function returnArray() {
		$output = array();
		
		while($r = mysql_fetch_assoc($this->queryID))
			array_push($output,$r);

		return $output;
	}
	
	function numRows() {
		return mysql_num_rows($this->queryID);
	}
	
	function numFields() {
		return mysql_num_fields($this->queryID);
	}
	
	function affectedRows() {
		return @mysql_affected_rows($this->linkID);
	}
	
	function insertID() {
		return mysql_insert_id($this->linkID);
	}
	
	function exception($msg) {
		printf("<b>database error:</b> %s<br>\n", $msg);
		printf("<b>MySQL error</b>: %s (%s)<br>\n",$this->errorNum,$this->error);
		
		die("Session halted.");

		return false;
	}
	
	function real_escape_string($string) {
		if(get_magic_quotes_gpc()) {
            $product_name        = stripslashes($string);
        } else {
            $product_name        = $string;
        }
		
		return mysql_real_escape_string($string,$this->linkID);
	}
}
?>