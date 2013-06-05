{css}<style>
#system-config-form .formelement:before,
#system-config-form .formelement:after
{
	content: " "; /* 1 */
	display: table; /* 2 */
}

#system-config-form .formelement:after {
	clear: both;
}

#system-config-form .formelement .formelement-labelinputgroup {
	border-radius: 0;
}
#system-config-form label {
	padding: 1px;
	width: 140px;
	overflow: hidden;
}
#system-config-form input,
#system-config-form select
{
	padding: 1px;
	min-width: 200px;
}
#system-config-form .formelement .formdescription {
	float: left;
	font-size: 75%;
	margin-left: 5px;
	max-width: 425px;
	padding: 0 0 5px;
}
#system-config-form .formelement > .clear {
	clear: none;
}
</style>{/css}

{if $config_count}
	{script library="jquery"}{/script}

	<p class="message-tutorial">
		The system config is a low-level utility for managing any and all configuration options of your site.
		If there is a component-provided utility available, it is recommended to use that, as you can break your site
		if you improperly configure this page.
		<br/><br/>You've been warned, tread with caution ;)
	</p>

	<div id="system-config-form">
		{$form->render()}
	</div>
{else}
	<p class="message-info">
		There are no configurable options for your site.
	</p>
{/if}