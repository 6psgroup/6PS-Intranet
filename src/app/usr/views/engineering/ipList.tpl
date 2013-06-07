{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

{if $msg != ''}
	<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
{/if}

<div class="wrap">
	<h2>{$block_title}</h2>
	<a href="ipAdd.php">New Subnet</a>
	
	<div align="center">
		{section name=types loop=$subnets}
			{if count($subnets[types].subnets) > 0}
				<h3 align="left">{$subnets[types].type}</h3>
				
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col" nowrap="nowrap">Network Address</th>
							<th scope="col" width="15%" nowrap="nowrap">CIDR</th>
							<th scope="col" width="15%" nowrap="nowrap">Subnet Mask</th>
							<th scope="col" width="15%" nowrap="nowrap">Inverted Subnet Mask</th>
							<th scope="col" colspan="2" width="15%" nowrap="nowrap">&nbsp;</th>
						</tr>
					</thead>
					
					<tbody id="the-list">
						{section name=subnet loop=$subnets[types].subnets}
							<tr id='post-{$pages[pages].id}' class='{cycle values="alternate2,alternate"}'>
								<td nowrap="nowrap">{$subnets[types].subnets[subnet].network}</td>
								<td nowrap="nowrap"><div align="center">/{$subnets[types].subnets[subnet].cidr}</div></td>
								<td nowrap="nowrap"><div align="center">{$subnets[types].subnets[subnet].mask}</div></td>
								<td nowrap="nowrap"><div align="center">{$subnets[types].subnets[subnet].inverted}</div></td>
				
								<td style="text-align:center"><a href="ipSubnetList.php?{$subnets[types].subnets[subnet].network}&{$subnets[types].subnets[subnet].cidr}">View</a></td>
								<td style="text-align:center"><a href="ipDelete.php?{$subnets[types].subnets[subnet].network}&{$subnets[types].subnets[subnet].cidr}" onClick="return confirm('Are you sure you wish to delete this subnet?');">Delete</a></td>
							</tr>
						{/section}
					</tbody>
				</table>
			{/if}
		{/section}
	</div>
</div>

{include file="admin/adminFooter.tpl"}