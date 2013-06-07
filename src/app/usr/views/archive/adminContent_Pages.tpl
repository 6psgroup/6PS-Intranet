{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2>{$block_title}</h2>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td align="left">
			<form action="" method="post">
				Filter: {html_options name=node options=$nodeTree selected=$nodeID}
				<input name="submit" type="submit" value="Submit &raquo;" />
			</form>
		</td>
		<td align="right">
			<form action="pageNew.php" method="post">
				Type: {html_options name=type options=$type selected="static"}
				<input name="submit" type="submit" value="Add New Page &raquo;" />
				<input type="hidden" name="parent" value="{$nodeID}"
			</form>
		</td>
</table>


<div>&nbsp;</div>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><div style="text-align: center">ID</div></th>
			<th scope="col" width="90%">Title</th>
			<th scope="col" nowrap="nowrap">Type</th>
			<th scope="col" nowrap="nowrap">Template</th>
			<th scope="col" colspan="3">&nbsp;</th>
		</tr>
	</thead>
	
	<tbody id="the-list">
		{section name=pages loop=$pages}
			<tr id='post-{$pages[pages].id}' class='{cycle values="alternate2,alternate"}'>
				{if $pages[pages].nav == true}
					<th scope="row" style="text-align: center" nowrap="nowrap">-</th>
				{else}
					<th scope="row" style="text-align: center" nowrap="nowrap">{$pages[pages].id}</th>
				{/if}

				{if $pages[pages].hidden == true}
					<td nowrap="nowrap"><em>{$pages[pages].name} (Hidden)</em></td>
				{else}
					<td nowrap="nowrap">{$pages[pages].name}</td>
				{/if}
				
				<td nowrap="nowrap">{$pages[pages].type}</td>
				<td nowrap="nowrap">{$pages[pages].template_name}</td>

				<td style="text-align:center"><a href="{$pages[pages].url}" target="_blank">View</a></td>
				{if $pages[pages].nav == true}
					<td style="text-align:center"><a href="/module/AdminCP_content/pageEditNav.php?{$pages[pages].id}">Edit</a></td>
				{else}
					<td style="text-align:center"><a href="/module/AdminCP_content/pageEditPage.php?{$pages[pages].id}">Edit</a></td>
				{/if}
				<td style="text-align:center"><a href="/module/AdminCP_content/pageDelete.php?{$pages[pages].id}" onClick="return confirm('Are you sure you wish to delete this page?');">Delete</a></td>
			</tr>
		{/section}
	</tbody>
</table>

</div>

{include file="admin/adminFooter.tpl"}