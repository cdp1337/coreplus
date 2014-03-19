{css src="assets/css/blog.css"}{/css}

<div class="blog-widget blog-widget-{$sort}">
	{if $title}
		<h3>{$title}</h3>
	{/if}

	{foreach $links as $l}
		{a href="`$l.baseurl`" class="blog-widget-link"}
			{$l.title}
		{/a}
	{/foreach}
</div>