<div itemscope itemtype="http://schema.org/Review" class="blog-article" xmlns="http://www.w3.org/1999/html">

	<h1>{$article.title}</h1>

	<div class="blog-article-posting-stats">
		{if $author && Core::IsComponentAvailable('user-social')}
			{widget baseurl="/userprofile/badge" user="$author" title="Posted By" orientation="right"}
		{/if}
		<meta itemprop="datePublished" content="{date format='c' date="`$article.published`"}"/>
		<div class="blog-article-date">Posted {date date="`$article.published`"}</div>
	</div>


	{if $article.image}
		{img class="blog-article-img" src="public/blog/`$article.image`" width='620' height='400' itemprop="thumbnailUrl" alt="`$article.title|escape`"}
	{/if}

	<div class="blog-article-body" itemprop="reviewBody">
		{$article.body}
	</div>


	{insertable type="text" name="product" title="Product" description="The name of the product this review is about" assign="product" default=""}
		<span itemprop="itemReviewed">{$product}</span>
	{/insertable}

	{insertable
		type="select"
		name="rating"
		title="Review Rating"
		options="0.0|0.5|1.0|1.5|2.0|2.5|3.0|3.5|4.0|4.5|5.0"
		default="2.5"
		assign="rating"
		description="The rating of this product, 0 being worst, 5 being best."
	}
		<div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
			<meta itemprop="worstRating" content="0" />
			<meta itemprop="ratingValue" content="{$rating}" />
			<meta itemprop="bestRating" content="5" />
			<!-- Display the stars! -->
			Rating:
			<span class="rating-stars" title="{$rating} out of 5 stars">
				{for $x=1; $x<=$rating; $x++}
					<i class="icon-star"></i>
				{/for}
				{if $x - $rating == 0.5}
					<i class="icon-star-half"></i>
					{* A half a star won't trigger a full step, so this logic tries to add an extra star :/ *}
					{for $x=$rating; $x<4; $x++}
						<i class="icon-star-empty"></i>
					{/for}
				{else}
					{* Full star stepping plays nicely. *}
					{for $x=$rating; $x<5; $x++}
						<i class="icon-star-empty"></i>
					{/for}
				{/if}
			</span>
		</div>
	{/insertable}

	{widget baseurl="/tags/display" page=$article->getLink('Page') title="Tags:" }
</div>
