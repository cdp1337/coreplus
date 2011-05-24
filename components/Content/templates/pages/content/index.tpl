<table class="listing">
	<tr>
		<th>Title</th>
		<th>Link</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$pages item=page}
		<tr>
			<td>{$page->get('title')}</td>
			<td>
				{a href=$page->getLink('Page')->get('rewriteurl')}
					{$page->getLink('Page')->get('rewriteurl')}
				{/a}
			</td>
			<td>
				<ul class="controls">
					<li class="view">{a href=$page->getLink('Page')->get('rewriteurl')}View{/a}</li>
					<li class="edit">{a href="/Content/Edit/`$page->get('id')`"}Edit{/a}</li>
					<li class="delete">{a href="/Content/Delete/`$page->get('id')`" confirm="Are you sure you want to delete `$page->get('title')`?"}Delete{/a}</li>
				</ul>
			</td>
		</tr>
	{/foreach}
</table>