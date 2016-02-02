<p>
	{if $entry.type == "info"}
		<i class="icon-info-circle" title="Informative Entry"></i>
	{elseif $entry.type == "error"}
		<i class="icon-exclamation" title="Error Entry"></i>
	{elseif $entry.type == "security"}
		<i class="icon-exclamation-triangle" title="Security Entry"></i>
	{else}
		[ {$entry.type} ]
	{/if}
	{$entry.code}<br/>
	
	Date: {date format='FDT' $entry.datetime}<br/>
	Source IP: {$entry.ip_addr}<br/>
	User Agent: {$entry.useragent}<br/>
	{if $entry.affected_user_id}
		Affected User: 
		{a href="/user/view/`$entry.affected_user_id`"}	
			{user $entry.affected_user_id}
		{/a}
		<br/>
	{/if}
	
	{$entry.message}
</p>

<pre>
{$entry.details}
</pre>
