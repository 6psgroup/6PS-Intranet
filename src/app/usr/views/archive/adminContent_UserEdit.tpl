{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

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
			<th scope="row">Password:</th>
			<td><input name="password1" type="password" value="" /></td>
		</tr>
		<tr>
			<th scope="row">Password Verify:</th>
			<td><input name="password2" type="password" value="" /></td>
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
			<th scope="row">Email:</th>
			<td><input name="email" type="text" value="{$email}" /></td>
		</tr>
		<tr>
			<th scope="row">Enabeled:</th>
			<td><input type="checkbox" name="enabled" value="checked" {$enabled} /></td>
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