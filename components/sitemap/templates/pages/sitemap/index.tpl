<ul>
	{foreach $pages as $page}
		<li>
			<a href="{$page->getResolvedURL()}" title="{$page.title}">
				{$page.title}
			</a>
		</li>
	{/foreach}
</ul>
