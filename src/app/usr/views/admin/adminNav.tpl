
<ul id="adminmenu">
	{section name=menuMain loop=$menuMain}
		<li
			{if $menuMain[menuMain][1] == $menuMainSel}
				class="current"
			{/if}
		>
			<a
				href="{$menuMain[menuMain][0]}"
				{if isset($menuMain[menuMain][2]) && $menuMain[menuMain][2] == true}
					target="_blank"
				{/if}
			>
				{$menuMain[menuMain][1]}
			</a>
		</li>
	{/section}
</ul>

<ul id="submenu">
	{section name=menuSub loop=$menuSub}
		<li
			{if $menuSub[menuSub][1] == $menuSubSel}
				class="current"
			{/if}
		>
			<a
				href="{$menuSub[menuSub][0]}"
				{if isset($menuSub[menuSub][2]) && $menuSub[menuSub][2] == true}
					target="_blank"
				{/if}
			>
				{$menuSub[menuSub][1]}
			</a>
		</li>
	{/section}
</ul>

<div id="main">
	<div id="user_info">
		<p class="user_text">
			Welcome {$headerUsername}! 
			[
				<a href="/intranet/module/AdminCP/logout.php" title="Log out of this account">Sign Out</a>
			]
		</p>
	</div>
<br clear="all" />