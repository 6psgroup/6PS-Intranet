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

<form action="passEditProcess.php" method="post" name="EditPass">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<td align="right">
				<strong>Name:</strong><br /><font size="-1" color="#666666">Warning: Unencrypted</font>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" name="name" value="{$name}" size="50" />
			</td>
		</tr>
        <tr>
			<td align="right">
				<strong>Category:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=cat options=$catTree selected=$selCat}
			</td>
		</tr>
        <tr>
            <td align="right" colspan="2">&nbsp;</td>
        </tr>
        
        <tr>
			<td align="right">
				<strong>Username:</strong>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" name="username" value="{$username}" size="50" />
			</td>
		</tr>
        
        {if $id > 1}
            <tr>
                <td align="right">
                    <strong>Password:</strong>
                </td>
                <td class="selectSmall" align="left">
                    <input type="text" name="password" value="{$password}" readonly="readonly" size="50" />
                </td>
            </tr>
        {/if}
        
        <tr>
            <td align="right" colspan="2">&nbsp;</td>
        </tr>
        <tr>
			<td align="right">
				<strong>Notes:</strong>
			</td>
			<td class="selectSmall" align="left">
				<textarea name="notes" cols="40" rows="8">{$notes}</textarea>
			</td>
		</tr>
        
        <tr>
                <td align="right" colspan="2">&nbsp;</td>
            </tr>
        <tr>
			<td align="right">
				<strong>{if $id > 0}Update {/if}Password:</strong><br /><font size="-1" color="#666666">Leave blank for no change</font>
			</td>
			<td class="selectSmall" align="left">
				<input type="password" name="password2" size="50" />
			</td>
		</tr>
        <tr>
			<td align="right">
				<strong>Verify Password:</strong>
			</td>
			<td class="selectSmall" align="left">
				<input type="password" name="password3" size="50" />
			</td>
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