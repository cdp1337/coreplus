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

{if $agents}
	<p class="message-tutorial">
		Below are the list of remote servers that are using this component.  This section only appears to super admins.
	</p>
	<table class="listing">
		<tr>
			<th>Remote Site</th>
			<th>Remote IP Address</th>
			<th>Latest Checkin</th>
			<th>Latest Version of {$latest.name}</th>
		</tr>
		{foreach $agents as $rec}
			<tr>
				<td>{$rec.site}</td>
				<td>{geoiplookup $rec.ip} {$rec.ip}</td>
				<td>{date format="SDT" $rec.datetime}</td>
				<td>{$rec.version}</td>
			</tr>
		{/foreach}
	</table>
{/if}

<div class="all-package-versions">
	<p class="message-tutorial">
		Historical versions for {$latest.name} that are included in this repository.  Also included is the full changelog as published by the maintainer.
	</p>
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