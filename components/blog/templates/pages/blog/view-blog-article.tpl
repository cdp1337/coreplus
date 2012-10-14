<div itemscope itemtype="http://schema.org/BlogPosting" class="blog-article" xmlns="http://www.w3.org/1999/html">
	<div class="blog-article-posting-stats">
		{if $author}
			<div class="blog-article-author">
					{if $author->get('avatar')}
						{img src="public/user/`$author->get('avatar')`" width="64" height="64" itemprop="image"}
					{/if}
					By {a href="/user/view/`$author->get('id')`" rel="author"}
						{$author->getDisplayName()}
					{/a}
			</div>
		{/if}
		<div class="blog-article-date" itemprop="dateCreated" datetime='{date format='c' date="`$article.created`"}'>Posted {date date="`$article.created`"}</div>
	</div>


	{if $article.image}
		{img class="blog-article-img" src="public/blog/`$article.image`" width='620' height='400' itemprop="thumbnailUrl"}
	{/if}

	<div class="blog-article-body" itemprop="articleBody">
		{$article.body}
	</div>

</div>

{*
<script type='text/javascript' src='http://zor.livefyre.com/wjs/v1.0/javascripts/livefyre_init.js'></script>
<script type='text/javascript'>
	var fyre = LF({
		site_id:308829
	});
</script>
*}