{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

{if $msg != ''}
	<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
{/if}

<div class="wrap">
	<h2>{$block_title}</h2>
	
	<div style="text-align:left"><a href="catEdit.php?0">New Category</a><br />&nbsp;</div>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><div style="text-align: center">ID</div></th>
				<th scope="col" width="90%">Title</th>
				<th scope="col"><div style="text-align: center">Sort</div></th>
				<th scope="col" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			{section name=catTree loop=$catTree}
				<tr id='post-{$catTree[catTree].id}' class='{cycle values="alternate2,alternate"}'>
					<th scope="row" style="text-align: center" nowrap="nowrap">{$catTree[catTree].id}</th>
					<td nowrap="nowrap">{$catTree[catTree].cat}</td>
					<td class="href_noUnderline" style="text-align:center;" nowrap="nowrap">
						{if $catTree[catTree].navUp == true}
							<a href="/intranet/module/Passwords/catSortUp.php?{$catTree[catTree].id}">
								<img src="/intranet/images/uparrow.png" border="0" alt="Move Node Up" />
							</a>
						{else}
							<img src="/intranet/images/spacer.gif" border="0" width="12" />
						{/if}
						
						{if $catTree[catTree].navDown == true}
							<a href="/intranet/module/Passwords/catSortDown.php?{$catTree[catTree].id}">
								<img src="/intranet/images/downarrow.png" border="0" alt="Move Node Down" />
							</a>
						{else}
							<img src="/intranet/images/spacer.gif" border="0" width="12" />
						{/if}
					</td>
					<td style="text-align:center"><a href="/intranet/module/Passwords/catEdit.php?{$catTree[catTree].id}">Edit</a></td>
					<td style="text-align:center"><a href="/intranet/module/Passwords/catDelete.php?{$catTree[catTree].id}" onClick="return confirm('Are you sure you wish to delete this category (and all items underneith it)?');">Delete</a></td>
				</tr>
			{/section}
		</tbody>
	</table>
</div>

{include file="admin/adminFooter.tpl"}