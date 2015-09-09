<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
	<head>
		<!-- Force latest IE rendering engine or ChromeFrame if installed -->
		<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
		{css src="css/custom.css" inline="1"}{/css}
		{css src="css/custom_print.css" inline="1"}{/css}
		<!--[if lt IE 9]>
			<script type="text/javascript" src="{asset src='js/html5.js'}"></script>
			<script type="text/javascript" src="{asset src='js/json2.js'}"></script>
		<![endif]-->

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>{$title}</title>
	</head>

	<body class="core-skin skin-print {$body_classes}">

		{$body}
		<footer>
			<p class="legal-notice">
				Licensed under the
				<a href="https://www.gnu.org/licenses/agpl" target="_blank" title="Licensed Under AGPLv3" class="agplv3-tag">AGPLv3</a>.

				&nbsp;&nbsp;

				Powered by the <a href="http://corepl.us" target="_blank">Secure PHP Framework and CMS, Core Plus</a>.
			</p>
		</footer>
	</body>

</html>
