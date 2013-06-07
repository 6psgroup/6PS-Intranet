{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">
	<h2>{$block_title}</h2>
	{if $msg != ''}
	<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
	<br />
	{/if}
	<a href="assetEdit.php?0&amp;{$selCat}">New Asset</a><br />&nbsp;
	
	<form action="assetList.php" method="post">
		Filter: {html_options name=cat options=$catTree selected=$selCat}
		<input name="submit" type="submit" value="Submit &raquo;" />
	</form>

	<div>&nbsp;</div>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><div style="text-align: center">ID</div></th>
				<th scope="col" width="90%">Name</th>
				<th scope="col" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			{section name=asset loop=$assets}
				<tr id='post-{$assets[asset].id}' class='{cycle values="alternate2,alternate"}'>
					<td nowrap="nowrap">{$assets[asset].id}</td>
					<td nowrap="nowrap">{$assets[asset].name}</td>
					
					<td style="text-align:center"><a href="assetEdit.php?{$assets[asset].id}&{$selCat}">Edit</a></td>
                    <td style="text-align:center"><a href="assetBarcode.php?{$assets[asset].id}" target="_blank">Barcode</a></td>
				</tr>
			{/section}
		</tbody>
	</table>
   
</div>

{include file="admin/adminFooter.tpl"}