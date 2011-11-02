<table class="listing">
	<tr>
		<th>Theme</th>
		<th colspan="2">Site Template</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$themes item=theme}
		<tr>
			<td rowspan="{sizeof($theme.templates) + 1}">{$theme.name}</td>
			<td colspan="3"></td>
			{*
			<td>
				{if $theme.default} Site Default{/if}
				{if !$theme.default} [set as default] {/if}
			</td>
			<td>
				<ul class="controls">
					<li>[widgets] </li>
				</ul>
			</td>
			*}
		</tr>
		{foreach from=$theme.templates item=template}
			<tr>
				<td>{$template.file}</td>
				<td>{$template.title}</td>
				<td>
					{if $template.default} Site Default{/if}
					{if !$template.default} [set as default] {/if}
					{a href="/Theme/Widgets/`$theme.name`?template=`$template.file`"}[widgets]{/a}
				</td>
			</tr>
		{/foreach}
	{/foreach}
</table>