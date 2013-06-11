<div itemscope itemtype="http://schema.org/BlogPosting" class="blog-article" xmlns="http://www.w3.org/1999/html">

	<h1>{$article.title}</h1>

	<div class="blog-article-posting-stats">
		{if $author && Core::IsComponentAvailable('user-social')}
			{widget baseurl="/userprofile/badge" user="$author" title="Posted By" orientation="right"}
		{/if}

		{if $article.published}
			<meta itemprop="dateCreated" content="{date format='c' date="`$article.published`"}"/>
			<div class="blog-article-date">Posted {date date="`$article.published`"}</div>
		{else}
			<div class="blog-article-date">Not Published</div>
		{/if}
	</div>


	{if $article.image}
		{img class="blog-article-img" src="public/blog/`$article.image`" width='620' height='400' itemprop="thumbnailUrl" alt="`$article.title|escape`"}
	{/if}

	<div class="blog-article-body" itemprop="articleBody">
		{$body}
	</div>

	{widget baseurl="/tags/display" page=$article->getLink('Page') title="Tags:" }

</div>

{widgetarea name="After Blog Content"}