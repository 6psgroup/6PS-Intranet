{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">
	<h2>{$block_title}</h2>

    {if $msg != ''}
		<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
	{/if}
	
	<a href="timeEdit.php?0">New Time Entry</a><br />
	
	<br />
	
	<form action="" method="post">
		User: {html_options name=selUser options=$users selected=$selUser}<br />
		Start: {html_select_date prefix="start" year_empty="" month_empty="" day_empty="" start_year=-1 end_year=+5 time=$startDate}<br />
		End: {html_select_date prefix="end" year_empty="" month_empty="" day_empty="" start_year=-1 end_year=+5 time=$endDate}<br />&nbsp;<br />
		<input name="submit" type="submit" value="Submit &raquo;" />
	</form>
	
	<br />
	
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><div style="text-align: center">Billed</div></th>
				<th scope="col"><div style="text-align: center">ID</div></th>
				<th scope="col" width="90%">Date</th>
				<th scope="col"><div style="text-align: center">Duration</div></th>
				<th scope="col" nowrap="nowrap"><div style="text-align: center">After-Hours</div></th>
				<th scope="col" colspan="1">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			{section name=entries loop=$entries}
				<tr class='{cycle values="alternate2,alternate"}'>
					{if $entries[entries].billing_system != 0}
						<td nowrap="nowrap" align="center"><input name="billed" type="checkbox" {if $entries[entries].billed == 1}checked="checked"{/if} disabled="disabled" /></td>
					{else}
						<td nowrap="nowrap" align="center">&nbsp;</td>
					{/if}
					<td nowrap="nowrap">{$entries[entries].id}</td>
					<td nowrap="nowrap">{$entries[entries].bill_date|date_format:"%m/%d/%Y :: %I:%M %p"}</td>
					<td nowrap="nowrap">{$entries[entries].duration} hours</td>
					<td nowrap="nowrap" align="center"><input name="afterhours" type="checkbox" {if $entries[entries].afterhours == 1}checked="checked"{/if} disabled="disabled" /></td>
					
					{if $entries[entries].billed != 1}
						<td style="text-align:center"><a href="timeEdit.php?{$entries[entries].id}">Edit</a></td>
					{else}
						<td style="text-align:center"><a href="timeEdit.php?{$entries[entries].id}">View</a></td>
					{/if}
				</tr>
			{/section}
			
			{if $totalHours > 0}
				<tr class='{cycle values="alternate2,alternate"}'>
					<td nowrap="nowrap" colspan="3" align="right"><strong>Total Hours:</strong></td>
					<td nowrap="nowrap" colspan="3">{$totalHours|string_format:"%.2f"}</td>
				</tr>
				<tr class='{cycle values="alternate2,alternate"}'>
					<td nowrap="nowrap" colspan="3" align="right"><strong>Total After-Hours Hours:</strong></td>
					<td nowrap="nowrap" colspan="3">{$totalAfterHours|string_format:"%.2f"}</td>
				</tr>
				<tr class='{cycle values="alternate2,alternate"}'>
					<td nowrap="nowrap" colspan="3" align="right"><strong>Total Billed Hours:</strong></td>
					<td nowrap="nowrap" colspan="3">{$totalBilledHours|string_format:"%.2f"} ({$totalBilledPercent|string_format:"%.2f"}%)</td>
				</tr>
			{/if}
		</tbody>
	</table>

		<br />
</div>

{include file="admin/adminFooter.tpl"}