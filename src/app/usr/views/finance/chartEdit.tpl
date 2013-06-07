{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

<h2>{$block_title}</h2>

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<form action="chartEditProcess.php" method="post">
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
				<strong>Account Number:</strong>
			</td>
			<td class="selectSmall" align="left">
				<input type="text" name="num" value="{$num}" size="50" />
			</td>
		</tr>
        <tr>
			<td align="right">
				<strong>Type:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=type options=$types selected=$selType}
			</td>
		</tr>
		<tr>
			<td align="right">
				<strong>Sub-Account of:</strong>
			</td>
			<td class="selectSmall" align="left">
				{html_options name=parent options=$accounts selected=$selParent}
			</td>
		</tr>
		 <tr>
			<td align="right">
				<strong>Disabled:</strong>
			</td>
			<td class="selectSmall" align="left">
				{if $disabled == 1}
					<input type="checkbox" name="disabled" value="checked" checked="checked" />
				{else}
					<input type="checkbox" name="disabled" value="checked" />
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