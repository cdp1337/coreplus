<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		{css src="css/styles.css"}{/css}
		{css src="css/custom.css" inline="1"}{/css}
		{css src="css/custom_print.css" inline="1" media="print"}{/css}

		<!--[if lt IE 9]>
			<script type="text/javascript" src="{asset src='js/html5shiv.js'}"></script>
			<script type="text/javascript" src="{asset src='js/json2.js'}"></script>
		<![endif]-->
		{script library="fontawesome"}{/script}
		{* This will enable the Core Plus context menus new in 2.4.0 *}
		{script library="jquery"}{/script}
		{script src="js/core.context-controls.js"}{/script}

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>{$seotitle}</title>
	</head>

	<body class="base-v3-skin skin-basic {$body_classes}">
		{widget baseurl="adminmenu/view"}

		{**
		 * Retrieve the contents of both headers first so I know the number of columns to display.
		 * This is particularly useful for collapsing the space of an unused column without needing weird javascript.
		 *}
		{widgetarea name="Left Column" assign="left_col"}
		{widgetarea name="Right Column" assign="right_col"}

		{if $left_col && $right_col}
			{assign var="col_width" value="2"}
		{elseif $left_col || $right_col}
			{assign var="col_width" value="1"}
		{else}
			{assign var="col_width" value="0"}
		{/if}

		<div class="outer-wrapper" id="outer-wrapper">
			<header class="page-header">
				{a href="`$smarty.const.ROOT_URL`" title="`$smarty.const.SITENAME|escape`"}
					{if $smarty.const.THEME_SITE_LOGO}
						{img src="`$smarty.const.THEME_SITE_LOGO`" alt="`$smarty.const.SITENAME|escape`"}
					{else}
						{img src="assets/images/logo.png" alt="`$smarty.const.SITENAME|escape`"}
					{/if}
				{/a}

				{include file='includes/site_social_links.tpl'}
				{include file='includes/site_schema_information.tpl'}
			</header>

			<nav id="primary-nav">
				{widgetarea name="Primary Navigation"}
			</nav>

			<div id="inner-wrapper" class="page-column-width-{$col_width}">

				<!--[if lt IE 9]>
					<p class="message-error">
						Internet Explorer 8.0 and lower is unsupported and usability of this site is not guaranteed.
						For your own safety, please upgrade to either a
						<a href="http://www.mozilla.com/firefox/" target="_blank">better</a>
						<a href="https://www.google.com/chrome/browser/desktop/" target="_blank">browser</a>
						or the latest version of
						<a href="http://windows.microsoft.com/en-US/internet-explorer/download-ie" target="_blank">IE</a>.
					</p>
				<![endif]-->
				
				<nav id="breadcrumbs">
					{if isset($breadcrumbs)}
						{foreach from=$breadcrumbs item=crumb name=crumbs}
							{if $crumb.link && !$smarty.foreach.crumbs.last}
								<a href="{$crumb.link}" class="page-breadcrumb">{$crumb.title}</a>
							{else}
								<span class="page-breadcrumb">{$crumb.title}</span>
							{/if}

							{if !$smarty.foreach.crumbs.last}
								»
							{/if}
						{/foreach}
					{else}
						<span class="page-breadcrumb">{$title}</span>
					{/if}

					{if $controls->hasLinks()}
						<div id="page-controls-wrapper" class="page-controls-wrapper">
							<menu id="page-controls" class="page-controls">
								{$controls->fetch()}
							</menu>	
						</div>
					{/if}
				</nav>
				
				<div class="page-content-and-columns-wrapper">
					{if $left_col}
						<!-- There are contents in the Left Column widget, render that aside! -->
						<aside id="left-col" class="page-column">
							{$left_col}
						</aside>
					{else}
						<!-- The Left Column widget is empty, skipping rendering of aside#left-col. -->
					{/if}

					<section class="page-content">
						{if !empty($messages)}
							{foreach from=$messages item="m"}
								<p class="message-{$m.mtype}">
									{$m.mtext}
								</p>
							{/foreach}
						{/if}

						{widgetarea name="Above Body"}

						{$body}

						{widgetarea name="After Body"}
					</section>

					{if $right_col}
						<!-- There are contents in the Right Column widget, render that aside! -->
						<aside id="right-col" class="page-column">
							{$right_col}
						</aside>
					{else}
						<!-- The Right Column widget is empty, skipping rendering of aside#right-col. -->
					{/if}
				</div>
			</div>

			<footer class="page-footer">
				{widgetarea name="Footer"}

				{include file='includes/site_schema_information.tpl'}

				{include file='includes/site_social_links.tpl'}

				{Core::_GetLegalFooterContent()}
			</footer>
		</div>
	</body>

</html>
