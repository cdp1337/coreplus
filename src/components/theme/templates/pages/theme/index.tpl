{css href="assets/css/theme/admin.css"}{/css}

<h3>Themes Installed</h3>
<p class="message-tutorial">
	Themes are the main top-level element controlling how your site looks.
	You can have multiple themes installed and ready, but only one theme activated at a time.
	Only the currently active theme can be used for selecting skins and email skins.
</p>

<div class="theme-picker">
	{foreach $themes as $theme}

		{assign var='screen' value=$theme->getScreenshot()}

		<div class="theme {if $theme->isDefault()}current-theme{/if}">
			{$theme->getName()}<br/>

			{img src="`$screen.file`" dimensions="220x160" placeholder="generic"}<br/><br/>

			{if !$theme->isDefault()}
				{a class="button" href="/theme/setdefault/`$theme->getKeyName()`" confirm="Set `$theme->getName()` as site-wide default theme?"}
					Set As Default
				{/a}
			{else}
				Current Default
			{/if}
		</div>
	{/foreach}
</div>

<div class="clear"></div>

<br/>
<h3>Theme {$current->getName()} Skins</h3>
<p class="message-tutorial">
	Theme skins are your site's main container controlling the entire look and feel.
	Some applications can use a different skin if your theme has multiple to choose from, but generally the "public" skin
	is used for all public-facing, (anonymous), pages.
	The admin skin will only be used for administrative pages.
</p>

<table class="listing">
	<tr>
		<th colspan="1">Skin</th>
		<th>Public Default</th>
		<th>Admin Default</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$current->getSkins() item=template}
		<tr>
			<td>
				{if $template.title && $template.title != $template.file}
					{$template.title}
					( {$template.file} )
				{else}
					{$template.file}
				{/if}
			</td>
			<td>
				{if $template.default}
					<i class="icon-star"></i>
					<span>Current Default</span>
				{else}
					{a href="/theme/setdefault/`$current->getKeyName()`?template=`$template.file`" confirm="Set `$template.file` as default?"}
						<i class="icon-ok"></i>
						<span>Set As Default</span>
					{/a}
				{/if}
			</td>
			<td>
				{if $template.admindefault}
					<i class="icon-star"></i>
					<span>Current Default</span>
				{else}
					{a href="/theme/setadmindefault/`$current->getKeyName()`?template=`$template.file`" confirm="Set `$template.file` as default for admin pages?"}
						<i class="icon-ok"></i>
						<span>Set As Default</span>
					{/a}
				{/if}
			</td>
			<td>
				<ul class="controls controls-hover">
					<li>
						{a href="/theme/widgets/`$current->getKeyName()`?template=`$template.file`"}
							<i class="icon-cogs"></i>
							<span>Widgets</span>
						{/a}
					</li>

					{if $template.has_stylesheets}
						<li>
							{a href="/theme/selectstylesheets/?template=skins/`$template.file`"}
								<i class="icon-strikethrough"></i>
								<span>Optional Stylesheets</span>
							{/a}
						</li>
					{/if}

					<li>
						{a href="/theme/editor?template=skins/`$template.file`"}
							<i class="icon-pencil"></i>
							<span>Editor</span>
						{/a}
					</li>

				</ul>

			</td>
		</tr>
	{/foreach}
</table>


<br/><br/>
<h3>Theme {$current->getName()} Email Skins</h3>
{if sizeof($current->getEmailSkins()) == 1}
	<p class="message-info">
		Your currently selected theme has no email skins available.
	</p>
{else}
	<p class="message-tutorial">
		The email skin is an optional container for all outbound emails sent to your users.
		If you want to stylize your automated site communications, select the skin here.
	</p>

	<table class="listing">
		<tr>
			<th colspan="1">Skin</th>
			<th>Default</th>
			<th width="100">&nbsp;</th>
		</tr>
		{foreach from=$current->getEmailSkins() item=template}
			<tr>
				<td>
					{if $template.title && $template.title != $template.file && $template.file}
						{$template.title}
						( {$template.file} )
					{elseif $template.title}
						{$template.title}
					{else}
						{$template.file}
					{/if}
				</td>
				<td>
					{if $template.default}
						<i class="icon-star"></i>
						<span>Current Default</span>
					{else}
						{a href="/theme/setemaildefault/`$current->getKeyName()`?template=`$template.file`" confirm="Set `$template.file` as default?"}
							<i class="icon-ok"></i>
							<span>Set As Default</span>
						{/a}
					{/if}
				</td>
				<td>
					<ul class="controls controls-hover">
						{if $template.file}
							<li>
								{a href="/theme/editor?template=emailskins/`$template.file`"}
									<i class="icon-pencil"></i>
									<span>Editor</span>
								{/a}
							</li>
						{/if}

					</ul>

				</td>
			</tr>
		{/foreach}
	</table>

