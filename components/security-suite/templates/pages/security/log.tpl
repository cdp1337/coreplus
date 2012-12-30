{script library="Core.AjaxLinks"}{/script}

{$filters->render()}


{$filters->pagination()}
<table class="listing column-sortable">
	<tr>
		<th sortkey="datetime">Date Time</th>
		<th sortkey="session_id">Session</th>
		<th sortkey="user_id">User</th>
		<th sortkey="ip_addr">IP Address</th>
		<th sortkey="useragent">User Agent</th>
		<th sortkey="action">Action</th>
		<th sortkey="affected_user_id">Affected User</th>
		<th sortkey="status">Status</th>
		<th width="50">&nbsp;</th>
	</tr>
	{foreach $listings as $entry}
		<tr>
			<td>{date date="`$entry.datetime`"}</td>
			<td>
				<a href="?filter[session_id]={$entry.session_id}" title="{$entry.session_id}">{$entry.session_id|truncate:10}</a>

			</td>
			<td>{$entry.user}</td>
			<td>{$entry.ip_addr}</td>
			<td>{$entry.useragent|truncate:50}</td>
			<td>{$entry.action}</td>
			<td>
				<a href="?filter[affected_user_id]={$entry.affected_user_id}">{$entry.affected_user}</a>
			</td>
			<td><span class="cron-status-{$entry.status}">{$entry.status}</span></td>
			<td>
				<ul class="controls">
					<li>
						{a href="/security/view/`$entry.datetime`-`$entry.session_id`" title="View Details" class="ajax-link"}
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