{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

<h2>{$block_title}</h2>

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<form action="taskEditProcess.php" method="post">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<td align="right">
				<strong>Name:</strong>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" name="name" value="{$name}" size="50" />
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Parent:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=parent options=$tasks selected=$parent}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>User:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=user options=$users selected=$user}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Private:</strong>
			</td>
			<td class="selectSmall" align="left">
				{if $private == 1}
					<input type="checkbox" name="private" value="checked" checked="checked" />
				{else}
					<input type="checkbox" name="private" value="checked" />
				{/if}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Completed:</strong>
			</td>
			<td class="selectSmall" align="left">
				{if $enabled != 1}
					<input type="checkbox" name="enabled" value="checked" checked="checked" />
				{else}
					<input type="checkbox" name="enabled" value="checked" />
				{/if}
			</td>
		</tr>
        <tr>
            <td align="right" colspan="2">&nbsp;</td>
        </tr>
        <tr>
			<td align="right">
				<strong>Description:</strong>
			</td>
			<td class="selectSmall" align="left">
				<textarea name="notes" cols="40" rows="8">{$notes}</textarea>
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="id" value="{$id}" />
	<p class="submit">
		<input name="submit" type="submit" value="Submit &raquo;" />
	</p>
</form>

</div>

{include file="admin/adminFooter.tpl"}