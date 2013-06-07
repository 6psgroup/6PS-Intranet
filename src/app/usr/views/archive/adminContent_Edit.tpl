{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2 id="add-new-user">Edit Navigation</h2>

<div class="narrow">

<form action="{$action}" method="post" name="EditNavigation">
	<table class="editform" width="100%" cellspacing="2" cellpadding="5">
		<tr>
			<th scope="row" width="33%">Name</th>
			<td width="66%"><input name="name" type="text" value="{$name}" /></td>
		</tr>
		<tr>
			<th scope="row">Parent </th>
			<td>{html_options name=parent options=$parentTree selected=$selParent}</td>
		</tr>
		<tr>
			<th scope="row">Title (Public)</th>
			<td><input name="title" type="text" value="{$title}" /></td>
		</tr>
		<tr>
			<th scope="row">URL</th>
			<td><input name="url" type="text" value="{$url}" /></td>
		</tr>
		<tr>
			<th scope="row">Template</th>
			<td>{html_options name=template options=$templates selected=$selTemplate}</td>
		</tr>
	
		<tr>
			<th scope="row">Type</th>
			<td>{html_options name=type options=$type selected=$selType}</td>
		</tr>
	
		<tr>
			<th scope="row">Hidden</th>
			<td><input type="checkbox" name="hidden" value="checked" {$hidden} /></td>
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