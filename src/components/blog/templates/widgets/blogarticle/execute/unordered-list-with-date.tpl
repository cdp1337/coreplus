{css src="assets/css/blog.css"}{/css}

<div class="blog-article-widget blog-article-widget-{$sort} blog-article-widget-ul-date">
	{if $title}
		<h3>{$title}</h3>
	{/if}

	<ul>
		{foreach $links as $l}
			<li class="blog-article-widget-item">
				{a href="`$l.baseurl`" class="blog-article-widget-link"}
					<span class="blog-article-widget-title">{$l.title}</span>
					<span class="blog-article-widget-date">{date format="SD" $l.published}</span>
				{/a}
			</li>
		{/foreach}
	</ul>
</div>