<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<style>
			/* Do some useful style defines here... */
			body { text-align:center; padding:0pt; margin:0pt; background:#394C3F; text-shadow:1px 1px 1px rgba(28, 43, 13, 0.1); font-size:11pt; }
			a { color:#1C2B0D; }
			a:hover { text-shadow:1px 1px 0px rgba(28, 43, 13, 0.9);}
			pre { background:black; border:1px solid #3F3; padding:1em 2em; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; color:#3F3; font-size:10pt; font-family:monospace; text-align:left;}
			#wrapper { width:75%; margin:0pt auto; text-align:left; background:#1C2B0d; box-shadow:0px 1px 4px black; -moz-box-shadow:0px 1px 4px black; -webkit-box-shadow:0px 1px 4px black; }
			#innerwrapper { background:#EDEDED; padding:1em 1em 2em; margin:0pt 2px; }
			#header, #footer { height:20px; }
			.message-note { background:#FCF0AD; border:1px solid #BCB06D; padding:1em 2em; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; }
			.message-error { background:#FCA0AD; border:1px solid #BC606D; padding:1em 2em; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; }
			.rounded-large { border-radius:10px; -moz-border-radius:10px; -webkit-border-radius:10px; }
			.rounded { border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; }
		</style>
		%head%
		<title>%title%</title>
	</head>

	<body>
		<div id="wrapper">
			<div id="header"></div>
			<div id="innerwrapper" class="rounded-large">
				{if("%error%" != "")}
					<p class="message-error">
						%error%
					</p>
				{/if}
				%body%
			</div>
			<div id="footer"></div>
		</div>
	</body>

</html>
