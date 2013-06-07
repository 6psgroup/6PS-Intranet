{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2 id="add-new-user">{$block_title}</h2>

<div>

<form action="ipEditProcess.php" method="post" name="EditSubnet">
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
</div>

{include file="admin/adminFooter.tpl"}