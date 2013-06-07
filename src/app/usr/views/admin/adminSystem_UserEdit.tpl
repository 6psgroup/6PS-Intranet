{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

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

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2 id="add-new-user">{$block_title}</h2>

<div>

<form action="{$action}" method="post" name="EditPage">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<th scope="row" width="33%">Username</th>
			<td width="66%"><input name="username" type="text" value="{$username}" /></td>
		</tr>
		<tr>
			<th scope="row">PIN:</th>
			<td><input name="pin1" type="password" value="" /></td>
		</tr>
		<tr>
			<th scope="row">PIN Verify:</th>
			<td><input name="pin2" type="password" value="" /></td>
		</tr>
		<tr>
			<th scope="row">User's Yubikey:<br /><font size="-2" color="#999999">(Leave blank for no change)</font></th>
			<td><input name="yubi" type="text" value="" onkeypress="return disableEnterKey(event)" class="yubiinput" size="18" /></td>
		</tr>
		
		<tr>
			<th scope="row">First Name:</th>
			<td><input name="firstname" type="text" value="{$firstname}" /></td>
		</tr>
		<tr>
			<th scope="row">Last Name:</th>
			<td><input name="lastname" type="text" value="{$lastname}" /></td>
		</tr>
		<tr>
			<th scope="row">Enabled:</th>
			<td><input type="checkbox" name="enabled" value="checked" {$enabled} /></td>
		</tr>
        <tr>
			<th scope="row">Email:</th>
			<td><input name="email" type="text" value="{$email}" /></td>
		</tr>
        <tr>
			<th scope="row" valign="middle">Permissions:</th>
			<td>{html_checkboxes name=permissions options=$permissions selected=$selPermissions separator='&nbsp;<br />'}</td>
		</tr>
		
		
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<th scope="row">Your PIN:</th>
			<td><input name="verify_pin" type="password" value="" /></td>
		</tr>
		<tr>
			<th scope="row">Your Yubikey:</th>
			<td><input name="verify_yubi" type="text" value="" onkeypress="return disableEnterKey(event)" class="yubiinput" size="18" /></td>
		</tr>
	</table>
	
	<input type="hidden" name="id" value="{$id}" />
	<p class="submit">
		<input name="submit" type="submit" value="Submit &raquo;" />
	</p>
</form>

</div>
</div>

{include file="admin/adminFooter.tpl"}