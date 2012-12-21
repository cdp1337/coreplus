{script library="Core.AjaxLinks"}{/script}

{$filters->render()}


{$filters->pagination()}
<table class="listing column-sortable">
	<tr>
		<th sortkey="cron">Cron</th>
		<th sortkey="created">Date Started</th>
		<th sortkey="duration">Duration (ms)</th>
		<th sortkey="status">Status</th>
		<th width="50">&nbsp;</th>
	</tr>
	{foreach $listings as $entry}
		<tr>
			<td>{$entry.cron}</td>
			<td>{date date="`$entry.created`"}</td>
			<td>{$entry.duration|round:2}</td>
			<td><span class="cron-status-{$entry.status}">{$entry.status}</span></td>
			<td>
				<ul class="controls">
					<li>
						{a href="/cron/view/`$entry.id`" title="View Details" class="ajax-link"}
							<i class="icon-view"></i>
							<span>View Details</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>
	{/foreach}
</table>
{$filters->pagination()}