{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2 id="add-new-user">Edit Module</h2>

<div>

{include file="globals/tinyMCE.tpl"}


<form action="moduleEditProcess" method="post" name="EditModule">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<th scope="row" width="33%">Name</th>
			<td width="66%"><input name="name" type="text" value="{$name}" size="50" readonly="readonly" /></td>
		</tr>
		<tr>
			<th scope="row" colspan="2" style="text-align:left;">Module Body</th>
		</tr>
		<tr>
			<td colspan="2"><textarea name="body" rows="25" cols="80" />{$body}</textarea></td>
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