{css src="assets/css/blog.css"}{/css}

<section class="blog-article-widget blog-article-widget-{$sort} blog-article-widget-everything-small">
	{if $title}
		<h3>{$title}</h3>
	{/if}

	{foreach $links as $l}
		<article class="blog-article-widget-item" itemscope="itemscope" itemtype="http://schema.org/BlogPosting">
			<link itemprop="url" href="{link $l.baseurl}"/>

			{if $l->getImage()}
				{a href="`$l.baseurl`"}
					{img class="blog-article-widget-img" placeholder="blog" file=$l->getImage() width='120' height='120' itemprop="thumbnailUrl" alt="`$l.title|escape`" includemeta=0}
				{/a}
			{/if}

			{a href="`$l.baseurl`" class="blog-article-widget-link"}
				<span class="blog-article-widget-title" itemprop="name">{$l.title}</span>
			{/a}

			{if $l->isPublished()}
				<meta itemprop="dateCreated" content="{date format='c' date="`$l.published`"}"/>
				<div class="blog-article-date">Posted {date format="SD" $l.published}</div>
			{else}
				<div class="blog-article-date">Not Published</div>
			{/if}

			{if $l->getTeaser()}
				<p class="blog-article-excerpt" itemprop="articleBody">
					{$l->getTeaser()}
					... {a class="blog-article-read-more" href="`$l.baseurl`"}Read More{/a}
				</p>
			{/if}
		</article>
	{/foreach}
</section>