<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		{css src="css/print.css" inline="1"}{/css}
		{css src="css/custom.css" inline="1"}{/css}
		{css src="css/custom_print.css" inline="1"}{/css}
		<!--[if lt IE 9]>
		<script type="text/javascript" src="{asset src='js/html5.js'}"></script>
		<script type="text/javascript" src="{asset src='js/json2.js'}"></script>
		<![endif]-->
		{script library="fontawesome"}{/script}

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		{if $seotitle}<title>{$seotitle}</title>{/if}
	</head>

	<body class="base-v3-dark-skin skin-print{if $body_classes} {$body_classes}{/if}">
		<div class="outer-wrapper" id="outer-wrapper">
			<header class="page-header">
				{assign var='site_logo' value=ConfigHandler::Get('/theme/site_logo')}

				{a href="`$smarty.const.ROOT_URL`" title="`$smarty.const.SITENAME|escape`"}
					{if $site_logo}
						{img src="`$site_logo`" alt="`$smarty.const.SITENAME|escape`"}
					{else}
						{img src="assets/images/logo.png" alt="`$smarty.const.SITENAME|escape`"}
					{/if}
				{/a}

				{include file='includes/site_schema_information.tpl'}
			</header>

			<div id="inner-wrapper" class="page-column-width-0">
				<section class="page-content">
					{$body}
				</section>
			</div>

			<footer class="page-footer">
				{include file='includes/site_schema_information.tpl'}

				{Core::_GetLegalFooterContent()}
			</footer>
		</div>
	</body>
</html>
