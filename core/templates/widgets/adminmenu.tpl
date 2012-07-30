{if count($pages)}

	{css src="css/admin/adminbar.css"}{/css}

	<div id="admin-bar" {if Core::IsLibraryAvailable('jquery')}class="admin-bar-collapsed"{/if}>
		{if Core::IsLibraryAvailable('jquery')}
			<a href="#" class="admin-bar-toggle" title="Expand/Collapse Bar">
				<i class="icon-chevron-right"></i>
				<i class="icon-chevron-right"></i>
				<i class="icon-chevron-left"></i>
				<i class="icon-chevron-left"></i>
			</a>
		{/if}
		<ul>
			{foreach from=$pages item=page}
				<li>
					{a href=$page->get('baseurl')}{$page->get('title')}{/a}
				</li>
			{/foreach}
			<li>{a href="/user/logout"}Logout{/a}</li>
		</ul>
	</div>

	{if Core::IsLibraryAvailable('jqueryui')}
		{script library="jqueryui"}{/script}
		{script library="jquery.cookie"}{/script}
		{script location="foot" src="js/admin/adminmenu-widget.js"}{/script}
	{/if}

{/if}