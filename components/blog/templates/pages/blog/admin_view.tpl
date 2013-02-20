{**
 * Variables available here are:
 *
 * @var $form Form The form
 *}

{css src="assets/css/blog.css"}{/css}


{$filters->render()}

{if !sizeof($articles)}
	<p class="message-info">
		There were no articles found!
		{if $blogid}
			{a href="/blog/article/create/`$blogid`"}Create One?{/a}
		{/if}
	</p>
{else}
	{$filters->pagination()}
	<table class="listing column-sortable">
		<tr>
			<th>Image</th>
			<th sortkey="title">Title</th>
			<th sortkey="status">Status</th>
			<th>Views</th>
			<th sortkey="created">Created</th>
			<th sortkey="published">Published</th>
			<th sortkey="updated">Updated</th>
			<th width="80">&nbsp;</th>
		</tr>
		{foreach $articles as $article}
			<tr class="blog-article-status-{$article.status}">
				<td>
					{img src="public/blog/`$article.image`" placeholder="blog" dimensions="50x50"}
				</td>
				<td>
					{$article.title}
				</td>
				<td>
					{$article.status}
				</td>
				<td>
					{$article->getLink('Page')->get('pageviews')}
				</td>
				<td>
					{date date="`$article.created`"}
				</td>
				<td>
					{if $article.published}
						{date date="`$article.published`"}
					{else}
						Not Published
					{/if}

				</td>
				<td>
					{date date="`$article.updated`"}
				</td>
				<td>
					<ul class="controls controls-hover">
						<li class="view">
							{a href="/blog/article/view/`$article.id`" title="View"}
								<i class="icon-eye-open"></i><span>View</span>
							{/a}
						</li>
						<li class="edit">
							{a href="/blog/article/update/`$article.blogid`/`$article.id`" title="Edit"}
								<i class="icon-edit"></i><span>Edit</span>
							{/a}
						</li>
						<li class="delete">
							{a href="/blog/article/delete/`$article.blogid`/`$article.id`" title="Delete" confirm="Are you sure you want to delete `$article.title`?"}
								<i class="icon-remove"></i><span>Delete</span>
							{/a}
						</li>
					</ul>
				</td>
			</tr>
		{/foreach}
	</table>
	{$filters->pagination()}
{/if}