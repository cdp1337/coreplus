{if !count($navs)}
	<p class="message-info">There are no navigation menus, {a href="/Navigation/Create"}Create One{/a}?</p>
{/if}

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

{if count($navs)}
	<p>
		To install a navigation menu into the theme, use the {a href="/Theme/Widgets/`$current_theme`"}Theme Manager{/a}.
	</p>
{/if}