<html>
	<head></head>
	<body>
		<h2>Yubikey decoder without replay check by Alex Skov Jensen</h2>
		<form method="post" action="">
			<table>
				<tr><td>Secret AES encryption key (HEX, 128 bit = 32 characters from 0-F):</td><td><input type="text" name="secret_aes_key" value="<?php echo($secret_aes_key); ?>" size="50"></td></tr>
				<tr><td>Press you yubikey here:</td><td><input type="text" name="yubistring" size="50"></td></tr>
			</table>
			<br><input type="submit" value="decode">
		</form>

	
		<?PHP
			require_once('yubiclass.php');
		
			$yubistring=$_POST["yubistring"];
			$secret_aes_key=$_POST["secret_aes_key"];

			$t=new YubikeyValidator();
			$valid=$t->isValidOTP($yubistring,$secret_aes_key);

			if ($yubistring) echo "<br>is valid: $valid<br><br><b>Decoded Yubikey data:</b><br><br>";
			while (list($key, $value) = each($t->yk)) 
				echo "$key=$value<br />\n";
		?>
	</body>
</html>