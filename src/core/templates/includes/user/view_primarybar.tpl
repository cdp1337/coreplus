{**
 * Helper template for /user/me and /user/view/N to render the sidebar that contains
 * most of the user attributes, particularly those that are short.
 *
 * Expected variables:
 * 
 * $user UserModel of the requested user.
 *}

<dl>
	{foreach $user->getEditableFields() as $k => $dat}
		{* A few fields have custom output configurations/layouts. *}

		{if $k == 'avatar' || $k == 'email' || $k == 'phone' || $k == 'mobile_phone'}
			{* These fields are already rendered on the sidebar! *}
		{elseif (strlen($dat.title) + strlen($dat.value)) >= 38}
			<dt class="user-field-{$k}">{$dat.title}</dt>
			<dd class="user-field-{$k}">{$dat.value|escape|nl2br}</dd>
		{/if}
	{/foreach}
	
	<dt>
		API Key:
	</dt>
	<dd>
		<a href="#user-apikey" class="reveal-hidden-value">
			Show
			<i class="icon icon-lock" title="Show API Key"></i>
		</a>
		<span class="hidden-value" id="user-apikey" style="display:none;">{$user.apikey}</span>
	</dd>
</dl>

{if isset($groups)}
	<hr/>
	<h3>User Group Memberships</h3>
	<ul>
		{foreach $groups as $g}
			<li>{$g.name}</li>
		{foreachelse}
			<li>Not a member of any groups!</li>
		{/foreach}
	</ul>
{/if}