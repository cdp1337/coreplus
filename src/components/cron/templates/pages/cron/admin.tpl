{script library="Core.AjaxLinks"}{/script}
{css src="assets/css/cron.css"}{/css}

{$filters->render()}


{$filters->pagination()}
<table class="listing column-sortable">
	<tr>
		<th sortkey="status"  width="25">&nbsp;</th>
		<th sortkey="cron">Cron</th>
		<th sortkey="created">Date Started</th>
		<th sortkey="duration">Duration</th>
		<th width="50">&nbsp;</th>
	</tr>
	{foreach $listings as $entry}
		<tr>
			<td class="cron-status-{$entry.status}">
				{if $entry.status == 'pass'}
					<i class="icon-ok"></i>
				{else}
					<i class="icon-remove"></i>
				{/if}
			</td>
			<td>{$entry.cron}</td>
			<td>{date date="`$entry.created`"}</td>
			<td>
				{if $entry.duration > 1500}
					{($entry.duration/1000)|round:0} seconds
				{else}
					{$entry.duration|round:2} ms
				{/if}
			</td>
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