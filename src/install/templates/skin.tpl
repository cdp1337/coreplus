<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link rel="stylesheet" href="assets/style.css"/>
		<script src="assets/jquery-1.7.1.min.js"></script>
		
		%head%
		
		<title>%title%</title>
	</head>

	<body>
		<div id="wrapper">
			<header>%title%</header>
			
			{if("%error%" != "")}
				<p class="message-error">
					%error%
				</p>
			{/if}
			<section>%body%</section>
			
			<footer></footer>
		</div>
	</body>

</html>
