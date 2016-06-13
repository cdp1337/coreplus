{$listings->render('head')}
{foreach $listings as $l}
	<tr>
		<td>{date $l.datetime format='SDT'}</td>
		<td>
			{a href="/useractivity/details?filter[user_id]=`$l.user_id`" style="text-decoration:none;"}
				{$l->getDisplayName()}
			{/a}
			<br/>

			{geoiplookup $l.ip_addr}
			{a href="/useractivity/details?filter[ip_addr]=`$l.ip_addr`" style="text-decoration:none;"}
				{$l.ip_addr}
			{/a}
			<br/>
			
			{$l.useragent|user_agent}
		</td>
		<td>{$l.type}</td>
		<td>
			{if $l.status == 200}
				<i class="icon icon-ok" style="color:green;" title="Request OK"></i>
			{else}
				<i class="icon icon-remove" style="color:red;" title="{$l.status}"></i>
			{/if}
			{$l.request}
			
			<br/>
			{if $l.referrer}
				<span class="referrer-info">Referrer: {$l.referrer}</span>
			{/if}
		</td>
		<td>{$l.referrer}</td>
		<td>
			{a href="/useractivity/details?filter[session_id]=`$l.session_id`" style="text-decoration:none;"}
				{$l.session_id}
			{/a}
		</td>
		<td>{$l.ip_addr}</td>
		<td>{$l.useragent}</td>
		<td>
			{if $l.xhprof_run &&$l.xhprof_source}
				<a
					href="{$smarty.const.SERVERNAME}/xhprof/index.php?run={$l.xhprof_run}&source={$l.xhprof_source}"
					target="_blank"
					title="View XHprof Profiler Report"><i class="icon icon-view"></i></a>
			{/if}
			{$l->getTimeFormatted()}
		</td>
		<td>{$l.db_reads}</td>
		<td>{$l.db_writes}</td>
		<td>&nbsp;</td>
	</tr>
{/foreach}
{$listings->render('foot')}