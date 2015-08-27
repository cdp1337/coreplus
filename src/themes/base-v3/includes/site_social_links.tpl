{if
	$smarty.const.THEME_SOCIAL_FACEBOOK ||
	$smarty.const.THEME_SOCIAL_FOURSQUARE ||
	$smarty.const.THEME_SOCIAL_GITHUB ||
	$smarty.const.THEME_SOCIAL_GITTIP ||
	$smarty.const.THEME_SOCIAL_GPLUS ||
	$smarty.const.THEME_SOCIAL_INSTAGRAM ||
	$smarty.const.THEME_SOCIAL_PINTEREST ||
	$smarty.const.THEME_SOCIAL_LINKEDIN ||
	$smarty.const.THEME_SOCIAL_TWITTER ||
	$smarty.const.THEME_SOCIAL_VIMEO ||
	$smarty.const.THEME_SOCIAL_YOUTUBE ||
	$smarty.const.THEME_SOCIAL_GENERIC
}
	<div class="site-social-links">
		{if $smarty.const.THEME_SOCIAL_FACEBOOK}
			<a href="{$smarty.const.THEME_SOCIAL_FACEBOOK}" target="_blank" title="Check us out on Facebook!">
				<i class="icon-facebook-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_FOURSQUARE}
			<a href="{$smarty.const.THEME_SOCIAL_FOURSQUARE}" target="_blank" title="Check us out on Foursquare!">
				<i class="icon-foursquare"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_GITHUB}
			<a href="{$smarty.const.THEME_SOCIAL_GITHUB}" target="_blank" title="Fork us on Github!">
				<i class="icon-github-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_GITTIP}
			<a href="{$smarty.const.THEME_SOCIAL_GITTIP}" target="_blank" title="Tip us on Gittip!">
				<i class="icon-gittip"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_GPLUS}
			<a href="{$smarty.const.THEME_SOCIAL_GPLUS}" target="_blank" title="Check us out on Google Plus!">
				<i class="icon-google-plus-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_INSTAGRAM}
			<a href="{$smarty.const.THEME_SOCIAL_INSTAGRAM}" target="_blank" title="Check us out on Instagram!">
				<i class="icon-instagram"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_LINKEDIN}
			<a href="{$smarty.const.THEME_SOCIAL_LINKEDIN}" target="_blank" title="Check us out on Linkedin!">
				<i class="icon-linkedin-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_PINTEREST}
			<a href="{$smarty.const.THEME_SOCIAL_PINTEREST}" target="_blank" title="Check us out on Pinterest!">
				<i class="icon-pinterest-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_TWITTER}
			<a href="{$smarty.const.THEME_SOCIAL_TWITTER}" target="_blank" title="Send us a Tweet!">
				<i class="icon-twitter-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_VIMEO}
			<a href="{$smarty.const.THEME_SOCIAL_VIMEO}" target="_blank" title="Check us out on Vimeo!">
				<i class="icon-vimeo-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_YOUTUBE}
			<a href="{$smarty.const.THEME_SOCIAL_YOUTUBE}" target="_blank" title="Check us out on Youtube!">
				<i class="icon-youtube-square"></i>
			</a>
		{/if}
		{if $smarty.const.THEME_SOCIAL_GENERIC}
			<a href="{$smarty.const.THEME_SOCIAL_GENERIC}" target="_blank" title="Check us out here too!">
				<i class="icon-globe"></i>
			</a>
		{/if}
	</div>
{/if}
