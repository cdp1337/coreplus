<div style="float:left; width:48%;">
	<table>
		<tr>
			<th>Cron</th>
			<td>{$entry.cron}</td>
		</tr>
		<tr>
			<th>Date Started</th>
			<td>{date date="`$entry.created`"}</td>
		</tr>
		<tr>
			<th>Duration</th>
			<td>
				{if $entry.duration > 1000}
					{($entry.duration/1000)|round:2} seconds
				{else}
					{$entry.duration|round:2} ms
				{/if}
			</td>
		</tr>
		<tr>
			<th>Status</th>
			<td>
				<span class="cron-status-{$entry.status}">{$entry.status}</span>
			</td>
		</tr>
		<tr>
			<th>IP Address</th>
			<td>{$entry.ip}</td>
		</tr>
	</table>
</div>

<div style="float:right; width:48%;">
	<h3>Execution Log</h3>
	{$entry.log|nl2br}
</div>

<div class="clear"></div>