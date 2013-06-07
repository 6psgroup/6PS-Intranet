{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

{if $msg != ''}
	<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
{/if}

<div class="wrap">
	<h2>{$block_title}</h2>
	<a href="passEdit.php">New Password</a><br />&nbsp;
	
	<form action="" method="post">
		Filter: {html_options name=cat options=$catTree selected=$selCat}
		<input name="submit" type="submit" value="Submit &raquo;" />
	</form>

	<div>&nbsp;</div>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><div style="text-align: center">ID</div></th>
				<th scope="col" width="90%">Title</th>
				<th scope="col" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			{section name=password loop=$passwords}
				<tr id='post-{$passwords[password].id}' class='{cycle values="alternate2,alternate"}'>
					<td nowrap="nowrap">{$passwords[password].id}</td>
					<td nowrap="nowrap">{$passwords[password].name}</td>
					
					<td style="text-align:center"><a href="passEdit.php?{$passwords[password].id}">Decrypt</a></td>
					<td style="text-align:center"><a href="passDelete.php?{$passwords[password].id}" onClick="return confirm('Are you sure you wish to delete this password?');">Delete</a></td>
				</tr>
			{/section}
		</tbody>
	</table>
</div>

{include file="admin/adminFooter.tpl"}