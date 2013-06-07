{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2>{$block_title}</h2>

<table class="widefat">
	<thead>
		<tr>
			<th scope="col"><div style="text-align: center">ID</div></th>
			<th scope="col" width="90%">Title</th>
			<th scope="col" nowrap="nowrap">Type</th>
			<th scope="col" colspan="3">&nbsp;</th>
		</tr>
	</thead>
	
	<tbody id="the-list">
		{section name=sections loop=$sections}
			<tr id='post-{$navTree[navTree].id}' class='{cycle values="alternate2,alternate"}'>
				<th scope="row" style="text-align: center" nowrap="nowrap">{$sections[sections].id}</th>
				<td nowrap="nowrap">{$sections[sections].title}</td>
				<td class="href_noUnderline" style="text-align:center;" nowrap="nowrap">
					{if $sections[sections].sort > 1}
						<a href="/module/AdminCP_content/navigationSortUp.php?{$sections[sections].id}">
							<img src="/images/uparrow.png" border="0" alt="Move Node Up" />
						</a>
					{else}
						<img src="/images/spacer.gif" border="0" width="12" />
					{/if}
					
					{if $sections[sections].navDown}
						<a href="/module/AdminCP_content/navigationSortDown.php?{$sections[sections].id}">
							<img src="/images/downarrow.png" border="0" alt="Move Node Down" />
						</a>
					{else}
						<img src="/images/spacer.gif" border="0" width="12" />
					{/if}
				</td>
				
				<td style="text-align:center"><a href="/module/AdminCP_content/home.php?{$navTree[navTree].id}">Edit</a></td>
			</tr>
		{/section}
	</tbody>
</table>

</div>

{include file="admin/adminFooter.tpl"}