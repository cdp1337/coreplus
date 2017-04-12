{css src="assets/css/blog.css"}{/css}

{if count($articles) == 0}
	<p class="message-info">This blog has no articles created yet, please try back later.</p>
{/if}

{$filters->pagination()}
<div class="multi-blog-listing blog-listing">

	{foreach $articles as $article}
		{assign var='blog' value=$article->getParent()}

		<div class="blog-article blog-article-status-{$article.status}" itemscope itemtype="http://schema.org/BlogPosting">
			<link itemprop="url" href="{link $article.baseurl}"/>
			
			{if $article->getImage()}
				<div class="blog-article-image">
					{img placeholder="blog" file=$article->getImage() dimensions="90x80^" itemprop="thumbnailUrl"}
				</div>
			{/if}
			
			<div class="blog-article-content">
				<a {if $blog.type == 'remote'} target="_blank"{/if} class="blog-article-title" href="{link $article.baseurl}" itemprop="name">
					{$article.title}
				</a>

				{if $article->isPublished()}
					<meta itemprop="dateCreated" content="{date format='c' date="`$article.published`"}"/>
					<div class="blog-article-date">Posted {date date="`$article.published`"}</div>
				{else}
					<div class="blog-article-date">Not Published</div>
				{/if}

				{if $article->getTeaser()}
					<p class="blog-article-excerpt" itemprop="articleBody">
						{$article->getTeaser()}
						...<a {if $blog.type == 'remote'} target="_blank"{/if} class="blog-article-read-more" href="{link $article.baseurl}">read more</a>
					</p>
				{elseif $manager}
					<p class="blog-article-excerpt message-tutorial">
						This article does not have any meta description (aka teaser), associated with it.
						If you want one, please edit the article and add a descritpion under the SEO tab.
					</p>
				{/if}
				
				{a href="`$blog->get('rewriteurl')`"}
					Posted in {$blog->get('title')}
				{/a}
			</div>
		</div>
	{/foreach}
</div>

<!-- This is just the waypoint trigger to know when to load the next set of results! -->
<div id="bottomofthelisting"></div>

{script library="jquery.waypoints"}{/script}
{script location="foot" src="assets/js/blog-waypoint-scroll.js"}{/script}