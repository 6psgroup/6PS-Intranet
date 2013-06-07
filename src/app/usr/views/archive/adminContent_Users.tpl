{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2>{$block_title}</h2>

<a href="userAdd.php">New User</a>

<div>&nbsp;</div>

<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><div style="text-align: center">ID</div></th>
			<th scope="col" width="90%">Username</th>
			<th scope="col" nowrap="nowrap">Name</th>
			<th scope="col" nowrap="nowrap">Email</th>
			<th scope="col" colspan="2">&nbsp;</th>
		</tr>
	</thead>
	
	<tbody id="the-list">
		{section name=users loop=$users}
			<tr id='post-{$users[users].id}' class='{cycle values="alternate2,alternate"}'>
				<th scope="row" style="text-align: center" nowrap="nowrap">{$users[users].id}</th>
				<td nowrap="nowrap">{$users[users].username}</td>
				<td nowrap="nowrap">{$users[users].lastname}, {$users[users].firstname}</td>
				<td nowrap="nowrap"><a href="mailto:{$users[users].email}">{$users[users].email}</a></td>

				<td style="text-align:center"><a href="/module/AdminCP_system/userEdit.php?{$users[users].id}">Edit</a></td>
			</tr>
		{/section}
	</tbody>
</table>

</div>

{include file="admin/adminFooter.tpl"}