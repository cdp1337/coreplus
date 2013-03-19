{css src="assets/css/cron.css"}{/css}

<div class="cron-detail-view">
	<div class="cron-detail-view-meta">
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
				<th>Memory Used</th>
				<td>
					{Core::FormatSize($entry.memory)}
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

	<div class="cron-detail-view-report">
		<h3>Execution Log</h3>
		<div class="cron-detail-view-report-contents">
			{$entry.log|nl2br}
		</div>
	</div>


	<div class="clear"></div>
</div>