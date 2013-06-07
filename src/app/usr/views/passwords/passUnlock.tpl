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

<form action="passEdit.php" method="post" name="EditPass">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
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
		<input name="submit" type="submit" value="Decrypt &raquo;" />
	</p>
</form>

</div>
</div>

{include file="admin/adminFooter.tpl"}