{if count($pages)}

<div id="admin-bar" {if Core::IsLibraryAvailable('jquery')}class="admin-bar-collapsed"{/if}>
	{if Core::IsLibraryAvailable('jquery')}
		<a href="#" class="admin-bar-toggle" title="Exapnd/Collapse Bar">
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
	</ul>
</div>

	{if Core::IsLibraryAvailable('jquery')}
		{script library="jquery.cookie"}{/script}
		{script location="foot" src="js/admin/adminmenu-widget.js"}{/script}
	{/if}

{/if}