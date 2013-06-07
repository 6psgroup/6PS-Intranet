{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

{if $msg != ''}
<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
<br />
{/if}

<h2 id="add-new-user">Edit Page</h2>

<div>

{if $selType == 'static'}
	{include file="globals/tinyMCE.tpl}
{/if}

<form action="{$action}" method="post" name="EditPage">
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
		<tr>
			<th scope="row" width="33%">Name</th>
			<td width="66%"><input name="name" type="text" value="{$name}" /></td>
		</tr>
		<tr>
			<th scope="row">Parent</th>
			<td>{html_options name=parent options=$parentTree selected=$selParent}</td>
		</tr>
		<tr>
			<th scope="row">Title (Public)</th>
			<td><input name="title" type="text" value="{$title}" /></td>
		</tr>
		
		{if $selType == 'static'}
			<tr>
				<th scope="row" colspan="2" style="text-align:left;">Page Body</th>
			</tr>
			<tr>
				<td colspan="2"><textarea name="body" rows="25" cols="80" />{$body}</textarea></td>
			</tr>
		{elseif $selType == 'ap_feed' || $selType == 'ap_headline'}
			<tr>
				<th scope="row" valign="top">Newsgroups</th>
				<td>
					{html_checkboxes name='body' options=$newsgroups selected=$selNewsgroups separator='<br />'}
				</td>
			</tr>
		{elseif $selType == 'ap_search'}
			<tr>
				<th scope="row">Search Terms<br /><font size="-4">(space-seperate terms)</font></th>
				<td valign="top"><input name="body" type="text" value="{$body}" /></td>
			</tr>
		{/if}
		
		<tr>
			<th scope="row">URL</th>
			<td><input name="url" type="text" value="{$url}" /></td>
		</tr>
		<tr>
			<th scope="row">Meta Tag Keywords</th>
			<td><input name="keywords" type="text" value="{$keywords}" /></td>
		</tr>
		<tr>
			<th scope="row">Meta Tag Description</th>
			<td><input name="description" type="text" value="{$description}" /></td>
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
	
	<input type="hidden" name="parent_id" value="{$parent_id}" />
	<input type="hidden" name="id" value="{$id}" />
	<p class="submit">
		<input name="submit" type="submit" value="Submit &raquo;" />
	</p>
</form>

</div>
</div>

{include file="admin/adminFooter.tpl"}