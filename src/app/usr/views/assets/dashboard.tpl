{include file="admin/adminHeader.tpl"}
{include file="admin/adminNav.tpl"}

<div class="wrap">

    <h2 id="add-new-user">{$block_title}</h2>
    
    {if $msg != ''}
    <div id="message" class="updated fade"><p><strong>{$msg}</strong></p></div>
    <br />
    {/if}
    
    <h3>How this works</h3>
    
    <p>Welcome to the 6PS asset manager. Once fully implemented, this system will contain data about all company-wide assets. This isn’t limited to just servers; this means everything – spare parts, office furniture, office equipment. All assets will be tagged with a label that contains a barcode, the ID and the name of the asset.</p>

    <p>Assets are grouped into <em>categories</em>. There can be an unlimited of categories, and they can be organized into a nested tree structure. Each asset category has a set of <em>data fields</em> that all assets in a particular category share. For example, all "Dedicated Servers" might have a field called "RAID Controller." The name, type and data contained in this field is individually controlled for each category. [For an example of this, click Categories in the menu above and edit the "Test" category.]</p>

    <p>
        In addition to the customizable data fields for each categories, all assets have a number of predefined fields detailed below:
        <ul>
            <li><strong>Name:</strong> This is the name of the asset and will be shown in the asset list and on the asset tag physically attached to the asset.</li>
            <li><strong>Asset Account:</strong> This is the financial accounting asset account the asset belongs to. This is used for financial reporting purposes and is the location on the balance sheet that the value of this asset will contribute to.</li>
            <li><strong>Depreciation Account:</strong> This is financial accounting contra-asset account that depreciation charges are accumulated to for this asset. As the asset is depreciated over its useful life, the amounts accumulated in this account will decrease the net value of the asset. This is also a balance sheet account, for financial reporting purposes.</li>
            <li><strong>Commission Date:</strong> This is the date the asset was first put into use. Unless specifically advised to do otherwise by accounting, this should be the date the asset was purchased.</li>
            <li><strong>Decommission Date:</strong> This is the date the asset was abandoned, scrapped, or sold. If the asset is still in use, this should be blank.</li>
            <li><strong>Useful Life:</strong> This is the period of time over which the asset will be depreciated. Accounting will provide this value for different types of assets, and it is important that this remain consistent for similar assets as this should generally match tax laws for asset depreciation schedules.</li>
            <li><strong>Initial Value:</strong> This is the initial cost of the asset. This is not the fair market value; this is the value the asset is accounted for as, and as such is accounted for at cost.</li>
            <li><strong>Residual Value:</strong> This is the amount the asset is expected to be worth at the end of its useful life. This is only an approximate value. When in doubt, select a lower number within an acceptable range.</li>
        </ul>
	</p>
    
    
    <h3>TODO</h3>
    
    <p>
    	<ul>
        	<li>Add search for asset list. This is also where the barcode data will be input to quickly jump to that asset.</li>
            <li>Add assembly support; for example, the ability to take spare parts and add to a server</li>
            <li>Validation of required fields</li>
            <li>
            	Depreciation calculation
                <ul>
                	<li>Support for double-declining and straight-line</li>
                    <li>Support for changing useful life, residual value mid-life cycle</li>
                    <li>Support for early decommission (and calculation of loss/gain thereof)</li>
                </ul>
            </li>
            <li>Add audit log to track changes to assets</li>
            <li>Add ability to assign assets to customers</li>
            <li>Add ability to search assets assigned to customers</li>
            <li>Add ability to hide decommissioned assets from asset list view</li>
            <li>Add pagination</li>
            <li>Dashboard
            	<ul>
                	<li>Add a quick view of available servers to dashboard</li>
            		<li>Add a recent activity log to dashboard</li>
                    <li>Add a spare parts list</li>
				</ul>
            </li>
            <li>Ability to create a new asset that is a copy of an existing asset</li>
            <li>Verify orphaned data fields remain with asset even when not defined in category (as in, when an asset is moved to another category)</li>
        </ul>
    </p>
</div>

{include file="admin/adminFooter.tpl"}