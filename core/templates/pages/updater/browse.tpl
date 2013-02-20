{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script src="js/updater/index.js"}{/script}
{css src="css/updater.css"}{/css}

<p>
	{if $sitecount == 1}There is {$sitecount} repository{/if}
	{if $sitecount > 1}There are {$sitecount} repositories{/if}
	currently available.  {a href='updater/repos'}Manage Them{/a}
</p>

<p>
	<span id="updates"></span>
</p>

<!-- This will get populated with the update progress for installs and updates. -->
<div id="update-terminal" style="display:none;"></div>

<!-- This will get cloned by javascript into the link when checking. -->
<span id="loading-replacement-text" style="display:none;">
	Checking Upgrade/Install
	{img src="assets/images/loading-bar-small.gif"}
</span>

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