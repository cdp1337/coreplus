<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		{css src="css/reset.min.css"}{/css}
		{css src="css/styles.css"}{/css}
		{css src="css/opt/gradients.css" optional="1" default="0"}{/css}
		{css src="css/opt/full-width.css" optional="1" default="0" title="Set the page to be full width"}{/css}
		{css src="css/print.css" media="print"}{/css}
		{css src="css/screen.css" media="screen"}{/css}

		<!--[if lt IE 9]>
			<script type="text/javascript" src="{asset src='js/html5shiv.min.js'}"></script>
		<![endif]-->
		{script library="fontawesome"}{/script}
		<!-- This will enable the Core Plus context menus new in 2.4.0 -->
		{script library="jquery"}{/script}
		{script src="js/core.context-controls.js"}{/script}

		<title>{$seotitle}</title>
	</head>

	<body>
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
								{$crumb.title}
							{/if}

							{if !$smarty.foreach.crumbs.last}
								»
							{/if}
						{/foreach}
					{else}
						{$title}
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
							<p class="message-{$m.mtype} rounded">
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
					This software is distributed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html" target="_BLANK">AGPL v3 License</a>, this means that as a user of this
					service, you are allowed to, (and encouraged to), look at the <a href="http://corepl.us" target="
_BLANK">complete unobfuscated source code of Core Plus</a>.
				</p>
			</footer>
		</div>
	</body>

</html>
