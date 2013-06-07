{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">
	<h2>{$block_title}</h2>

	{if $msg != ''}
		<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
	{/if}
	
	<a href="chartEdit.php">New Account</a><br />
	{if $showDisabled != true}
		<a href="chartList.php?1">Show All Accounts</a><br />&nbsp;
	{else}
		<a href="chartList.php">Show Enabled Accounts</a><br />&nbsp;
	{/if}
	
	<div>&nbsp;</div>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><div style="text-align: center">Number</div></th>
				<th scope="col" width="75%">Name</th>
				<th scope="col">Type</th>
				<th scope="col">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			{section name=chart loop=$chart}
				<tr class='{cycle values="alternate2,alternate"}'>
					<td nowrap="nowrap">{$chart[chart].num}</td>
					<td nowrap="nowrap">{$chart[chart].name}</td>
					<td nowrap="nowrap">{$chart[chart].type}</td>
					
					<td style="text-align:center"><a href="chartEdit.php?{$chart[chart].id}">Edit</a></td>
				</tr>
			{/section}
		</tbody>
	</table>
</div>

{include file="admin/adminFooter.tpl"}