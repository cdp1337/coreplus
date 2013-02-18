<table class="listing">
	<tr>
		<td>Site</td>
		<td>Description</td>
		<td>&nbsp;</td>
	</tr>
	{foreach from=$sites item=site}
		<tr>
			<td>
				{$site.url}
			</td>
			<td>
				{$site.description|truncate:200}
			</td>
			<td>
				<ul class="controls">
					<li>
						{a href="/updater/repos/delete/`$site.id`" confirm="Really remove repo?"}
							<i class="icon-remove"></i>
							<span>Remove Repository</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>
	{/foreach}
</table>