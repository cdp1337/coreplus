{script library="jquery"}{/script}

<table class="listing">
	<tr>
		<th>Name</th>
		{if $display_global}
			<th>Scope</th>
		{elseif $multisite}
			<th>Site</th>
		{/if}
		{if $permissionmanager}
			<th>Context</th>
			<th>Permissions</th>
		{/if}
		<th width="80">&nbsp;</th>
	</tr>
	{foreach $groups as $group}
		<tr>
			<td>{$group.name}</td>
			{if $display_global}
				{*
				 * Global is a dumbed down view of multisite, basically if it's this site or global.
				 *
				 * This is displayed to administrators of the child site.
				 *}
				<td>
					{if $group.site == '-1'}
						Global
					{else}
						Local
					{/if}
				</td>
			{elseif $multisite}
				<td>
					{multisite $group.site}
				</td>
			{/if}
			{if $permissionmanager}
				<td>
					{$group.context}
				</td>
				<td>
					{$group->getPermissions()|implode:", "}
				</td>
			{/if}
			<td>
				{if !$display_global || ($display_global && ($group.site == $site || !$site))}
					<ul class="controls">
						<li class="edit">
							{a href="/usergroupadmin/update/`$group.id`"}
								<i class="icon icon-edit"></i>
								<span>Edit</span>
							{/a}
						</li>
						<li class="delete">
							{a href="/usergroupadmin/delete/`$group.id`" confirm="Delete `$group.name|escape`?"}
								<i class="icon icon-remove"></i>
								<span>Delete</span>
							{/a}
						</li>
					</ul>
				{/if}
			</td>
		</tr>
	{/foreach}
</table>
