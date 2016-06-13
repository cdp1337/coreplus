{script library="jquery"}{/script}
{script library="Core.AjaxLinks"}{/script}
{script library="fancy_ip"}{/script}

{$listings->render('head')}

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
				{date date="`$l.expires`" format="FD"}
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
						<i class="icon icon-edit"></i>
						<span>Edit</span>
					{/a}
				</li>
				<li>
					{a href="/security/blacklistip/delete/`$l.id`" confirm="Are you sure you want to unban this IP?"}
						<i class="icon icon-thumbs-up"></i>
						<span>Un-Ban</span>
					{/a}
				</li>
			</ul>
		</td>
	</tr>
{/foreach}
{$listings->render('foot')}