{/if}










{css}<style>
	.directory-listing ul {
		margin: 0;
	}
	.directory-listing li {
		margin-left: 1em;
		list-style: none;
	}

	.directory-listing li span {
		line-height: 28px;
	}

	.directory-listing .collapsed ul {
		display: none;
	}

	.directory-listing .expanded-hint,
	.directory-listing .collapsed-hint {
		cursor: pointer;
	}

	.directory-listing .collapsed > .expanded-hint{
		display:none;
	}
	.directory-listing .expanded > .collapsed-hint{
		display:none;
	}

	.directory-listing .filename {
		display: inline-block;
		width: 120px;
	}
	.directory-listing .inline-control {
		display: inline-block;
		font-size: 85%;
		white-space: nowrap;
		width: 120px;
	}
	</style>{/css}

	{script location="foot"}<script>
	$('.expanded-hint').click(function(){
		$(this).closest('li').removeClass('expanded').addClass('collapsed');
		return false;
	});
	$('.collapsed-hint').click(function(){
		$(this).closest('li').removeClass('collapsed').addClass('expanded');
		return false;
	});
	</script>{/script}

{function name=printAssetList}
	<ul>
		{foreach $items as $key => $item}
			<li class="collapsed">
				{if isset($item.obj)}
					<img src="{$item.obj->getMimetypeIconURL('24x24')}"/>
					<span>{$key}</span>
					{a href="/theme/editor?file=`$item.file`" title="Edit Asset"}<i class="icon-pencil"></i>{/a}
				{else}
					<i class="icon-folder-close collapsed-hint"></i>
					<i class="icon-folder-open expanded-hint"></i>
					<span>{$key}</span>
					{call name=printAssetList items=$item}
				{/if}
			</li>
		{/foreach}
	</ul>
{/function}


{function name=printTemplateList}
	<ul>
		{foreach $items as $key => $item}
			<li class="collapsed">
				{if isset($item.obj)}
					{img file=$item.obj dimensions="24x24"}
					<span class="filename" title="{$key|escape}">{$key}</span>
					{if $item.haswidgets}
						{a class="inline-control" href="/theme/widgets/?page=`$item.file`" title="Manage Widgets"}
							<i class="icon-cogs"></i>
							<span>Manage Widgets</span>
						{/a}
					{/if}
					{if $item.has_stylesheets}
						{a class="inline-control" href="/theme/selectstylesheets/?template=skins/`$item.file`" title="Optional Stylesheets"}
							<i class="icon-strikethrough"></i>
							<span>Optional Stylesheets</span>
						{/a}
					{/if}
					{a class="inline-control" href="/theme/editor?template=`$item.file`" title="Edit Template"}
						<i class="icon-pencil"></i>
						<span>Edit Template</span>
					{/a}
				{else}
					<i class="icon-folder-close collapsed-hint" title="Click to expand"></i>
					<i class="icon-folder-open expanded-hint" title="Click to close"></i>
					<span>{$key}</span>
					{call name=printTemplateList items=$item}
				{/if}
			</li>
		{/foreach}
	</ul>
{/function}


<br/><br/>
<h3>Assets</h3>
<p class="message-tutorial">
	Assets are stylesheets, javascript files, and other static resources used by components that get installed to your CDN.
</p>
<div class="directory-listing">
	{call name=printAssetList items=$assets.assets}
</div>


{if sizeof($templates)}
	<br/><br/>
	<h3>Templates</h3>
	<p class="message-tutorial">
		Templates are pages, emails, widgets, and other views that are used throughout your site.
	</p>

	<div class="directory-listing">
		{call name=printTemplateList items=$templates}
	</div>
{/if}