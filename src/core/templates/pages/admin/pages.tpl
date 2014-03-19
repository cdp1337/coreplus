
{foreach $links as $l}
	{a href="`$l.baseurl`" class="button" title="Create New `$l.title` Page"}
		<i class="icon-add"></i>
		<span>{$l.title} Page</span>
	{/a}
{/foreach}


{$filters->render()}


{$filters->pagination()}
<table class="listing column-sortable">
	<tr>
		<th sortkey="title">Title</th>
		<th sortkey="parenturl">Parent</th>
		<th sortkey="rewriteurl">URL</th>
		<th sortkey="pageviews">Views</th>
		<th sortkey="popularity">Score</th>
		<th sortkey="expires">Expires</th>
		<th sortkey="created">Created</th>
		<th sortkey="published">Published</th>
		<th sortkey="access">Access</th>
		<th width="75">&nbsp;</th>
	</tr>
	{foreach $listings as $entry}
		<tr>
			<td>{$entry.title}</td>
			<td>{$entry.parenturl}</td>
			<td>{$entry.rewriteurl}</td>
			<td>{$entry.pageviews}</td>
			<td>
				{if $entry.indexable}
					{$entry.popularity}
				{else}
					N/A
				{/if}
			</td>
			<td>
				{if $entry.expires == 0}
					Disabled
				{elseif $entry.expires < 60}
					{$entry.expires} seconds
				{elseif $entry.expires < 3600}
					{$entry.expires/60} min
				{else}
					{$entry.expires/3600} hr
				{/if}
			</td>
			<td>{date format="SD" $entry.created}</td>
			<td>
				{if $entry.published}
					{date format="SD" $entry.published}
				{else}
					Not Published
				{/if}
			</td>
			<td>
				{if $entry.access == 'g:admin'}
					Only Super Admins
				{elseif $entry.access == '*'}
					Anyone, (guests and users)
				{elseif $entry.access == 'g:authenticated'}
					Only Authenticated Users
				{elseif $entry.access == '!g:authenticated'}
					Only Anonymous Guests
				{else}
					{$entry.access}
				{/if}
			</td>

			<td>
				<ul class="controls">
					<li>
						{a href="`$entry.baseurl`"}
							<i class="icon-view"></i>
							<span>View</span>
						{/a}
					</li>
					{if $entry.editurl}
						<li>
							{a href="`$entry.editurl`"}
								<i class="icon-edit"></i>
								<span>Edit</span>
							{/a}
						</li>
					{/if}

					{if $entry.published_status == 'draft'}
						<li>
							{a href="/admin/page/publish?baseurl=`$entry.baseurl`" title="Publish Page" confirm="Publish Page?"}
								<i class="icon-thumbs-up"></i><span>Publish Page</span>
							{/a}
						</li>
					{else}
						<li>
							{a href="/admin/page/unpublish?baseurl=`$entry.baseurl`" title="Unpublish Page" confirm="Unpublish Page?"}
								<i class="icon-thumbs-down"></i><span>Unpublish Page</span>
							{/a}
						</li>
					{/if}

					{if $entry.deleteurl}
						<li>
							{a href="`$entry.deleteurl`" confirm="Are you sure you want to completely delete this page?"}
								<i class="icon-remove"></i>
								<span>Delete</span>
							{/a}
						</li>
					{/if}
				</ul>
			</td>
		</tr>

	{/foreach}
</table>
{$filters->pagination()}