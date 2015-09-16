<div id="tabs-group">
	<ul>
		<li>
			<a href="#email-config-group" class="formtabsgroup-tab-link"><span>Configuration</span></a>
		</li>
		<li>
			<a href="#email-test-group" class="formtabsgroup-tab-link"><span>Test</span></a>
		</li>
	</ul>

	<div id="email-config-group">
		{$form->render()}
	</div>

	<div id="email-test-group">
		{if $email_enabled}
			<p class="message-tutorial">
				Please save any configuration on the other tab first.
				This section will allow you to test the configuration by sending a live email.
				<br/><br/>
				Simply enter an email address to send a test to and click Test!
			</p>

			<form action="{link '/admin/email/test'}" method="post" target="test-log" id="test-form">
				<input type="text" placeholder="Email Address (Destination)" name="email"/>
				<input type="submit" value="Test!"/>
			</form>
			<br/>

			{progress_log_iframe name="test-log" form="test-form"}
		{else}
			<p class="message-error">
				Email sending is currently disabled!  No testing is possible.
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

