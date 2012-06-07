{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script src="js/updater/index.js"}{/script}

{if $sitecount == 0}
	<p class="message-error">
		There are no update repositories currently enabled.  Go {a href='updater/repos'}Manage Them{/a}!
	</p>
{else}
	<p>
		{if $sitecount == 1}There is {$sitecount} update repository{/if}
		{if $sitecount > 1}There are {$sitecount} update repositories{/if}
		currently enabled.  {a href='updater/repos'}Manage Them{/a}
	</p>

	<p>
		<span id="updates"></span>
	</p>

	<script>$(function(){ Updater.PerformCheck($('#updates')); });</script>
{/if}

<table class="listing" id="core-list">
	<tr type="core">
		<td>Core {$core->getVersion()}</td>
		<td>
			<a href="#" class="update-link" style="display:none;">Update</a>
		</td>
	</tr>
</table>
<br/>

<table class="listing" id="component-list">
	<tr>
		<th>Component</th>
		<th>Version</th>
		<th>Installed</th>
		<th>Enabled</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$components item=c}
		<tr componentname="{$c->getName()|lower}" type="components">
			<td>{$c->getName()}</td>
			<td>{$c->getVersion()}</td>
			<td>{if $c->isInstalled()}yes{else}---{/if}</td>
			<td>{if $c->isEnabled()}yes{else}---{/if}</td>
			<td>
				{if $c->isEnabled()}
					<a href="#" class="disable-link">Disable</a>
					<a href="#" class="update-link" style="display:none;">Update</a>
				{else}
					<a href="#" class="enable-link">Enable</a>
				{/if}
			</td>
		</tr>
	{/foreach}
</table>
<br/>

<table class="listing" id="theme-list">
	<tr>
		<th>Theme</th>
		<th>Version</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$themes item=t}
		<tr themename="{$t->getName()|lower}" type="themes">
			<td>{$t->getName()}</td>
			<td>{$t->getVersion()}</td>
			<td>
				<a href="#" class="update-link" style="display:none;">Update</a>
			</td>
		</tr>
	{/foreach}
</table>