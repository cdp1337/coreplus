{if !sizeof($blogs)}
	<p class="message-info">
		There are no blogs on the system.  {a href="/blog/create"}Create one?{/a}
	</p>
{else}
	<table class="listing">
		<tr>
			<th>Title</th>
			<th>Type</th>
			<th>Link</th>
			<th width="80">&nbsp;</th>
		</tr>
		{foreach $blogs as $blog}
			<tr>
				<td>{$blog->get('title')}</td>
				<td>{$blog.type}</td>
				<td>
					{a href="/blog/view/`$blog->get('id')`"}
						{link link="/blog/view/`$blog->get('id')`"}
					{/a}
				</td>
				<td>
					<ul class="controls controls-hover">
						<li>
							{a href="/blog/admin/view/`$blog.id`"}
								<i class="icon-tasks"></i>
								<span>Articles</span>
							{/a}
						</li>
						{if $blog.type == 'remote'}
							<li>
								{a href="/blog/import/`$blog.id`" title="Import Feed"}
									<i class="icon-exchange"></i>
									<span>Import Feed</span>
								{/a}
							</li>
						{else}
							<li>
								{a href="/blog/article/create/`$blog.id`" title="Add Article"}
									<i class="icon-add"></i>
									<span>Add Article</span>
								{/a}
							</li>
						{/if}


						<li class="view">
							{a href="/blog/view/`$blog.id`" title="View"}
								<i class="icon-eye-open"></i>
								<span>View</span>
							{/a}
						</li>
						<li class="edit">
							{a href="/blog/update/`$blog.id`" title="Edit"}
								<i class="icon-edit"></i>
								<span>Edit</span>
							{/a}
						</li>
						<li class="delete">
							{a href="/blog/delete/`$blog.id`" title="Delete" confirm="Are you sure you want to delete `$blog->get('title')`?"}
								<i class="icon-remove"></i>
								<span>Delete</span>
							{/a}
						</li>
					</ul>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}