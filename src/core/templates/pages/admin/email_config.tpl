<div id="tabs-group">
	<ul>
		<li>
			<a href="#email-config-group" class="formtabsgroup-tab-link"><span>{t 'STRING_CONFIGURATION'}</span></a>
		</li>
		<li>
			<a href="#email-test-group" class="formtabsgroup-tab-link"><span>{t 'STRING_TEST'}</span></a>
		</li>
	</ul>

	<div id="email-config-group">
		{$form->render()}
	</div>

	<div id="email-test-group">
		{if $email_enabled}
			<p class="message-tutorial">
				{t 'MESSAGE_PLEASE_SAVE_EMAIL_CONFIGURATION_TUTORIAL_TO_TEST'}
			</p>

			<form action="{link '/admin/email/test'}" method="post" target="test-log" id="test-form">
				<input type="text" placeholder="{t 'STRING_EMAIL_DESTINATION'}" name="email"/>
				<input type="submit" value="{t 'STRING_TEST'}"/>
			</form>
			<br/>

			{progress_log_iframe name="test-log" form="test-form"}
		{else}
			<p class="message-error">
				{t 'MESSAGE_EMAIL_SENDING_DISABLED_TEST_NOT_POSSIBLE'}
			</p>
		{/if}
	</div>
</div>

{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$(function(){
		$('#tabs-group').tabs();
	});
</script>{/script}

