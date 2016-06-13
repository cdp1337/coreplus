<h4>Recent Logins</h4>
{if sizeof($logins) > 1}
	<table class="listing">
		<tr>
			<th>Date &amp; Time</th>
			<th>Source</th>
			<th>Notes</th>
		</tr>
		{foreach $logins as $login}
			<tr>
				<td>{date format="SDT" $login.datetime}</td>
				<td>
					{$login.useragent|user_agent}<br/>
					{geoiplookup $login.ip_addr}<br/>
					{$login.ip_addr}
				</td>
				<td>{($login.message) ? $login.message : $login.code}</td>
			</tr>
		{/foreach}
	</table>
	
	{if $can_view_logs}
		{a href="/admin/log?filter[type]=security&filter[code]=%2Fuser%2Flogin&filter[affected_user_id]=`$user.id`"}
			View All Attempts
		{/a}
	{/if}
{else}
	<p class="message-info">There have been no logins for this user.</p>
{/if}