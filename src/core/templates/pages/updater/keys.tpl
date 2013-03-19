<p>Base Directory: {$directory}</p>

{if sizeof($keys)}
	<table class="listing">
		<tr>
			<th>Key</th>
			<th>Date Created</th>
			<th>Names</th>
			<th>&nbsp;</th>
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
				<td>
					{a href="/updater/keys/delete/`$k.key`" confirm="Really delete key `$k.key`?" class="control-delete"}Delete{/a}
				</td>
			</tr>
		{/foreach}
	</table>
{else}
	<p class="message-info">No keys are installed on the system, go {a href="updater/keys/import"}add one{/a}</p>
{/if}