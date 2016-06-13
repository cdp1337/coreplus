{**
 * Public view for the user profiles
 *}

{css src="assets/css/usersocial.css"}{/css}

{View::AddHead('<link rel="profile" href="http://microformats.org/profile/hcard"/>')}

<div class="user-full-profile vcard" itemscope itemtype="http://schema.org/Person">

	<div class="user-full-profile-imagewrapper">
		{img src="public/user/`$user->get('avatar')`" placeholder="person" width="250" height="300" itemprop="image" class="user-full-profile-photo photo"}
	</div>

	<div class="user-full-profile-displayname fn nickname">
		<span itemprop="name">{$user->getDisplayName()}</span>'s Public Profile
	</div>

	<div class="user-full-profile-bio note" itemprop="description">
		{$user->get('bio')}
	</div>

	{if $profiles}
		<ul>
			<li><b>Connected Profiles</b></li>
			{foreach $profiles as $profile}
				<li>
					<a href="{$profile.url}" rel="me">
						<i class="icon icon-{$profile.type}"></i>{if $profile.title}{$profile.title}{else}{$profile.url}{/if}
					</a>
				</li>
			{/foreach}
		</ul>
	{/if}

	{widgetarea name="Public User Profile" installable="/user-social/view/`$user->get('id')`"}

</div>