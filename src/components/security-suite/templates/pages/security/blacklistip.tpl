{script library="jquery"}{/script}
{script library="Core.AjaxLinks"}{/script}
{script library="fancy_ip"}{/script}

<table class="listing">
	<tr>
		<th>IP or Network</th>
		<th>Comment</th>
		<th>Expires</th>
		<th>Created</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach $listings as $l}
		<tr>
			<td>
				<span class="ip">{$l.ip_addr}</span>
			</td>
			<td>
				{$l.comment}
			</td>

			<td>
				{if $l.expires}
					{date date="`$l.expires`"}
				{else}
					Never!
				{/if}
			</td>

			<td>
				{date date="`$l.created`"}
			</td>

			<td>
				<ul class="controls">
					<li>
						{a href="/security/blacklistip/edit/`$l.id`"}
							<i class="icon-edit"></i>
							<span>Edit</span>
						{/a}
					</li>
					<li>
						{a href="/security/blacklistip/delete/`$l.id`" confirm="Are you sure you want to unban this IP?"}
							<i class="icon-thumbs-up"></i>
							<span>Un-Ban</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>
	{/foreach}
</table>