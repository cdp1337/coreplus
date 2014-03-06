{if $manager}
	{foreach $links as $l}
		{a href="`$l.baseurl`" class="button" title="Create New `$l.title` Widget"}
			<i class="icon-add"></i>
			<span>{$l.title} Widget</span>
		{/a}
	{/foreach}
{/if}


{$filters->render()}


{$filters->pagination()}
<table class="listing column-sortable">
	<tr>
		<th sortkey="title">Title</th>
		<th sortkey="baseurl">Base URL</th>
		<th>Installable</th>
		<th sortkey="created">Created</th>
		<th width="50">&nbsp;</th>
	</tr>
	{foreach $listings as $entry}
		<tr>
			<td>{$entry.title}</td>
			<td>{$entry.baseurl}</td>
			<td>{$entry.installable}</td>
			<td>{date format="SD" $entry.created}</td>

			<td>
				{if $manager}
				<ul class="controls">
					{if $entry.editurl}
						<li>
							{a href="`$entry.editurl`"}
								<i class="icon-edit"></i>
								<span>Edit</span>
							{/a}
						</li>
					{/if}
					{if $entry.deleteurl}
						<li>
							{a href="`$entry.deleteurl`" confirm="Are you sure you want to completely delete this widget?"}
								<i class="icon-remove"></i>
								<span>Delete</span>
							{/a}
						</li>
					{/if}
				</ul>
				{/if}
			</td>
		</tr>

	{/foreach}
</table>
{$filters->pagination()}