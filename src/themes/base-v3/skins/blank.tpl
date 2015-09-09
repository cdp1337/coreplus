<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		{css src="css/styles.css" inline="1"}{/css}
		{css src="css/custom.css" inline="1"}{/css}
		{css src="css/custom_print.css" inline="1" media="print"}{/css}
		<!--[if lt IE 9]>
		<script type="text/javascript" src="{asset src='js/html5.js'}"></script>
		<script type="text/javascript" src="{asset src='js/json2.js'}"></script>
		<![endif]-->
		{script library="fontawesome"}{/script}

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		{if $seotitle}<title>{$seotitle}</title>{/if}
	</head>

	<body class="base-v3-skin skin-blank{if $body_classes} {$body_classes}{/if}">
		<div class="outer-wrapper" id="outer-wrapper">
			<div id="inner-wrapper" class="page-column-width-0">
				<section class="page-content">
					{$body}
				</section>
			</div>
		</div>
	</body>
</html>
