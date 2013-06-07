{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

    {if $msg != ''}
        <div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
        <br />
    {/if}
    
    <h2 id="add-new-user">{$block_title}</h2>
    
    <form action="catEditProcess.php" method="post" name="EditPassCat">
    
    <div>
        <table class="editform" cellspacing="2" cellpadding="5" align="center">
            <tr>
                <td align="right">
                    <strong>Category Name:</strong>
                </td>
                <td class="selectSmall" align="left">
                    <input type="text" name="catname" value="{$name}" size="50" />
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
        
        
    </div>

    <h3>Data Fields</h3>
	
    <p class="submit" style="text-align:left">
	    {html_options name=copyFields options=$copyTree}
        <input name="copy" type="submit" value="Copy Fields &raquo;" />
    </p>
    
    <p>&nbsp;</p>
    
	<table class="editform" cellspacing="2" cellpadding="5" align="center">
        <tr>
            <td align="center">
                <strong>Delete</strong>
            </td>
            <td align="center">
                <strong>Name</strong>
            </td>
            <td align="center">
                <strong>Type</strong>
            </td>
            <td align="center">
                <strong>Options</strong>
            </td>
            <td align="center">
                <strong>Required</strong>
            </td>
            <td align="center">
                <strong>Sort</strong>
            </td>
        </tr>
        
        {section name=field loop=$fields}
        	<tr>
            	<td align="center">
                	{if $fields[field].name != ''}
	                    <input type="checkbox" name="delete[{$fields[field].id}]" value="checked" {$fields[field].delete} /><br />
                    {/if}
                </td>
                <td align="center">
                    <input type="text" name="name[]" value="{$fields[field].name}" size="40" />
                </td>
                <td align="center">
                    {html_options name=type[] options=$types selected=$fields[field].type}
                </td>
                <td align="center">
                    <input type="text" name="options[]" value="{$fields[field].options}" size="35" />
                </td>
                <td align="center">
                    <input type="checkbox" name="required[{$fields[field].id}]" value="checked" {if $fields[field].required == 1}checked="checked"{/if} /><br />
                </td>
                <td align="center">
                	{if $fields[field].name != ''}
	                    <input type="text" name="sort[]" value="{$fields[field].sort}" size="3" /><br />
                    {/if}
                </td>
            </tr>
            <input type="hidden" name="fieldid[]" value="{$fields[field].id}" />
        {/section}
	</table>
    
    <input type="hidden" name="id" value="{$id}" />
    <p class="submit">
        <input name="submit" type="submit" value="Submit &raquo;" />
    </p>

	</form>
</div>

{include file="admin/adminFooter.tpl"}