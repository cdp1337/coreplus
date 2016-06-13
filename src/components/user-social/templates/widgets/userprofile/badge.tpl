{css src="assets/css/usersocial.css"}{/css}
{*{View::AddHead('<link rel="profile" href="http://microformats.org/profile/hcard"/>')}*}


<meta content="{$user->getDisplayName()}" itemprop="author">

<div class="user-badge user-badge-{$direction} user-badge-{$orientation} vcard {if !$enableavatar}user-badge-no-avatar{/if}" itemscope itemtype="http://schema.org/Person">
	{if $enableavatar}
		<div class="user-badge-photo-wrapper">
			<div class="user-badge-photo-inner">
				<a href="{$link}" class="user-badge-name url" rel="author">
					{img src="`$user->get('avatar')`" placeholder="person" dimensions="64x64^" itemprop="image" class="user-badge-photo photo" alt="`$user->getDisplayName()|escape`"}
				</a>
			</div>
		</div>
	{/if}

	<div class="user-badge-content">
		{if $title}
			<h4>{$title}</h4>
		{/if}

		{* I can do a traditional a tag here because the link is already resolved from the widget. *}
		<a href="{$link}" class="user-badge-name url" rel="author">
			<span class="user-badge-displayname fn nickname" itemprop="name">
				{$user->getDisplayName()}
			</span>
		</a>

		{if $profiles}
			<div class="user-badge-profiles">
				{foreach $profiles as $profile}
					<a itemprop="url"  href="{$profile.url}" rel="me" title="{if $profile.title}{$profile.title}{else}{$profile.url}{/if}">
						<i class="icon icon-{$profile.type}"></i>
					</a>
				{/foreach}
			</div>
		{/if}
	</div>
</div>