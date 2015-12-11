<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		{css src="css/print.css" inline="1"}{/css}
		{css src="css/custom.css" inline="1"}{/css}
		{css src="css/custom_print.css" inline="1"}{/css}

		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	</head>

	<body class="base-v3-emailskin skin-email skin-print{if isset($body_classes) && $body_classes} {$body_classes}{/if}">
		<div class="outer-wrapper" id="outer-wrapper">
			<header class="page-header">
				{a href="`$smarty.const.ROOT_URL`" title="`$smarty.const.SITENAME|escape`"}
				{if $smarty.const.THEME_SITE_LOGO}
					{img src="`$smarty.const.THEME_SITE_LOGO`" alt="`$smarty.const.SITENAME|escape`" inline="1"}
				{else}
					{img src="assets/images/logo.png" alt="`$smarty.const.SITENAME|escape`" inline="1"}
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

				<p class="legal-notice">
					{t 'STRING_LICENSED_UNDER'}
					<a href="https://www.gnu.org/licenses/agpl" target="_blank" title="Licensed Under AGPLv3" class="agplv3-tag">AGPLv3</a>.
					&nbsp;&nbsp;
					{t 'STRING_POWERED_BY'} <a href="http://corepl.us" target="_blank">Secure PHP Framework and CMS, Core Plus</a>.
				</p>
			</footer>
		</div>
	</body>
</html>
