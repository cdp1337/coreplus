{script library="fancy_ip"}{/script}

{$filters->render()}

{$filters->pagination()}

<table class="listing">
	<tr>
		<th>Request Time</th>
		<th>User/IP/Sess</th>
		<th><abbr title="Rendering Time">Time</abbr></th>
		<th>Type</th>
		<th>Request</th>
		<th>Referrer</th>
	</tr>
	{foreach $listings as $l}
		<tr>
			<td>{date date="`$l.datetime`" format="M j H:i:s"}</td>
			<td>
				{a href="/useractivity/details?filter[user_id]=`$l.user_id`" style="text-decoration:none;"}
					{$l->getDisplayName()}
				{/a}
				<br/>
				<span class="ip">
					{a href="/useractivity/details?filter[ip_addr]=`$l.ip_addr`" style="text-decoration:none;"}
						{$l.ip_addr}
					{/a}
				</span>
				<br/>

				{a href="/useractivity/details?filter[session_id]=`$l.session_id`" style="text-decoration:none;"}
					{$l.session_id}
				{/a}
			</td>

			<td>

				{if $l.xhprof_run &&$l.xhprof_source}
					<a
						href="{$smarty.const.SERVERNAME}/xhprof/index.php?run={$l.xhprof_run}&source={$l.xhprof_source}"
						target="_blank"
						title="View XHprof Profiler Report"><i class="icon-view"></i></a>
				{/if}
				{$l->getTimeFormatted()}

			</td>

			<td>{$l.type}</td>

			<td>
				{if $l.status == 200}
					<i class="icon-ok" style="color:green;" title="Request OK"></i>
				{else}
					<i class="icon-remove" style="color:red;" title="{$l.status}"></i>
				{/if}
				{$l.request}
			</td>
			<td>{$l.referrer}</td>
		</tr>
	{/foreach}
</table>

{$filters->pagination()}