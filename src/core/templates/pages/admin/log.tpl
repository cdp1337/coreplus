{script library="Core.AjaxLinks"}{/script}

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
		<td class="systemlog-entry-code">
			{if $entry.type == "info"}
				<!-- No icon needed -->
			{elseif $entry.type == "error"}
				<i class="icon icon-exclamation" title="Error Entry"></i>
			{elseif $entry.type == "security"}
				<i class="icon icon-exclamation-triangle" title="Security Entry"></i>
			{else}
				[ {$entry.type} ]
			{/if}
			{$entry.code}
		</td>
		<td>{date $entry.datetime format='SDT'}</td>
		<td>
			{$entry.useragent|user_agent}<br/>
			{geoiplookup $entry.ip_addr}<br/>
			{$entry.ip_addr}
		</td>
		<td>{$entry.message|truncate:100}</td>
		<td>
			{user $entry.user_id}
		</td>
		<td>
			{if $entry.affected_user_id}
				{user $entry.affected_user_id}
			{else}
				N/A
			{/if}
		</td>
		<td>
			<ul class="controls" data-proxy-force="1">
				<li>
					{a href="/admin/log/details/`$entry.id`" title="View Details" class="ajax-link"}
						<i class="icon icon-view"></i>
						<span>View Details</span>
					{/a}
				</li>
				<li>
					{a href="/security/blacklistip/add?ip_addr=`$entry.ip_addr`/32"}
						<i class="icon icon-thumbs-down"></i>
						<span>Ban IP</span>
					{/a}
				</li>
				<li>
					{a href="/useractivity/details?filter[ip_addr]=`$entry.ip_addr`" title="Track User Activity"}
						<i class="icon icon-list-alt"></i>
						<span>View Activity by IP</span>
					{/a}
				</li>
				{if $entry.user_id}
					<li>
						{a href="/useractivity/details?filter[user_id]=`$entry.user_id`" title="Track User Activity"}
							<i class="icon icon-list-alt"></i>
							<span>View Activity by User</span>
						{/a}
					</li>	
				{/if}
			</ul>
		</td>
	</tr>
{/foreach}
{$listings->render('foot')}