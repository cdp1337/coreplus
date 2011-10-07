<table>
	<tr>
		<td>Site</td>
		<td>Enabled</td>
		<td>&nbsp;</td>
	</tr>
	{foreach from=$sites item=site}
		<tr>
			<td>{$site.url}</td>
			<td>
				{if $site.enabled}Yes{/if}
				{if !$site.enabled}No{/if}
			</td>
			<td>
				{a href="Updater/Sites/Edit/`$site.id`"}edit{/a}
			</td>
		</tr>
	{/foreach}
</table>