<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<link href="{asset src='css/reset.css'}" type="text/css" rel="stylesheet"/>
		<link href="{asset src='css/styles.css'}" type="text/css" rel="stylesheet"/>
		<!--[if lt IE 9]>
			<script type="text/javascript" src="{asset src='js/html5.js'}"></script>
		<![endif]-->
		<title>{$seotitle}</title>
	</head>

	<body>
		<div id="wrapper" class="column1">
			<header>
				<a href="{$smarty.const.ROOT_URL}" title="Home"><img src="{asset src='logo.png'}" alt="Home"/></a>
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
								&raquo;
							{/if}
						{/foreach}
					{else}
						{$title}
					{/if}
					{if isset($controls) && sizeof($controls)}
						<ul class="controls">
							{foreach from=$controls item=control}
								<li class="{$control.class}">
									{if $control.link}
										<a href="{$control.link}" title="{$control.title}">{$control.title}</a>
									{else}
										{$control.title}
									{/if}
								</li>
							{/foreach}
						</ul>
					{/if}
				</nav>
				
				<section class="pagecontent">
					{if !empty($messages)}
						{foreach from=$messages item="m"}
							<p class="message-{$m.mtype} rounded">
								{$m.mtext}
							</p>
						{/foreach}
					{/if}
					
					{$body}
				</section>
				<div style="clear:both;"></div>
			</div>
			<footer>
				{widgetarea name="Footer"}
			</footer>
		</div>
	</body>

</html>
