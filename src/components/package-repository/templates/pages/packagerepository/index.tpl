{css src="css/package-repository.css"}{/css}

{if $is_admin}
	<form id="progress-log-form" name="progress-log-form" target="progress-log" method="POST" action="{link '/packagerepository/rebuild'}">
		<input type="submit" value="{t 'STRING_PACKAGE_REPOSITORY_REBUILD'}"/>
	</form>

	{progress_log_iframe}
{/if}

{if $version_selector}
	{$version_selector->render()}
{/if}

<div class="package-list">
	{foreach $packages as $pkg}
		<div class="package">
			{img src=$pkg.package.logo dimensions="96x96" class="package-screenshot" placeholder="generic"}
			{a href="/packagerepository/details?type=`$pkg.package.type`&key=`$pkg.package.key`" class="package-name"}{$pkg.package.name}{/a}

			{if $pkg.package.description}
				<p class="package-description">
					{$pkg.package.description|truncate:300}
				</p>
			{/if}
			
			{foreach $pkg.package->getScreenshots() as $screen}
				{a class="screenshot-previewer" href="`$screen->getPreviewURL('800x480')`" data-lightbox="pkg-`$pkg.package.id`"}
					{img file=$screen dimensions="32x32" class="package-screenshot"}
				{/a}
			{/foreach}
			
			<span class="package-release-date">
				Released 
				{date $pkg.package.datetime_released format="FD"}	
			</span>
			
			{if !$version_selected}
				<span class="package-compatibility-versions">
					Compatible with Core Plus {$pkg.min} - {$pkg.max}	
				</span>	
			{/if}
		</div>
	{/foreach}
</div>

{script library="jquery.lightbox"}{/script}