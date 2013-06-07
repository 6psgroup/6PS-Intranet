{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

<h2 id="add-new-user">{$block_title}</h2>

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<div>

<form action="assetEditProcess.php" method="post">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<td align="right">
				<strong>(*) Name:</strong>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" size="40" name="name" value="{$asset.name}" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>(*) Category:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=cat options=$catTree selected=$asset.cat}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>(*) Asset Account:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=assetaccount options=$assetaccounts selected=$asset.asset_account}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>(*) Depreciation Account:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=depreciationaccount options=$depreciationaccounts selected=$asset.asset_depreciation}
			</td>
		</tr>
        <tr>
			<td align="right">
				<strong>(*) Commission Date:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_select_date prefix=commission year_empty="" month_empty="" day_empty="" start_year=-40 end_year=+40 time=$asset.commission}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Decommission Date:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_select_date prefix=decommission year_empty="" month_empty="" day_empty="" start_year=-40 end_year=+40 time=$asset.decommission}
			</td>
		</tr>
        <tr>
			<td align="right">
				<strong>(*) Useful Life:</strong><br /><font size="-3">(in months)</font>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" size="40" name="usefullife" value="{$asset.usefullife}" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>(*) Initial Value:</strong><br /><font size="-3">($)</font>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" size="40" name="initialvalue" value="{$asset.initialvalue}" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>(*) Residual Value:</strong><br /><font size="-3">($; smaller than initial value)</font>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" size="40" name="residualvalue" value="{$asset.residualvalue}" />
			</td>
		</tr>
        
        
        {section name=field loop=$fields}
	        {assign var=fieldid value=`$running_total+$some_array[row].some_value`}
        	<tr>
                <td align="right">
                    <strong>{if $fields[field].required == 1}(*){/if} {$fields[field].name}:</strong>
                </td>
                <td class="selectSmall" align="left">
                    {if $fields[field].type == 'text'}
                    	<input type="text" size="40" name="fields[{$fields[field].id}][data]" value="{$fieldData[field]}" />
                    {/if}
                    
                    
                    {if $fields[field].type == 'select'}
                    	{html_options name=fields[`$fields[field].id`][data] options=$fields[field].options selected=$fieldData[field] field_array=name}
                    {/if}
                    
                    
                    {if $fields[field].type == 'date'}
                    	{html_select_date prefix="fields[`$fields[field].id`][data][" year_empty="" month_empty="" day_empty="" start_year=-1 end_year=+5 suffix=']' time=$fieldData[field]}	
                    {/if}
                    
                    
                    {if $fields[field].type == 'checkbox'}
                    	{html_checkboxes name=fields[`$fields[field].id`][data] values=$fields[field].options output=$fields[field].options selected=$fieldData[field] separator='<br q />' }
                    {/if}
                    
                     
					{if $fields[field].type == 'radio'}
                    	{html_radios name=fields[`$fields[field].id`][data] values=$fields[field].options output=$fields[field].options selected=$fieldData[field] separator='<br q />' }
                    {/if}
                    
                    
                    {if $fields[field].type == 'textarea'}
                    	<textarea name="fields[{$fields[field].id}][data]" cols="45" rows="8">{$fieldData[field]}</textarea>
                    {/if}
                </td>
            </tr>
            
            <input type="hidden" name="fields[{$fields[field].id}][type]" value="{$fields[field].type}" />
            <input type="hidden" name="fields[{$fields[field].id}][id]" value="{$fields[field].id}" />
        {/section}
		
	</table>
	
	<input type="hidden" name="id" value="{$id}" />
	<p class="submit">
		<input name="submit" type="submit" value="Submit &raquo;" />
	</p>
</form>

<p><strong>(*)</strong> - Required field</p>

</div>
</div>

{include file="admin/adminFooter.tpl"}