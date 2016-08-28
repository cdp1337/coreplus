{$form->render()}

<p class="message-tutorial">
	Below are the saved log files on the filesystem for this site.
</p>

<table class="listing">
	<tr>
		<th>Log Type</th>
		<th>Path</th>
		<th>Filesize</th>
		<th>&nbsp;</th>
	</tr>
	{foreach $logs as $row}
		<tr>
			<td>
				{$row.type}
			</td>
			<td>
				{$row.file->getFilename()}
			</td>
			<td>
				{$row.file->getFilesize(true)}
			</td>
			<td>
				{a href="/admin/log/config?download=`$row.file->getBasename()`"}
					<i class="icon icon-download"></i>
					<span>Download Raw Log</span>
				{/a}
			</td>
		</tr>
	{/foreach}
	{foreach $archived as $row}
		<tr>
			<td>
				{$row.type}
			</td>
			<td>
				{$row.file->getFilename()}
			</td>
			<td>
				{$row.file->getFilesize(true)}
			</td>
			<td>
				{a href="/admin/log/config?download=`$row.file->getBasename()`"}
					<i class="icon icon-download"></i>
					<span>Download Raw Log</span>
				{/a}
			</td>
		</tr>
	{/foreach}
</table>