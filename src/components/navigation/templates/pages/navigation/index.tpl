{if !count($navs)}
<p class="message-info">There are no navigation menus, {a href="/Navigation/Create"}Create One{/a}?</p>
{/if}

<table class="listing">
	<tr>
		<th>Navigation Menu</th>
		<th width="60">&nbsp;</th>
	</tr>
{foreach from=$navs item=nav}
	<tr>
		<td>{$nav->get('name')}</td>

		<td>
			<ul class="controls">
				<li class="edit">
					{a href="/Navigation/Edit/`$nav->get('id')`"}
						<i class="icon icon-edit"></i>
						<span>Edit</span>
					{/a}
				</li>
				<li class="delete">
					{a confirm="Are you sure you want to delete {$nav.name}?" href="/Navigation/Delete/`$nav->get('id')`"}
						<i class="icon icon-remove"></i>
						<span>Delete</span>
					{/a}
				</li>
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