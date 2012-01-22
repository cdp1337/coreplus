<table class="listing">
	<tr>
		<th>Navigation Menu</th>	
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$navs item=nav}
		<tr>
			<td>{$nav->get('name')}</td>
			
			<td>
				<ul class="controls">
					<li class="edit">{a href="/Navigation/Edit/`$nav->get('id')`"}Edit{/a}</li>
					<li class="delete">{a href="/Content/Delete/`$nav->get('id')`"}Delete{/a}</li>
				</ul>
			</td>
		</tr>
	{/foreach}
</table>
