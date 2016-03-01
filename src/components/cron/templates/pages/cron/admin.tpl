{script library="Core.AjaxLinks"}{/script}
{css src="assets/css/cron.css"}{/css}

{$listings->render('head')}

{foreach $listings as $entry}
	<tr>
		<td class="cron-status-{$entry.status}">
			{if $entry.status == 'pass'}
				<i class="icon-ok"></i>
			{else}
				<i class="icon-remove"></i>
			{/if}
			{$entry.cron}
		</td>
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
{$listings->render('foot')}