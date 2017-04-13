{*#META
page-edit-meta-regroup: metas[author] Basic
page-edit-meta-regroup: metas[image] Basic
page-edit-meta-regroup: metas[keywords] Basic
page-edit-meta-regroup: metas[description] Basic
#*}

{css src="assets/css/blog.css"}{/css}

<div itemscope itemtype="http://schema.org/BlogPosting" class="blog-article" xmlns="http://www.w3.org/1999/html">

	{insertable name="page_h1" assign="page_h1" title="Page Heading" type="text" description="The page H1 tag."}
		{if $page_h1}<h1>{$page_h1}</h1>{/if}
	{/insertable}

	<div class="blog-article-posting-stats">
		{if $page->getAuthor()}
			{if Core::IsComponentAvailable('user-social')}
				{widget baseurl="/userprofile/badge" user=$page->getAuthor() title="Posted By" orientation="right"}
			{elseif Core::IsComponentAvailable('team')}
				{widget baseurl="/teambadge/execute" user=$page->getAuthor() title="Posted By" orientation="right"}
			{/if}
		{/if}

		{if $page->isPublished()}
			<meta itemprop="dateCreated" content="{date format='c' date="`$page.published`"}"/>
			<div class="blog-article-date">Posted {date date="`$page.published`"}</div>
		{else}
			<div class="blog-article-date">Not Published</div>
		{/if}
	</div>
	
	{insertable name="video" assign="video" title="Posting Video" accept="video/*" type="file" description="The showcase video for this post."}{/insertable}
	
	{link $video assign="videourl"}
	
	<video src="{$videourl}" controls>
		<a href="{$videourl}">Download</a>
	</video>

	<div class="blog-article-body" itemprop="articleBody">
		{insertable name="body" title="Body Content" type="wysiwyg"}{/insertable}
	</div>

	{widget baseurl="/tags/display" page=$page title="Tags:" }

</div>

{widgetarea name="After Blog Content"}