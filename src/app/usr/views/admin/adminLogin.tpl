<html>
<head>
<link rel="stylesheet" href="/intranet/styles/admin.css" type="text/css" />
<title>{$page_title}</title>
</head>

<body id="login">



{literal}
<script language="JavaScript">
function disableEnterKey(e)
{
     var key;
     if(window.event)
          key = window.event.keyCode;     //IE
     else
          key = e.which;     //firefox
     if(key == 13)
          return false;
     else
          return true;
}
</script>
{/literal}

<div align="center">
<div id="login">
	<!--h1><a href="http://www.6ps.com" target="_blank"><span class="hide">&nbsp;</span></a></h1-->
	
	<div id="background">
	
	<div id="logo"><img src="/intranet/images/logo.gif" width="230" height="60" /></div>
		<div id="box">
		
		{if $msg != ''}
		<div id="login_error">
			{$msg}<br />
		</div>
		{/if}
		
		<!--h1><a href="http://www.6ps.com" target="_blank"><span class="hide">&nbsp;</span></a></h1-->
		<div id="form">
			<br />
			<form name="loginform" id="loginform" action="loginProcess.php" method="post">
			
			<p>
			<label>PIN:</label><br />
			<input type="password" name="pin" id="user_pin" class="input" value="" size="20" tabindex="20" /></label>
			</p>
			<p>
			<label>Yubikey:</label><br />
			<input type="text" name="yubikey" id="user_yubikey" onKeyPress="return disableEnterKey(event)" class="y_input" value="" size="20" tabindex="20" /></label>
			</p>
			<p class="submit">
			<input type="submit" name="submit" id="submit" value="Login &raquo;" tabindex="100" />
			</p>
			
			</form>
			</div><!--form-->
		</div><!--box-->

	{include file="admin/adminFooter.tpl"}

	</div><!--background-->
</div><!--login-->
</div>

</body>


