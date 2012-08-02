<table class="listing">
	<tr>
		<th>Title</th>
		<th>Link</th>
		<th width="80">&nbsp;</th>
	</tr>
{foreach $blogs as $blog}
	<tr>
		<td>{$blog->get('title')}</td>
		<td>
			{a href="/blog/view/`$blog->get('id')`"}
					{link link="/blog/view/`$blog->get('id')`"}
				{/a}
		</td>
		<td>
			<ul class="controls">
				<li class="view">
					{a href="/blog/view/`$blog->get('id')`" title="View"}
						<i class="icon-eye-open"></i><span>View</span>
					{/a}
				</li>
				<li class="edit">
					{a href="/blog/update/`$blog->get('id')`" title="Edit"}
						<i class="icon-edit"></i><span>Edit</span>
					{/a}
				</li>
				<li class="delete">
					{a href="/blog/delete/`$blog->get('id')`" title="Delete" confirm="Are you sure you want to delete `$blog->get('title')`?"}
						<i class="icon-remove"></i><span>Delete</span>
					{/a}
				</li>
			</ul>
		</td>
	</tr>
{/foreach}
</table>