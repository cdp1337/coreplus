<p>Base Directory: {$directory}</p>

{if sizeof($keys)}
	<table class="listing">
		<tr>
			<th>Key</th>
			<th>Date Created</th>
			<th>Names</th>
		</tr>
		{foreach from=$keys item=k}
			<tr>
				<td>{$k.key}</td>
				<td>{$k.date}</td>
				<td>
					{foreach from=$k.names item='e'}
						{$e|escape}<br/>
					{/foreach}
				</td>
			</tr>
		{/foreach}
	</table>
{else}

{/if}