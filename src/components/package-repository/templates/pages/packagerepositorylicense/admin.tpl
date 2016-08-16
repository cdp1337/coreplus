
{$listings->render('head')}
	{foreach $listings as $l}
		<tr>
			<td>
				{if $l.comment}
					{$l.comment}<br/>
					({$l->getLabel()})
				{else}
					{$l->getLabel()}
				{/if}
				<br/>
				{if $l.expires && $l.expires != '0000-00-00'}
					Expires {date format="SD" $l.expires}
				{else}
					EXPIRED/UNKNOWN
				{/if}
			</td>
			<td>
				{if $l.password}
					<a href="#" class="toggle-password" title="Click to toggle password">
						<span class="plain-text"><i class="icon-eye"></i></span>
						<span class="password" style="display:none;"><i class="icon-eye-slash"></i></span>
					</a>
					<span class="plain-text">**********</span>
					<span class="password" style="display:none;">{$l.password}</span>	
				{/if}
			</td>
			<td>{$l.ip_restriction}</td>
			<td>
				{foreach $l.features as $k => $v}
					{$k}: {$v}<br/>
				{/foreach}
			</td>
			<td>
				{$l.referrer_last_checkin}<br/>
				{date format="SDT" $l.datetime_last_checkin}<br/>
				{geoiplookup $l.ip_last_checkin} {$l.ip_last_checkin}
			</td>
			<td>
				{controls model=$l}
			</td>
		</tr>
	{/foreach}
{$listings->render('foot')}

{script location="foot"}<script>
	$('.toggle-password').click(function() {
		$(this).closest('td').find('.plain-text, .password').toggle();
		return false;
	});
</script>{/script}