{*css src="assets/css/admin/config.css"}{/css*}

{if $config_count}
	{script library="jquery"}{/script}

	<p class="message-tutorial">
		The system config is a low-level utility for managing any and all configuration options of your site.
		If there is a component-provided utility available, it is recommended to use that, as you can break your site
		if you improperly configure this page.
		<br/><br/>You've been warned, tread with caution ;)
	</p>

	<div id="system-config-form">
		{$form->set('orientation', 'grid')}
		{$form->render()}
	</div>
{else}
	<p class="message-info">
		There are no configurable options for your site.
	</p>
{/if}