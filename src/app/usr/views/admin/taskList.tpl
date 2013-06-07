{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">
	<h2>{$block_title}</h2>
	
	{if $msg != ''}
		<div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
	{/if}
	
	<a href="taskEdit.php?0&{$user}">New Task</a><br />
	{if $user == $currentUser}
		{if $showDisabled != true}
			<a href="taskList.php?1&{$user}">Show All Tasks</a><br />&nbsp;
		{else}
			<a href="taskList.php?0&{$user}">Show Active Tasks</a><br />&nbsp;
		{/if}
	{else}
		&nbsp;
	{/if}
	
	<form action="" method="post">
		User: {html_options name=user options=$users selected=$selUser}
		<input name="submit" type="submit" value="Submit &raquo;" />
	</form>
	
	<div>&nbsp;</div>
	<form action="taskMarkCompleted" method="post">
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col"><div style="text-align: center">Completed</div></th>
					<th scope="col" width="90%">Name</th>
					<th scope="col"><div style="text-align: center">Sort</div></th>
					<th scope="col">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody id="the-list">
				{section name=tasks loop=$tasks}
					<tr class='{cycle values="alternate2,alternate"}'>
						<td nowrap="nowrap" align="center">
							<input type="checkbox" name="tasks[{$tasks[tasks].id}]" value="checked" {if $tasks[tasks].enabled == false}checked="checked"{/if} />
							<input type="hidden" name="taskids[{$tasks[tasks].id}]" value="{$tasks[tasks].id}" />
						</td>
						<td nowrap="nowrap">{$tasks[tasks].name}</td>
						<td class="href_noUnderline" style="text-align:center;" nowrap="nowrap">
							{if $tasks[tasks].navUp == true}
								<a href="taskSortUp.php?{$tasks[tasks].id}">
									<img src="/intranet/images/uparrow.png" border="0" alt="Move Node Up" />
								</a>
							{else}
								<img src="/intranet/images/spacer.gif" border="0" width="12" />
							{/if}
							
							{if $tasks[tasks].navDown == true}
								<a href="taskSortDown.php?{$tasks[tasks].id}">
									<img src="/intranet/images/downarrow.png" border="0" alt="Move Node Down" />
								</a>
							{else}
								<img src="/intranet/images/spacer.gif" border="0" width="12" />
							{/if}
						</td>
						
						<td style="text-align:center"><a href="taskEdit.php?{$tasks[tasks].id}">Edit</a></td>
					</tr>
				{/section}
			</tbody>
			
			<tr>
				<td colspan="4" align="center">
					<input name="submit" type="submit" value="Submit &raquo;" />
				</td>
			</tr>
		</table>

		<input type="hidden" name="user" value="{$user}" />
		<input type="hidden" name="showDisabled" value="{$showDisabled}" />
	</form>
</div>

{include file="admin/adminFooter.tpl"}