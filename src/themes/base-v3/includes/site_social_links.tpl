{assign var='social_facebook' value=ConfigHandler::Get('/theme/social_link_facebook')}
{assign var='social_foursquare' value=ConfigHandler::Get('/theme/social_link_foursquare')}
{assign var='social_github' value=ConfigHandler::Get('/theme/social_link_github')}
{assign var='social_gittip' value=ConfigHandler::Get('/theme/social_link_gittip')}
{assign var='social_gplus' value=ConfigHandler::Get('/theme/social_link_gplus')}
{assign var='social_instagram' value=ConfigHandler::Get('/theme/social_link_instagram')}
{assign var='social_pinterest' value=ConfigHandler::Get('/theme/social_link_pinterest')}
{assign var='social_linkedin' value=ConfigHandler::Get('/theme/social_link_linkedin')}
{assign var='social_twitter' value=ConfigHandler::Get('/theme/social_link_twitter')}
{assign var='social_vimeo' value=ConfigHandler::Get('/theme/social_link_vimeo')}
{assign var='social_youtube' value=ConfigHandler::Get('/theme/social_link_youtube')}
{assign var='social_generic' value=ConfigHandler::Get('/theme/social_link_generic')}

{if
	$social_facebook ||
	$social_foursquare ||
	$social_github ||
	$social_gittip ||
	$social_gplus ||
	$social_instagram ||
	$social_pinterest ||
	$social_linkedin ||
	$social_twitter ||
	$social_vimeo ||
	$social_youtube ||
	$social_generic
}
	<div class="site-social-links">
		{if $social_facebook}
			<a href="{$social_facebook}" target="_blank" title="Check us out on Facebook!">
				<i class="icon-facebook-square"></i>
			</a>
		{/if}
		{if $social_foursquare}
			<a href="{$social_foursquare}" target="_blank" title="Check us out on Foursquare!">
				<i class="icon-foursquare"></i>
			</a>
		{/if}
		{if $social_github}
			<a href="{$social_github}" target="_blank" title="Fork us on Github!">
				<i class="icon-github-square"></i>
			</a>
		{/if}
		{if $social_gittip}
			<a href="{$social_gittip}" target="_blank" title="Tip us on Gittip!">
				<i class="icon-gittip"></i>
			</a>
		{/if}
		{if $social_gplus}
			<a href="{$social_gplus}" target="_blank" title="Check us out on Google Plus!">
				<i class="icon-google-plus-square"></i>
			</a>
		{/if}
		{if $social_instagram}
			<a href="{$social_instagram}" target="_blank" title="Check us out on Instagram!">
				<i class="icon-instagram"></i>
			</a>
		{/if}
		{if $social_linkedin}
			<a href="{$social_linkedin}" target="_blank" title="Check us out on Linkedin!">
				<i class="icon-linkedin-square"></i>
			</a>
		{/if}
		{if $social_pinterest}
			<a href="{$social_pinterest}" target="_blank" title="Check us out on Pinterest!">
				<i class="icon-pinterest-square"></i>
			</a>
		{/if}
		{if $social_twitter}
			<a href="{$social_twitter}" target="_blank" title="Send us a Tweet!">
				<i class="icon-twitter-square"></i>
			</a>
		{/if}
		{if $social_vimeo}
			<a href="{$social_vimeo}" target="_blank" title="Check us out on Vimeo!">
				<i class="icon-vimeo-square"></i>
			</a>
		{/if}
		{if $social_youtube}
			<a href="{$social_youtube}" target="_blank" title="Check us out on Youtube!">
				<i class="icon-youtube-square"></i>
			</a>
		{/if}
		{if $social_generic}
			<a href="{$social_generic}" target="_blank" title="Check us out here too!">
				<i class="icon-globe"></i>
			</a>
		{/if}
	</div>
{/if}
