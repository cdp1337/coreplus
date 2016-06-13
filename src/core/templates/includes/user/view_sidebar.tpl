{**
 * Helper template for /user/me and /user/view/N to render the sidebar that contains
 * most of the user attributes, particularly those that are short.
 *
 * Expected variables:
 * 
 * $user UserModel of the requested user.
 *}

<ul>
	{foreach $user->getEditableFields() as $k => $dat}
		{* A few fields have custom output configurations/layouts. *}
		
		{if $k == 'avatar'}
			<li class="user-field-{$k}">{img src="`$dat.value`" placeholder="person" width="150" height="300"}</li>
		{elseif $k == 'email' && $dat.value}
			<li class="user-field-{$k}"><i class="icon icon-envelope" title="{$dat.title}"></i> {$dat.value}</li>
		{elseif $k == 'phone' && $dat.value}
			<li class="user-field-{$k}"><i class="icon icon-phone" title="{$dat.title}"></i> {$dat.value}</li>
		{elseif $k == 'mobile_phone' && $dat.value}
			<li class="user-field-{$k}"><i class="icon icon-mobile" title="{$dat.title}"></i> {$dat.value}</li>
		{elseif (strlen($dat.title) + strlen($dat.value)) < 38 && $dat.value}
			<li class="user-field-{$k}">{$dat.title}: {$dat.value|escape}</li>
		{/if}
	{/foreach}

	<li>Member Since: {date format="FD" $user.created}</li>
	{if $user.gpgauth_pubkey}
		<li>
			GPG Key: {gpg_fingerprint $user.gpgauth_pubkey short=true}
		</li>
	{/if}

	{if $profiles}
		{foreach $profiles as $profile}
			<li>
				<i class="icon icon-{$profile.type}"></i>
				<a href="{$profile.url}" rel="me" title="{($profile.title) ? $profile.title : $profile.type}" target="_blank">
					{if $profile.title}{$profile.title}{else}{$profile.url}{/if}
				</a>
			</li>
		{/foreach}
	{/if}
</ul>