{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script src="js/updater/index.js"}{/script}
{css src="css/updater.css"}{/css}

{if $sitecount == 0}
	<p class="message-warning">
		{t 'MESSAGE_WARNING_UPDATER_NO_UPDATE_SITES'}
		{a href='updater/repos/add' class="button"}
			{t 'STRING_ADD_REPOSITORY_SITE'}
		{/a}
	</p>
{else}
	<p>
		{t 'MESSAGE_THERE_ARE_N_REPOSITORIES_AVAILABLE' $sitecount}
		{a href='updater/repos'}{t 'STRING_MANAGE_REPOSITORIES'}{/a}
	</p>

	<p>
		<span id="updates"></span>
	</p>

	{script location="foot"}<script>
		$(function(){ Updater.PerformCheck($('#updates')); });
	</script>{/script}
{/if}

<div id="update-everything-wrapper" style="display:none;">
	<form action="{link '/updater/update_everything'}" method="POST" id="update-everything-form" target="update-everything">
		<input type="submit" value="Update Everything"/>
	</form>

	{progress_log_iframe name='update-everything' form='update-everything-form'}
</div>

<!-- This will get populated with the update progress for installs and updates. -->
<div id="update-terminal" style="display:none;"></div>

<!-- This will get cloned by javascript into the link when checking. -->
<span id="loading-replacement-text" style="display:none;">
	Checking Upgrade/Install
	{img src="assets/images/loading-bar-small.gif"}
</span>

<table class="listing" id="core-list">
	<tr data-type="core">
		<td><img src="{$core->getLogo()->getPreviewURL('64x64')}"/></td>
		<td>Core {$core->getVersion()}</td>
		<td>
			<a href="#" class="update-link perform-update" style="display:none;">Update</a>
		</td>
	</tr>
</table>
<br/>

<table class="listing" id="component-list">
	<tr>
		<th>Component</th>
		<th>Version</th>
		<th>Enabled</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$components item=c}
		{if $c->isEnabled() && !$c->isReady()}
			<tr data-name="{$c->getKeyName()}" data-type="components">
				<td colspan="4">
					<p class="message-error">
						Errors with {$c->getName()} {$c->getVersion()}
						&nbsp;&nbsp;<a href="#" class="disable-link">Disable Component</a>
						<a href="#" class="update-link perform-update" style="display:none;">Update</a>
					</p>
					{$c->getErrors()}
				</td>
			</tr>
		{else}
			<tr data-name="{$c->getKeyName()}" data-type="components">
				<td>
					{if $c->getLogo()}
						<img src="{$c->getLogo()->getPreviewURL('64x64')}"/>
					{/if}
					{$c->getName()}
				</td>
				<td>{$c->getVersion()}</td>
				<td>
					{if $c->isEnabled()}
						<i title="Yes" style="color:green;" class="icon icon-ok"></i>
					{else}
						<i title="No" style="color:red;" class="icon icon-remove"></i>
					{/if}
				</td>
				<td>
					{if $c->isEnabled()}
						<a href="#" class="disable-link">Disable</a>
						<a href="#" class="update-link perform-update" style="display:none;">Update</a>
					{else}
						{if $c->isInstalled()}
							<a href="#" class="enable-link">Enable</a>
						{else}
							<a href="#" class="perform-update" data-type="components" data-name="{$c->getKeyName()}" data-version="{$c->getVersion()}">Install</a>
						{/if}
					{/if}
				</td>
			</tr>
		{/if}

	{/foreach}
</table>
<br/>

{* Themes < 2.1.0 do not support keynames. *}

{if Core::IsComponentAvailable('theme') && version_compare(Core::GetComponent('theme')->getVersion(), 2.1)}
	<table class="listing" id="theme-list">
		<tr>
			<th>Theme</th>
			<th>Version</th>
			<th>&nbsp;</th>
		</tr>
		{foreach from=$themes item=t}
			<tr data-name="{$t->getKeyName()}" data-type="themes">
				<td>{$t->getName()}</td>
				<td>{$t->getVersion()}</td>
				<td>
					<a href="#" class="update-link perform-update" style="display:none;">Update</a>
				</td>
			</tr>
		{/foreach}
	</table>
{else}
	<p class="message-error">
		Either the "Theme" component is not installed or it is too old.  Please update it to at least 2.1 to get access to manage theme updates.
	</p>
{/if}