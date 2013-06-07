{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

<h2>{$block_title}</h2>

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

{if $readonly != 1}
	<form action="timeEditProcess.php" method="post">
{/if}

	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<td align="right">
				<strong>Employee:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=employee options=$employees selected=$selEmployee}
			</td>
		</tr>
	
		<tr>
			<td align="right">
				<strong>Customer:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=users options=$users selected=$selUser}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Bill Date:</strong><br />
				<font size="-5" color="#999999">Date entry will be billed to customer</font>
			</td>
			<td class="selectSmall" align="left">
				{html_select_date prefix="bill" year_empty="" month_empty="" day_empty="" start_year=-1 end_year=+5 time=$bill_date}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Duration:</strong><br />
				<font size="-5" color="#999999">Hours (ex: 1.5 for 1:30)</font>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" name="duration" value="{$duration}" size="40" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Rate:</strong><br />
				<font size="-5" color="#999999">Refer to <a href="http://sites.google.com/a/6ps.com/wiki/Home/sales-marketing/billableservicesrateschedule" target="_blank" style="font-size:9px">Billable Services Rate Schedule</a></font>
			</td>
			<td class="selectSmall" align="left">
				$<input type="text" name="rate" value="{$rate}" size="39" />
			</td>
		</tr>
		
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		
		<tr>
			<td align="right">
				<strong>After Hours:</strong><br />
				<font size="-5" color="#999999">Bonus paid to technician for after hours work</font>
			</td>
			<td class="selectSmall" align="left">
				{if $afterhours != 1}
					<input type="checkbox" name="afterhours" value="checked" />
				{else}
					<input type="checkbox" name="afterhours" value="checked" checked="checked" />
				{/if}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>After Hours Fee:</strong><br />
				<font size="-5" color="#999999">After-hours fee billed to customer</font>
			</td>
			<td class="selectSmall" align="left">
				{if $afterhoursfee != 1}
					<input type="checkbox" name="afterhoursfee" value="checked" />
				{else}
					<input type="checkbox" name="afterhoursfee" value="checked" checked="checked" />
				{/if}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>After Hours Fee Amount:</strong><br />
				<font size="-5" color="#999999">Leave 0.00 for default fee</font>
			</td>
			<td class="selectSmall" align="left">
				$<input type="text" name="priceoverride" value="{$priceoverride}" size="39" />
			</td>
		</tr>
        
		<tr>
            <td align="right" colspan="2">&nbsp;</td>
        </tr>
        
		<tr>
			<td align="right">
				<strong>Description:</strong><br />
				<font size="-5" color="#999999">Warning: Displayed on customer invoice</font>
			</td>
			<td class="selectSmall" align="left">
				<textarea name="description" cols="40" rows="8">{$description}</textarea>
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="id" value="{$id}" />
{if $readonly != 1}
		<p class="submit">
			<input name="submit" type="submit" value="Submit &raquo;" />
		</p>
	</form>
{/if}

</div>

{include file="admin/adminFooter.tpl"}