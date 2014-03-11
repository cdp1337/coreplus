<div itemscope itemtype="http://schema.org/BlogPosting" class="blog-article" xmlns="http://www.w3.org/1999/html">

	<div class="blog-article-posting-stats">
		{if $page->getAuthor() && Core::IsComponentAvailable('user-social')}
			{widget baseurl="/userprofile/badge" user=$page->getAuthor() title="Posted By" orientation="right"}
		{/if}

		{if $page->isPublished()}
			<meta itemprop="dateCreated" content="{date format='c' date="`$page.published`"}"/>
			<div class="blog-article-date">Posted {date date="`$page.published`"}</div>
		{else}
			<div class="blog-article-date">Not Published</div>
		{/if}
	</div>


	{if $page->getImage()}
		{img class="blog-article-img" file=$page->getImage() width='620' height='400' itemprop="thumbnailUrl" alt="`$page.title|escape`" includemeta=1}
	{/if}

	<div class="blog-article-body" itemprop="articleBody">
		{insertable name="body" title="Body Content" type="wysiwyg"}{/insertable}
	</div>

	{widget baseurl="/tags/display" page=$page title="Tags:" }

</div>

{widgetarea name="After Blog Content"}