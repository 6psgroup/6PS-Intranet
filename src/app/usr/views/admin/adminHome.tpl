{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">
	<h2>{$block_title}</h2>
    
    <h3>Bugs</h3>
    
    <p>
    	If you find a bug in this system, it is very important that you report it. To 
        report a bug go to <a href="http://bugs.servermotion.com/" target="_blank">
        http://bugs.servermotion.com/</a> and sign up for an account. Email Chris so
        he can add you to the project to allow you to report bugs. Once added to the
        project, click "Report Issue" and fill out the bug report form.
    </p>
    
    <h3>TODO</h3>
    
    <p>
    	<ul>
        	<li>Change Yubikey authenitcation to be local encryption instead of SOA</li>
            <li>Port to CodeIgniter</li>
            <li>
            	Wiki
            	<ul>
                	<li>Install MediaWiki</li>
                    <li>Theme to match layout of Intranet</li>
                    <li>Intragrate into menu</li>
                    <li>Integrate authentication tokens for signle-signon</li>
                </ul>
            </li>
            <li>
	            IP System
                <ul>
                	<li>Add support for "Other" package types (such as colo customers)</li>
                </ul>
			</li>
            <li>
            	Time Entries
                <ul>
                	<li>Re-write integration into billing system for automated invoicing</li>
                    <li>Make searchable</li>
                    <li>Impliment supervisor approval system for time entries</li>
                    <li>Once billed or approved, mark time entry as read-only</li>
                </ul>
            </li>
            <li>Develop way to manage software licenses and reporting</li>
            <li>Develop way to automatically audit IP addresses for unathorized/undocumented use</li>
            <li>Develop asset tracking system <font size="1">(see TODO under Assets section for more)</font></li>
            <li>
            	Bugs
                <ul>
                	<li>Bug #705: Expired session remains logged in</li>
                	<li>Bug #699: No warning when missing yubikey and password for edits</li>
                    <li>Bug #488: Validate Description on time entries</li>
                    <li>Bug #475: Breadcrumb Navigation</li>
                    <li>Bug #451: Validate Description on time entries</li>
                </ul>
            </li>
        </ul>
    </p>
</div>

{include file="admin/adminFooter.tpl"}