<div id="user-edit-listing">
	{img src="`$user->get('avatar')`" placeholder="person" width="150" height="300"}


	<ul>
		<li>User Name: {$user->getDisplayName()}</li>
		<li>Email: {$user.email}</li>
		<li>Member Since: {date format="FD" $user.created}</li>

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
				<th>Date</th>
				<th>IP</th>
				<th>Location</th>
				<th>Notes</th>
			</tr>
			{foreach $logins as $login}
				<tr>
					<td>{date format="SDT" $login.datetime}</td>
					<td>{$login.ip_addr}</td>
					<td>{geoiplookup $login.ip_addr}</td>
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
	});
</script>