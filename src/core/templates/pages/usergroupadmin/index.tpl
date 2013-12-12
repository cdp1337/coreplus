{script library="jquery"}{/script}

<table class="listing">
	<tr>
		<th>Name</th>
		{if $display_global}
			<th>Scope</th>
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
				<td>
					{if $group.site == '-1'}
						Global
					{else}
						Local
					{/if}
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
								<i class="icon-edit"></i>
								<span>Edit</span>
							{/a}
						</li>
						<li class="delete">
							{a href="/usergroupadmin/delete/`$group.id`" confirm="Delete `$group.name|escape`?"}
								<i class="icon-remove"></i>
								<span>Delete</span>
							{/a}
						</li>
					</ul>
				{/if}
			</td>
		</tr>
	{/foreach}
</table>
