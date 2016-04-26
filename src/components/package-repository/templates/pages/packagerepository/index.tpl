{css src="css/package-repository.css"}{/css}

{if $version_selector}
	{$version_selector->render()}
{/if}

<div class="package-list">
	{foreach $packages as $pkg}
		<div class="package">
			{img src=$pkg.package->getScreenshot() dimensions="175x175" class="package-screenshot" placeholder="generic"}
			<span class="package-name">{$pkg.package.name}</span>
			{if $pkg.package.description}
				<p class="package-description">
					{$pkg.package.description|truncate:300}		
				</p>
			{/if}
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