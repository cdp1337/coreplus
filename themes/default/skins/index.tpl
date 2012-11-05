<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		<link href="{asset src='css/reset.css'}" type="text/css" rel="stylesheet"/>
		<link href="{asset src='css/styles.css'}" type="text/css" rel="stylesheet"/>
		<!--[if lt IE 9]>
			<script type="text/javascript" src="{asset src='js/html5.js'}"></script>
		<![endif]-->
		{script library="fontawesome"}{/script}
		<title>{$title}</title>
	</head>

	<body>
		{widget name="AdminMenu"}
		<div id="wrapper" class="column1">
			<header>
				<a href="{$smarty.const.ROOT_URL}" title="Home"><img src="{asset src='logo.png'}" alt="Home"/></a>
			</header>
			<nav id="primary-nav">
				{widgetarea name="Primary Navigation"}
			</nav>
			<div style="clear:both;"></div>
			<div id="innerwrapper" class="rounded-large">
				
				<nav id="breadcrumbs">
					{if isset($breadcrumbs)}
						{foreach from=$breadcrumbs item=crumb name=crumbs}
							{if $crumb.link && !$smarty.foreach.crumbs.last}
								<a href="{$crumb.link}">{$crumb.title}</a>
							{else}
								{$crumb.title}
							{/if}

							{if !$smarty.foreach.crumbs.last}
								&raquo;
							{/if}
						{/foreach}
					{else}
						{$title}
					{/if}
					{if isset($controls) && sizeof($controls)}
						<ul class="controls">
							{foreach from=$controls item=control}
								{if $control instanceof ViewControl}
									{$control->fetch()}
								{else}
									<!-- Deprecated version of control -->
									<li class="{$control.class}">
										{if $control.link}
											<a href="{$control.link}" title="{$control.title}">
												<i class="icon-{$control.class}"></i>
												<span>{$control.title}</span>
											</a>
											{else}
											<i class="icon-{$control.class}"></i>
											{$control.title}
										{/if}
									</li>
								{/if}
							{/foreach}
						</ul>
					{/if}
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
