{if count($articles) == 0}
	<p class="message-info">This blog has no articles created yet, please try back later.</p>
{/if}

<div itemscope itemtype="http://schema.org/Blog">
	<h1>{$page.title}</h1>
	{if $page.description}
		<div itemprop="description" style="display:none;">{$page.description}</div>
	{/if}
	{foreach $articles as $article}
		<div class="blog-entry blog-article blog-article-status-{$article.status}" itemscope itemtype="http://schema.org/BlogPosting">
			<link itemprop="url" href="{link link="`$article.rewriteurl`"}"/>
			{a class="blog-entry-title" href="`$article.rewriteurl`" itemprop="name"}
				{$article.title}
			{/a}

			<div class="blog-entry-date" itemprop="dateCreated" datetime='{date format='c' date="`$article.created`"}'>Posted {date date="`$article.created`"}</div>

			{if $article.image}
				{img class="blog-image" src="public/blog/`$article.image`" width='75' height='75' itemprop="thumbnailUrl"}
			{/if}

			<p class="blog-entry-excerpt" itemprop="articleBody">
				{$article->getTeaser()}
				... {a class="blog-entry-read-more" href="`$article.rewriteurl`"}Read More{/a}
			</p>
		</div>
	{/foreach}
</div>