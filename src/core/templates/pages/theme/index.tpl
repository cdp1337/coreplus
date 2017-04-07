{css href="assets/css/theme/admin.css"}{/css}

<h3>Themes Installed</h3>
<p class="message-tutorial">
	Themes are the main top-level element controlling how your site looks.
	You can have multiple themes installed and ready, but only one theme activated at a time.
	Only the currently active theme can be used for selecting skins and email skins.
</p>

<div class="theme-picker theme-section">
	{if $theme_selection_enabled}
		{foreach $themes as $theme}

			{assign var='screen' value=$theme->getScreenshot()}

			<div class="theme {if $theme->isDefault()}current-theme{/if}">
				{$theme->getName()}<br/>

				{if $screen.file}
					<!--{img src="assets/images/placeholders/generic.png" dimensions="220x160"}-->
					<img src="{$screen.file->getPreviewURL('220x160')}" title="{$screen.title}"/>
				{else}
					{img src="assets/images/placeholders/generic.png" dimensions="220x160"}
				{/if}
				<br/>

				{if !$theme->isDefault()}
					{a class="button" href="/theme/setdefault/`$theme->getKeyName()`" confirm="Set `$theme->getName()` as site-wide default theme?"}
						Set As Default
					{/a}
				{else}
					Current Default
				{/if}
			</div>
		{/foreach}
	{else}
		{foreach $themes as $theme}
			{if $theme->isDefault()}
				{assign var='screen' value=$theme->getScreenshot()}
				<div class="theme current-theme">
					{$theme->getName()}<br/>
	
					{if $screen.file}
						<!--{img src="assets/images/placeholders/generic.png" dimensions="220x160"}-->
						<img src="{$screen.file->getPreviewURL('220x160')}" title="{$screen.title}"/>
					{else}
						{img src="assets/images/placeholders/generic.png" dimensions="220x160"}
					{/if}
					<br/>
	
					Current Default
				</div>
			{/if}
		{/foreach}
	{/if}
</div>

<div class="theme-section">
	<h3>Theme {$current->getName()} Configurable Options</h3>
	{if $options_form}
		{$options_form->render()}
	{else}
		<p class="message-info">
			There are no configurable options for your selected theme.
		</p>
	{/if}
</div>

{if $theme_selection_enabled}
	<div class="theme-section">
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
							<i class="icon icon-star"></i>
							<span>Current Default</span>
						{else}
							{a href="/theme/setdefault/`$current->getKeyName()`?template=`$template.file`" confirm="Set `$template.file` as default?"}
								<i class="icon icon-ok"></i>
								<span>Set As Default</span>
							{/a}
						{/if}
					</td>
					<td>
						{if $template.admindefault}
							<i class="icon icon-star"></i>
							<span>Current Default</span>
						{else}
							{a href="/theme/setadmindefault/`$current->getKeyName()`?template=`$template.file`" confirm="Set `$template.file` as default for admin pages?"}
								<i class="icon icon-ok"></i>
								<span>Set As Default</span>
							{/a}
						{/if}
					</td>
					<td>
						<ul class="controls controls-hover">
							<li>
								{a href="/admin/widgets?skin=`$template.file`"}
									<i class="icon icon-cogs"></i>
									<span>Widgets</span>
								{/a}
							</li>
	
							{if $template.has_stylesheets}
								<li>
									{a href="/theme/selectstylesheets/?template=skins/`$template.file`"}
										<i class="icon icon-strikethrough"></i>
										<span>Optional Stylesheets</span>
									{/a}
								</li>
							{/if}
	
							<li>
								{a href="/theme/editor?template=skins/`$template.file`"}
									<i class="icon icon-pencil"></i>
									<span>Editor</span>
								{/a}
							</li>
	
						</ul>
	
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}

{if $theme_selection_enabled}
	<div class="theme-section">
		<h3>Theme {$current->getName()} Site Skin Options</h3>
		<p class="message-tutorial">
			Any application that supports a skin to be set site-wide.
		</p>
		{$site_skins_form->render()}
	</div>
{/if}

{if $email_selection_enabled}
	<div class="theme-section">
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
								<i class="icon icon-star"></i>
								<span>Current Default</span>
							{else}
								{a href="/theme/setemaildefault/`$current->getKeyName()`?template=`$template.file`" confirm="Set `$template.file` as default?"}
									<i class="icon icon-ok"></i>
									<span>Set As Default</span>
								{/a}
							{/if}
						</td>
						<td>
							<ul class="controls controls-hover">
								{if $template.file}
									<li>
										{a href="/theme/editor?template=emailskins/`$template.file`"}
											<i class="icon icon-pencil"></i>
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
	</div>
{/if}

