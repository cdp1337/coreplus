<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		{css src="css/print.css" inline="1"}{/css}
		{css src="css/custom.css" inline="1"}{/css}
		{css src="css/custom_print.css" inline="1"}{/css}

		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	</head>

	<body class="core-2017-emailskin skin-email skin-print{if isset($body_classes) && $body_classes} {$body_classes}{/if}">
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
