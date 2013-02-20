{**
 * Admin dashboard for cron executions
 *
 * @var $crons array Array of CronModels of the different types of crons.
 *}

{css src="assets/css/cron.css"}{/css}

<table class="widget-listing">
	<tr>
		<th>Cron</th>
		<th>Date Started</th>
		<th>Duration (ms)</th>
		<th>Status</th>
		<th width="50">&nbsp;</th>
	</tr>
	{foreach $crons as $cron}
		<tr>
			<td>{$cron.cron}</td>
			<td>{date date="`$cron.created`"}</td>
			<td>{$cron.duration|round:2}</td>
			<td><span class="cron-status-{$cron.status}">{$cron.status}</span></td>
			<td>
				<ul class="controls">
					<li>
						{a href="/cron/view/`$cron.id`" title="View Details" class="ajax-link"}
							<i class="icon-view"></i>
							<span>View Details</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>
	{/foreach}
	<tr>
		<td colspan="5" align="center">
			{a href="/cron/admin" class="button"}<i class="icon-view"></i> View All Crons{/a}
		</td>
	</tr>
</table>
