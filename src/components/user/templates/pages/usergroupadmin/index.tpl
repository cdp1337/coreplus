{script library="jquery"}{/script}

<table class="listing">
	<tr>
		<th>Name</th>
		{if $permissionmanager}<th>Permissions</th>{/if}
		<th width="80">&nbsp;</th>
	</tr>
	{foreach $groups as $group}
		<tr>
			<td>{$group.name}</td>
			{if $permissionmanager}
				<td>
					{$group->getPermissions()|implode:", "}
				</td>
			{/if}
			<td>
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
			</td>
		</tr>
	{/foreach}
</table>
