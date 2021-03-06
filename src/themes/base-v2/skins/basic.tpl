<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		{css src="css/styles.css"}{/css}
		{css src="css/opt/gradients.css" optional="1" default="0"}{/css}
		{css src="css/opt/full-width.css" optional="1" default="0" title="Set the page to be full width"}{/css}

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

	<body class="{$body_classes}">
		{widget name="AdminMenu"}
		<div id="wrapper" class="column1">
			<header>
				<a href="{$smarty.const.ROOT_URL}" title="Home"><img src="{asset src='images/logo.png'}" alt="Home"/></a>
			</header>
			<nav id="primary-nav">
				{widgetarea name="Primary Navigation"}
			</nav>
			<div style="clear:both;"></div>
			<div id="innerwrapper">
				
				<nav id="breadcrumbs">
					{if isset($breadcrumbs)}
						{foreach from=$breadcrumbs item=crumb name=crumbs}
							{if $crumb.link && !$smarty.foreach.crumbs.last}
								<a href="{$crumb.link}">{$crumb.title}</a>
							{else}
								<h1>{$crumb.title}</h1>
							{/if}

							{if !$smarty.foreach.crumbs.last}
								»
							{/if}
						{/foreach}
					{else}
						<h1>{$title}</h1>
					{/if}

					<menu id="controls" style="float:right; width: 150px;">
						{$controls->fetch()}
					</menu>
				</nav>
				
				<aside id="leftcol" class="pagecolumn">

					{*widget name="/Content/View/5"*}
					{widgetarea name="Left Column"}
				</aside>
				<section class="pagecontent">
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
				<div style="clear:both;"></div>
			</div>
			<footer>
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
