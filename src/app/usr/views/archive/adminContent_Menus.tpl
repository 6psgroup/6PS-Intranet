{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2>{$block_title}</h2>


<div>&nbsp;</div>
	<table class="narrow">
		<thead>
			<tr>
				<th scope="col"><div style="text-align: center">ID</div></th>
				<th scope="col" width="90%">Name</th>
				<th scope="col" colspan="1">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody id="the-list">
			{section name=menus loop=$menus}
				<tr id='post-{$menus[menus].id}' class='{cycle values="alternate2,alternate"}'>
					<th scope="row" style="text-align: center" nowrap="nowrap">{$menus[menus].id}</th>
					<td nowrap="nowrap">{$menus[menus].name}</td>
					<td style="text-align:center"><a href="/module/AdminCP_content/menuEdit.php?{$menus[menus].id}">Edit</a></td>
				</tr>
			{/section}
		</tbody>
	</table>
</div>

{include file="admin/adminFooter.tpl"}