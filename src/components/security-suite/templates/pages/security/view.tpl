<div style="float:left; width:48%;">
	<table>

		<tr>
			<th>Action</th>
			<td>{$entry.action}</td>
		</tr>
		<tr>
			<th>Status</th>
			<td>
				<span class="cron-status-{$entry.status}">{$entry.status}</span>
			</td>
		</tr>
		<tr>
			<th>Date</th>
			<td>{date date="`$entry.datetime`"}</td>
		</tr>
		<tr>
			<th>Affected User</th>
			<td>{$affected_user}</td>
		</tr>
		<tr>
			<th>User</th>
			<td>{$user}</td>
		</tr>
		<tr>
			<th>Session</th>
			<td>{$entry.session_id}</td>
		</tr>
		<tr>
			<th>IP Address</th>
			<td>{$entry.ip_addr}</td>
		</tr>
	</table>
</div>

<div style="float:right; width:48%;">
	<h3>User Agent</h3>
	{$entry.useragent}<br/><br/>
	<h3>Details</h3>
	{$entry.details|nl2br}
</div>

<div class="clear"></div>