<div id="user-edit-listing">
	{img src="`$user->get('avatar')`" placeholder="person" width="150" height="300"}
	
	<ul>
		<li>User Name: {$user->getDisplayName()}</li>
		<li>Email: {$user.email}</li>
		<li>Member Since: {date format="FD" $user.created}</li>
		{if $user.gpgauth_pubkey}
			<li>
				GPG Key: {gpg_fingerprint $user.gpgauth_pubkey short=true}
			</li>
		{/if}
		<li>
			API Key:
			<a href="#user-apikey" class="reveal-hidden-value">
				Show
				<i class="icon-lock" title="Show API Key"></i>
			</a>
			<span class="hidden-value" id="user-apikey" style="display:none;">{$user.apikey}</span>
		</li>

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
	<a href="#" class="edit-user-toggle">Edit Account</a>
	<br/><br/>

	{if sizeof($logins) > 1}
		Latest Security Logs<br/>
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
	{/if}
</div>


<div id="user-edit-form" style="display:none;">
	{$form->render()}
	<br/>
	<a href="#" class="edit-user-toggle">Cancel</a>
</div>


<script>
	$(function(){
		//$('#formfileinput-avatar-action-upload input').attr('size','5');

		$('.edit-user-toggle').click(function(){
			$('#user-edit-form').toggle();
			$('#user-edit-listing').toggle();

			return false;
		});

		$('.reveal-hidden-value').click(function() {
			$($(this).attr('href')).toggle();
			return false;
		});
	});
</script>