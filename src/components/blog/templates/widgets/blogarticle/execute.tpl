{css src="assets/css/blog.css"}{/css}

<div class="blog-article-widget blog-article-widget-{$sort}">
	{if $title}
		<h3>{$title}</h3>
	{/if}

	{foreach $links as $l}
		{a href="`$l.baseurl`" class="blog-article-widget-link"}
			{$l.title}
		{/a}
	{/foreach}
</div>