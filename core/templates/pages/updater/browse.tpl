{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script src="js/updater/index.js"}{/script}

<p>
	<span id="updates"></span>
</p>

<table class="listing" id="component-list">
	<tr>
		<th>Component</th>
		<th>Version</th>
		<th>Status</th>
	</tr>
</table>
<br/>

<table class="listing" id="theme-list">
	<tr>
		<th>Theme</th>
		<th>Version</th>
		<th>Status</th>
	</tr>
</table>

<script> Updater.GetPackages(); </script>