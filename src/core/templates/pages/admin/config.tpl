{if $config_count}
	{script library="jquery"}{/script}
	{script src="assets/js/admin/config.js"}{/script}

	{$form->render()}
{else}
	<p class="message-info">
		There are no configurable options for your site.
	</p>
{/if}