<ul>
	{foreach from=$pages item=page}
		<li>
			{a href=$page->get('baseurl')}{$page->get('title')}{/a}
		</li>
	{/foreach}
</ul>