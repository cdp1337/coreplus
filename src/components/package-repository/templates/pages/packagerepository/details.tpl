{css}<style>
	.package-version-details {
		margin-bottom: 1em;
		border-bottom: 1px solid;
	}
</style>{/css}

<h1>{$latest.name}</h1>
{img src=$latest.logo dimensions="96x96" class="package-screenshot" placeholder="generic"}

{if $latest.description}
	<p class="package-description">
		{$latest.description}
	</p>
{/if}

{foreach $latest->getScreenshots() as $screen}
	{a class="screenshot-previewer" href="`$screen->getPreviewURL('800x480')`" data-lightbox="pkg-`$pkg.package.id`"}
		{img file=$screen dimensions="32x32" class="package-screenshot"}
	{/a}
{/foreach}

<div class="all-package-versions">
	{foreach $all as $p}
		<div class="package-version-details">
			<h2>{$p.name} {$p.version}</h2>
			{if $p.datetime_released}
				Released {date format="FD" $p.datetime_released}<br/>
			{/if}
			Built for Core Plus {$p.packager}<br/>
			{if $p.packager_name}
				Author: {$p.packager_name} <a href="mailto:{$p.packager_email}"><i class="icon icon-envelope-o"></i></a><br/>	
			{/if}
			
			{if $p.changelog}
				<h3>{$p.version} Changelog</h3>
				{$p.changelog}
			{/if}
		</div>
	{/foreach}
</div>


{script library="jquery.lightbox"}{/script}