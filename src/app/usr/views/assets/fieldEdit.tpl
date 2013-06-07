{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2 id="add-new-user">{$block_title}</h2>

<div>

<form action="catEditProcess.php" method="post" name="EditPassCat">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<td align="right">
				<strong>Category Name:</strong>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" name="cat" value="{$cat}" size="50" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Parent:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=parent options=$catTree selected=$selParent}
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="id" value="{$id}" />
	<p class="submit">
		<input name="submit" type="submit" value="Submit &raquo;" />
	</p>
</form>

</div>
</div>

{include file="admin/adminFooter.tpl"}