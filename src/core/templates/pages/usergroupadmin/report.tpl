{foreach $data as $d}
	<div class="group">
		<h4>{$d.group.name}</h4>
		<p>
			{if $d.group.default}
				Is Default Group<br/>
			{else}
				Not Default Group<br/>
			{/if}

			{$d.group.description}

			{foreach $d.group.permissions as $p}
				{if $p}
					Has Permission: {$p}<br/>
				{/if}
			{/foreach}
		</p>
		<ul>
			{foreach $d.users as $u}
				<li>{user $u link=1}</li>
			{foreachelse}
				<li>No Current Members</li>
			{/foreach}
		</ul>
		<hr/>
	</div>
{/foreach}
