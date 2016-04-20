<div id="user-edit-listing">
	{img src="`$user->get('avatar')`" placeholder="person" width="150" height="300"}


	<ul>
		<li>User Name: {$user->getDisplayName()}</li>
		<li>Email: {$user.email}</li>
		<li>Member Since: {date format="FD" $user.created}</li>
		{if $user->get('gpgauth_pubkey')}
			<li>
				GPG Public Key: {$user->get('gpgauth_pubkey')|gpg_fingerprint}
			</li>
		{/if}
		{foreach $user->getConfigObjects() as $c}
			{if $c.value && !$c.UserConfig.hidden}
				<li>{$c.UserConfig.name}: {$c.value}</li>
			{/if}
		{/foreach}

		{if $profiles}
			{foreach $profiles as $profile}
				<li>
					<i class="icon-{$profile.type}"></i>
					<a href="{$profile.url}" rel="me" title="{($profile.title) ? $profile.title : $profile.type}" target="_blank">
						{if $profile.title}{$profile.title}{else}{$profile.url}{/if}
					</a>
				</li>
			{/foreach}
		{/if}
	</ul>

	<br/>

	{if sizeof($logins) > 1}
		Latest Security Logs<br/>
		{a href="/admin/log?filter[affected_user_id]=`$user.id`" class="button"}
			<span>View All Logs</span>
		{/a}
		<table class="listing">
			<tr>
				<th>Date</th>
				<th>IP</th>
				<th>Location</th>
				<th>User Agent</th>
				<th>Notes</th>
			</tr>
			{foreach $logins as $login}
				<tr>
					<td>{date format="SDT" $login.datetime}</td>
					<td>{$login.ip_addr}</td>
					<td>{geoiplookup $login.ip_addr}</td>
					<td>{$login.useragent}</td>
					<td>{($login.message) ? $login.message : $login.code}</td>
				</tr>
			{/foreach}
		</table>
	{/if}
</div>