{if !$multisite}
	<div class="theme-section">
		<h3>Edit Custom CSS</h3>
		{if $cssform}
			<p class="message-tutorial">
				This stylesheet allows you to add custom styles to your site.
				
				{a href="/theme/editor?file=assets/css/custom.css"}view all revisions{/a}.
			</p>
			
			<div id="theme-editor-wysiwyg">
				{$cssform->render()}
			</div>
		{else}
			<p class="message-tutorial">
				Please ensure that themes/custom exists and is writable by the web server in order to enable custom CSS editing!
			</p>
		{/if}
	</div>
{/if}

{if $cssprintform && !$multisite}
	<div class="theme-section">
		<h3>Edit Custom Print CSS</h3>
		<p class="message-tutorial">
			This stylesheet allows you to add custom styles to the print styles of your site.<br/><br/>
			Print styles take effect automatically when a page is printed and for operations such as PDF generation.
			You can also
			{a href="/theme/editor?file=assets/css/custom_print.css"}view all revisions{/a}.
		</p>
		
		<div id="theme-editor-wysiwyg">
			{$cssprintform->render()}
		</div>
	</div>
{/if}


{function name=printTemplateList}
	<ul>
		{foreach $items as $key => $item}
			{if isset($item.obj)}
				<li class="file">
					{assign var="imgsrc" value="`$item.obj->getPreviewURL('22x22')`"}
					{*img file=$item.obj dimensions="24x24" alt="thing" assign="imgsrc"*}
					
					<img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="{$imgsrc}" data-isloaded="0"/>
					<span class="filename" title="{$key|escape}">{$key}</span>
					
					<span class="file-modified" title="Date Modified">{date format="SDT" $item.obj->getMTime()}</span>
					
					{if $item.haswidgets}
						<a class="inline-control" href="{$url_themewidgets}?page={$item.file}" title="Manage Widgets">
							<i class="icon icon-cogs"></i>
							<span>Widgets</span>
						</a>
					{/if}
					{if $item.has_stylesheets}
						<a class="inline-control" href="{$url_themestylesheets}?template=skins/{$item.file}" title="Optional Stylesheets">
							<i class="icon icon-strikethrough"></i>
							<span>Optional Stylesheets</span>
						</a>
					{/if}
					{if $item.type == 'template'}
						<a class="inline-control" href="{$url_themeeditor}?template={$item.file}" title="Edit Template">
							<i class="icon icon-pencil"></i>
							<span>Edit</span>
						</a>
					{/if}
					{* <a href="{$url_themeeditor}?file={$item.file}" title="Edit Asset"><i class="icon icon-pencil"></i></a> *}
				</li>
			{else}
				<li class="collapsed">
					<span class="collapsed-hint" title="Click to expand">
						<i class="icon icon-folder-close"></i>
						<span class="folder-name">{$key}</span>
						<span class="folder-children-count">{t 'STRING_N_ITEM' sizeof($item)}</span>
					</span>
					<span class="expanded-hint" title="Click to close">
						<i class="icon icon-folder-open"></i>
						<span class="folder-name">{$key}</span>
					</span>
					
					{call name=printTemplateList items=$item}	
				</li>
			{/if}
		{/foreach}
	</ul>
{/function}


{if sizeof($templates) && !$multisite}
	<fieldset class="collapsed collapsible theme-section" id="theme-expandable-templates">
		<h3 class="fieldset-title">
			Templates
			<i class="icon icon-chevron-down expandable-hint"></i>
			<i class="icon icon-chevron-up collapsible-hint"></i>
		</h3>
		<p class="message-tutorial">
			Templates are pages, emails, widgets, and other views that are used throughout your site.
		</p>

		<div class="directory-listing">
			{call name=printTemplateList items=$templates}
		</div>
	</fieldset>
{/if}



{script location="foot"}<script>
	$('.expanded-hint').click(function(){
		$(this).closest('li').removeClass('expanded').addClass('collapsed');
		return false;
	});
	$('.collapsed-hint').click(function(){
		var $this = $(this),
			$li = $this.closest('li'),
			$immediateFiles = $li.children('ul').children('li.file');
		
		$li.removeClass('collapsed').addClass('expanded');
		
		// Now, run through every img located herein and set the src if necessary.
		// This facilitates the lazy-loading operation.
		$immediateFiles.each(function(){
			var $ct = $(this),
				$img = $ct.children('img');
			//console.log($img);
			//console.log($img.data('isloaded'));
			if($img.data('isloaded') === 0){
				$img.attr('src', $img.data('src'));
				$img.data('isloaded', 1);
			}
		});
		
		return false;
	});
</script>{/script}
{if Core::IsComponentAvailable('codemirror')}
	{script library="codemirror_css"}{/script}
	{css href="assets/codemirror/theme/ambiance.css"}{/css}
	{script location="foot"}<script>
		CodeMirror.fromTextArea(document.getElementById("custom_content_0"), {
			//theme: 'ambiance',
			lineNumbers: true,
			lineWrapping: true,
			mode: 'css'
		});
		CodeMirror.fromTextArea(document.getElementById("custom_content_1"), {
			//theme: 'ambiance',
			lineNumbers: true,
			lineWrapping: true,
			mode: 'css'
		});
	</script>{/script}
{/if}