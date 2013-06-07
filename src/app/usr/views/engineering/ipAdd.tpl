{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2 id="add-new-user">{$block_title}</h2>

<div>

<form action="{$action}" method="post" name="EditSubnet">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<th scope="row" width="33%">Network Address</th>
			<td width="66%"><input name="network" type="text" value="{$network}" /></td>
		</tr>
		<tr>
			<th scope="row">Subnet Mask<br /><font size="1">(x.x.x.x or /x)</font></th>
			<td><input name="subnet" type="text" value="{$subnet}" /></td>
		</tr>
		<tr>
			<th scope="row">Type:</th>
			<td>{html_options name=type options=$types selected=$selType}</td>
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