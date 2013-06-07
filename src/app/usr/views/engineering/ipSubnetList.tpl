{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

{if $msg != ''}
	<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
{/if}

<div class="wrap">
	<h2>{$block_title}</h2>
	
	
    <table width="350" align="center">
		<thead>
			<tr>
				<th colspan="3" scope="col" nowrap="nowrap">Key</th>
           	</tr>
        </thead>
        
		<tbody id="the-list">
	        <tr>
            	<td class="alternate4">IP Address In Use</td>
            </tr>
            <tr>
            	<td class="alternate5">IP Address Assigned to Cancelled Package</td>
            </tr>
            <tr>
            	<td class="alternate3">IP Address Available</td>
            </tr>
        </tbody>
    </table>
    
    <p>&nbsp;</p>
    
    <form action="ipEditProcessMulti.php" method="post" name="EditSubnet">
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" width="1%" nowrap="nowrap"></th>
                <th scope="col" nowrap="nowrap">Address</th>
				<th scope="col" width="15%" nowrap="nowrap">Assigned System</th>
				<th scope="col" width="15%" nowrap="nowrap">Assigned User</th>
				<th scope="col" width="15%" nowrap="nowrap">Assigned Package</th>
				<th scope="col" width="15%" nowrap="nowrap">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			{section name=address loop=$addresses}
				{if $addresses[address].billing_system > 0 && $addresses[address].billing_status == 1}
                	<tr id='post-{$pages[pages].id}' class="alternate4">
                {elseif $addresses[address].billing_system > 0 && $addresses[address].billing_status == 0}
                	<tr id='post-{$pages[pages].id}' class="alternate5">
                {else}
					<tr id='post-{$pages[pages].id}' class="alternate3">
				{/if}
                        <td nowrap="nowrap">
                        	<input type="checkbox" name="addresses[]" value="{$addresses[address].id}" />
                        </td>
                        <td nowrap="nowrap">{$addresses[address].address}</td>
                        <td nowrap="nowrap"><div align="center">{$addresses[address].system}</div></td>
                        <td nowrap="nowrap">
                            {if $addresses[address].billing_username != ''}
                                <div align="center"><a href="{$addresses[address].billing_userurl}" target="_blank">{$addresses[address].billing_username}</a></div>
                            {/if}
                        </td>
                        <td nowrap="nowrap">
                            <div align="center">
                                {$addresses[address].billing_package}
                            </div>
                        </td>
                        
                        {if $smarty.section.address.first !== true && $smarty.section.address.last !== true}
                            {if $addresses[address].billing_system != 0}
                                <td style="text-align:center"><a href="ipEdit.php?{$addresses[address].address}">Edit</a></td>
                            {else}
                                <td style="text-align:center"><a href="ipEdit.php?{$addresses[address].address}">Assign</a></td>
                            {/if}
                        {else}
                            <td>&nbsp;</td>
                        {/if}
				</tr>
			{/section}
		</tbody>
	</table>
    
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    
    <h2>Multiple Address Edit</h2>
    
    <p>Changes here will be applied to addresses selected above.</p>
    
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<th scope="row">
				Package (User):<br />
				<font size="1" color="#999999" style="font-weight:normal"><br />select only one one package type<br />(set others to "--NONE--")<br />&nbsp;<br />to revoke address, set all to "--NONE--"</font>
			</th>
			<td class="selectSmall">
				<table cellpadding="3" cellspacing="0" border="0" width="100%">
					<tr>
						<td align="right">
							<strong>Shared:</strong>
						</td>
						<td class="selectSmall" align="left">
							{html_options name=packagesShared options=$packagesShared selected=$selShared}
						</td>
					</tr>
					<tr>
						<td align="right">
							<strong>Reseller:</strong>
						</td>
						<td class="selectSmall" align="left">
							{html_options name=packagesReseller options=$packagesReseller selected=$selReseller}
						</td>
					</tr>
					<tr>
						<td align="right">
							<strong>VPS:</strong>
						</td>
						<td class="selectSmall" align="left">
							{html_options name=packagesVPS options=$packagesVPS selected=$selVPS}
						</td>
					</tr>
					<tr>
						<td align="right">
							<strong>Dedicated:</strong>
						</td>
						<td class="selectSmall" align="left">
							{html_options name=packagesDedicated options=$packagesDedicated selected=$selDedicated}
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<th scope="row">Notes:</th>
			<td><textarea name="notes" cols="65" rows="10">{$notes}</textarea></td>
		</tr>
	</table>
	
	<input type="hidden" name="id" value="{$id}" />
	<input type="hidden" name="address" value="{$address}" />
	<p class="submit">
		<input name="submit" type="submit" value="Submit &raquo;" />
	</p>
</form>
</div>

{include file="admin/adminFooter.tpl"}