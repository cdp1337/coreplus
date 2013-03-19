{css src="assets/css/blog.css"}{/css}

{if count($articles) == 0}
	<p class="message-info">This blog has no articles created yet, please try back later.</p>
{/if}

{$filters->pagination()}
<div itemscope itemtype="http://schema.org/Blog" class="single-blog-listing blog-listing">
	<h1>{$page.title}</h1>

	{if $page.description}
		<div itemprop="description" style="display:none;">{$page.description}</div>
	{/if}

	{foreach $articles as $article}
		<div class="blog-article blog-article-status-{$article.status}" itemscope itemtype="http://schema.org/BlogPosting">
			<link itemprop="url" href="{link link="`$article.rewriteurl`"}"/>
			{a class="blog-article-title" href="`$article.rewriteurl`" itemprop="name"}
				{$article.title}
			{/a}

			{if $article.published}
				<meta itemprop="dateCreated" content="{date format='c' date="`$article.published`"}"/>
				<div class="blog-article-date">Posted {date date="`$article.published`"}</div>
			{else}
				<div class="blog-article-date">Not Published</div>
			{/if}


			<div class="blog-article-image">
				{img placeholder="blog" src="public/blog/`$article.image`" width='75' height='75' itemprop="thumbnailUrl"}
			</div>


			<p class="blog-article-excerpt" itemprop="articleBody">
				{$article->getTeaser()}
				... {a class="blog-article-read-more" href="`$article.rewriteurl`"}Read More{/a}
			</p>

			<div class="clear"></div>
		</div>
	{/foreach}
</div>

<!-- This is just the waypoint trigger to know when to load the next set of results! -->
<div id="bottomofthelisting"></div>

{script library="jquery.waypoints"}{/script}
{script location="foot" src="assets/js/blog-waypoint-scroll.js"}{/script}