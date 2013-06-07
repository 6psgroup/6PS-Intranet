<html>
	<head></head>
	<body>
		<h2>Yubikey full authentication using MySQL by Alex Skov Jensen</h2>
		<form method="post" action="">
			Press your Yubikey:
			<input type="text" name="yubistring" size="50">
			<input type="submit" value="submit">
		</form>

		<?PHP
			require_once('yubiclass.php');
		
			$yubistring=$_POST["yubistring"];

			$t=new YubikeyMysqlAuthenticator("hostname","yubikeydatabase","yubikeyuser","yubikeypass","yubikeytable");
			$valid=$t->isAuthenticatedOTP($yubistring);
			$err=$t->yk_auth_err;

			print ("<b>Yubikey check:</b><br>is valid: $valid<br>Error message: $err<br><br>\n<b>Debug information:</b><br>");

			while (list($key, $value) = each($t->yk)) 
				echo "$key=$value<br />\n";
		?>
	</body>
</html>