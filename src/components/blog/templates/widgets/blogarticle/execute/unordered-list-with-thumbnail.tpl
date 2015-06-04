{css src="assets/css/blog.css"}{/css}

<div class="blog-article-widget blog-article-widget-{$sort} blog-article-widget-ul-thumbnail">
	{if $title}
		<h3>{$title}</h3>
	{/if}

	<ul>
		{foreach $links as $l}
			<li class="blog-article-widget-item">
				{a href="`$l.baseurl`" class="blog-article-widget-link"}
					{if $l->getImage()}
						{img class="blog-article-widget-img" file=$l->getImage() dimensions="50x25" itemprop="thumbnailUrl" alt="`$l.title|escape`" includemeta=0}
					{/if}
					<span class="blog-article-widget-title">{$l.title}</span>
				{/a}
			</li>
		{/foreach}
	</ul>
</div>