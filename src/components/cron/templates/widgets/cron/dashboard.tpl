{**
 * Admin dashboard for cron executions
 *
 * @var $crons array Array of CronModels of the different types of crons.
 *}

{css src="assets/css/cron.css"}{/css}

<h3>Recent Cron Events</h3>

{if sizeof($crons)}
	<table class="listing">
		<tr>
			<th>&nbsp;</th>
			<th>Cron</th>
			<th>Date Started</th>
			<th>Duration</th>
			<th width="50">&nbsp;</th>
		</tr>
		{foreach $crons as $cron}
			<tr>
				<td class="cron-status-{$cron.status}">
					{if $cron.status == 'pass'}
						<i class="icon-ok" title="Cron Succeeded"></i>
					{else}
						<i class="icon-remove" title="Cron Failed"></i>
					{/if}
				</td>
				<td>{$cron.cron}</td>
				<td>{date date="`$cron.created`"}</td>
				<td>
					{if $cron.duration > 120000}
						{($cron.duration/1000/60)|round:0} minutes
					{elseif $cron.duration > 1500}
						{($cron.duration/1000)|round:0} seconds
					{else}
						{$cron.duration|round:2} ms
					{/if}
				</td>
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
	</table>
<br/>
	{a href="/cron/admin" class="button"}<i class="icon-view"></i> View All Crons{/a}
{else}
	<p class="message-info">No cron information is available!</p>
{/if}