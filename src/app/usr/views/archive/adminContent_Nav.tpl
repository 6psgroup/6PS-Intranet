{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2>{$block_title}</h2>

<div style="text-align:right"><a href="navNew.php?0">New Root-Level Node</a><br />&nbsp;</div>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><div style="text-align: center">ID</div></th>
			<th scope="col" width="90%">Title</th>
			<th scope="col" nowrap="nowrap">Type</th>
			<th scope="col" nowrap="nowrap">Template</th>
			<th scope="col"><div style="text-align: center">Sort</div></th>
			<th scope="col" colspan="3">&nbsp;</th>
		</tr>
	</thead>
	
	<tbody id="the-list">
		{section name=navTree loop=$navTree}
			<tr id='post-{$navTree[navTree].id}' class='{cycle values="alternate2,alternate"}'>
				<th scope="row" style="text-align: center" nowrap="nowrap">{$navTree[navTree].id}</th>
				<td nowrap="nowrap">{$navTree[navTree].name}</td>
				<td nowrap="nowrap">{$navTree[navTree].type}</td>
				<td nowrap="nowrap">{$navTree[navTree].template_name}</td>
				
                <td class="href_noUnderline" style="text-align:center;" nowrap="nowrap">
					{if $navTree[navTree].sort > 1}
						<a href="/module/AdminCP_content/navigationSortUp.php?{$navTree[navTree].id}">
							<img src="/images/uparrow.png" border="0" alt="Move Node Up" />
						</a>
					{else}
						<img src="/images/spacer.gif" border="0" width="12" />
					{/if}
					
					{if $navTree[navTree].navDown}
						<a href="/module/AdminCP_content/navigationSortDown.php?{$navTree[navTree].id}">
							<img src="/images/downarrow.png" border="0" alt="Move Node Down" />
						</a>
					{else}
						<img src="/images/spacer.gif" border="0" width="12" />
					{/if}
				</td>
				
                <td style="text-align:center"><a href="/module/AdminCP_content/navNew.php?{$navTree[navTree].id}">New</a></td>
				<td style="text-align:center"><a href="/module/AdminCP_content/navEdit.php?{$navTree[navTree].id}">Edit</a></td>
				<td style="text-align:center"><a href="/module/AdminCP_content/navDelete.php?{$navTree[navTree].id}" onClick="return confirm('Are you sure you wish to delete this navigation item (and all items underneith it)?');">Delete</a></td>
			</tr>
		{/section}
	</tbody>
</table>

</div>

{include file="admin/adminFooter.tpl"}