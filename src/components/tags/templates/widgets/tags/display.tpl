<div class="tags-display-widget">
	<span class="tags-display-widget-title">
		{$title}
	</span>
	<span itemprop="keywords">
		{foreach $tags as $keyword}
			<span class="tags-display-widget-keyword">
				{if strpos($keyword.meta_value, 'u:') === 0 && Core::IsComponentAvailable('user-social')}
					{assign var="keyworduser" value=UserModel::Construct(substr($keyword.meta_value,2))}

					<a href="{UserSocialHelper::ResolveProfileLink($keyworduser)}">
						{img src="`$keyworduser->get('avatar')`" placeholder="person" dimensions="24x24" alt="`$keyworduser->getDisplayName()|escape`"}
						{$keyword.meta_value_title}
					</a>
				{else}
					{a href="/page/search?q=tag:`$keyword.meta_value`"}
						{$keyword.meta_value_title}
					{/a}
				{/if}
			</span>
		{/foreach}
	</span>
</div>