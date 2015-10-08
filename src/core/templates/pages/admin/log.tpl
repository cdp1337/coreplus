{script library="Core.AjaxLinks"}{/script}
{script library="fancy_ip"}{/script}

{css}<style>
	.systemlog-entry-type-security {
		font-weight: bold;
	}
	.systemlog-entry-type-security .systemlog-entry-code {
		color: red;
	}
</style>{/css}

{$listings->render('head')}
{foreach $listings as $entry}
	<tr class="systemlog-entry-type-{$entry.type}">
		<td colspan="8">
				<span class="systemlog-entry-code">
					{if $entry.type == "info"}
						<!-- No icon needed -->
					{elseif $entry.type == "error"}
						<i class="icon-exclamation" title="Error Entry"></i>
					{elseif $entry.type == "security"}
						<i class="icon-exclamation-triangle" title="Security Entry"></i>
					{else}
						[ {$entry.type} ]
					{/if}
					{$entry.code}
				</span>

			{$entry.message}
		</td>
	</tr>

	<tr class="systemlog-entry-type-{$entry.type} systemlog-metarow">
		<td>{date date="`$entry.datetime`"}</td>
		<td>
			{a href="/useractivity/details?filter[session_id]=`$entry.session_id`" title="Track User Activity" style="text-decoration:none;"}<i class="icon-list-alt"></i>{/a}
			{$entry.session_id|truncate:10}
		</td>
		<td>{user $entry.user_id}</td>
		<td>
			{a href="/useractivity/details?filter[ip_addr]=`$entry.ip_addr`" title="Track User Activity" style="text-decoration:none;"}<i class="icon-list-alt"></i>{/a}
			<span class="ip">
					{$entry.ip_addr}
				</span>
		</td>
		<td title="{$entry.useragent|escape}">{$entry.useragent|truncate:50}</td>
		<td>
			{if $entry.affected_user_id}
				{a href="/useractivity/details?filter[user_id]=`$entry.affected_user_id`" title="Track User Activity" style="text-decoration:none;"}<i class="icon-list-alt"></i>{/a}
				{user $entry.affected_user_id}
			{else}
				N/A
			{/if}
		</td>
		<td>
			<ul class="controls">
				<!--<li>
						{a href="/security/view/`$entry.id`" title="View Details" class="ajax-link"}
							<i class="icon-view"></i>
							<span>View Details</span>
						{/a}
					</li>-->
				<li>
					{a href="/security/blacklistip/add?ip_addr=`$entry.ip_addr`/32"}
						<i class="icon-thumbs-down"></i>
						<span>Ban IP</span>
					{/a}
				</li>
			</ul>
		</td>
	</tr>
{/foreach}
{$listings->render('foot')}