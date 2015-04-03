<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		{css src="css/styles.css"}{/css}
		{css src="css/custom.css"}{/css}

		<!--[if lt IE 9]>
			<script type="text/javascript" src="{asset src='js/html5shiv.js'}"></script>
		<![endif]-->
		{script library="fontawesome"}{/script}
		{* This will enable the Core Plus context menus new in 2.4.0 *}
		{script library="jquery"}{/script}
		{script src="js/core.context-controls.js"}{/script}

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>{$seotitle}</title>
	</head>

	<body class="base-v3-skin skin-basic {$body_classes}">
		{widget name="AdminMenu"}

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
			<header>
				<a href="{$smarty.const.ROOT_URL}" title="{$smarty.const.SITENAME|escape}"><img src="{asset src='images/logo.png'}" alt="{$smarty.const.SITENAME|escape}"/></a>
			</header>

			<nav id="primary-nav">
				{widgetarea name="Primary Navigation"}
			</nav>

			<div id="inner-wrapper" class="page-column-width-{$col_width}">
				
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
						<menu id="page-controls" class="page-controls">
							{$controls->fetch()}
						</menu>
					{/if}
				</nav>

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

			<footer class="page-footer">
				{widgetarea name="Footer"}

				<p class="legal-notice">
					Licensed under the
					<a href="https://www.gnu.org/licenses/agpl" target="_blank" title="Licensed Under AGPLv3" class="agplv3-tag">AGPLv3</a>.

					&nbsp;&nbsp;

					Powered by the <a href="http://corepl.us" target="_blank">Secure PHP Framework and CMS, Core Plus</a>.
				</p>
			</footer>
		</div>
	</body>

</html>
