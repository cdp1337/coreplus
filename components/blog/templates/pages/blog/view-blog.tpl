{if count($articles) == 0}
<p class="message-info">This blog has no articles created yet, please try back later.</p>
{/if}

{foreach $articles as $article}
	<div class="blog-entry">
		{a class="blog-entry-title" href="`$article.rewriteurl`"}
			{$article.title}
		{/a}

		<div class="blog-entry-date">Posted {date date="`$article.created`"}</div>

		<p class="blog-entry-excerpt">
			{if $article.description}
					{$article.description}
			{else}
					{$article.body|truncate:500}
			{/if}
			...{a class="blog-entry-read-more" href="`$article.rewriteurl`"}Read More{/a}
	</div>
{/foreach}