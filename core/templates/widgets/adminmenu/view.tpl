{if count($pages)}

	{css src="css/admin/adminbar.css"}{/css}

<div id="admin-bar">
	<ul>
		<li class="has-sub">
			<i class="icon-cogs"></i>
			<ul class="sub-menu">
				{foreach from=$pages item=page}
					{if strpos($page->get('baseurl'), '/volleyball') === false}
						<li>
							{a href=$page->get('baseurl')}{$page->get('title')}{/a}
						</li>
					{/if}
				{/foreach}
			</ul>
		</li>
	</ul>
	{*
	<ul>
		{foreach from=$pages item=page}
			{if strpos($page->get('baseurl'), '/volleyball') !== false}
				<li>
					{a href=$page->get('baseurl')}{$page->get('title')}{/a}
				</li>
			{/if}
		{/foreach}
	</ul>
	*}

	{widget baseurl="/user/login"}

</div>

	{if Core::IsLibraryAvailable('jqueryui')}
		{script library="jqueryui"}{/script}
		{script library="jquery.cookie"}{/script}
	{*script location="foot" src="js/admin/adminmenu-widget.js"}{/script*}
	{/if}

{/if}