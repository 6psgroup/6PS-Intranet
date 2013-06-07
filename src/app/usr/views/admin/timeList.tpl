{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">
	<h2>{$block_title}</h2>

    {if $msg != ''}
		<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
	{/if}
	
	<a href="timeEdit.php?0">New Time Entry</a><br />
	
	{if $paginate.total > 0}
    	
		<br />

		<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><div style="text-align: center">ID</div></th>
					<th scope="col">Date <font size="1">(mm/dd/yyyy)</font></th>
					<th scope="col" width="90%">Description</th>
                    <th scope="col"><div style="text-align: center">Duration</div></th>
					<th scope="col" colspan="2">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody id="the-list">
				{section name=entries loop=$entries}
					<tr class='{cycle values="alternate2,alternate"}'>
						<td nowrap="nowrap">{$entries[entries].id}</td>
						<td nowrap="nowrap">{$entries[entries].bill_date|date_format:"%m/%d/%Y :: %I:%M %p"}</td>
                        <td nowrap="nowrap">{$entries[entries].description|truncate:75:"..."}</td>
                        <td nowrap="nowrap">{$entries[entries].duration} hours</td>
						
						{if $entries[entries].billed != 1}
							<td style="text-align:center"><a href="timeEdit.php?{$entries[entries].id}">Edit</a></td>
							<td style="text-align:center"><a href="timeDelete.php?{$entries[entries].id}" onClick="return confirm('Are you sure you wish to delete this time entry?');">Delete</a></td>
						{else}
							<td style="text-align:center" colspan="2"><a href="timeEdit.php?{$entries[entries].id}">View</a></td>
						{/if}
					</tr>
				{/section}
			</tbody>
		</table>

    	<br />

		{if $paginate.total >= $paginate.limit}
			<div style="font-size:10px; text-align:center">Items {$paginate.first}-{$paginate.last} out of {$paginate.total} displayed.</div>
			<div class="pagination">{paginate_prev text="Previous"} {paginate_middle format="page" page_limit="10" suffix="] "} {paginate_next text="Next"}</div>
		{/if}
	{/if}
</div>

{include file="admin/adminFooter.tpl"}