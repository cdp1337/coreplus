{css src="assets/css/user.css"}{/css}

<p class="message-info">
	Please login {if $registerform}or create an account {/if} to view this page.
</p>

<div class="user-guest-403-container {if $registerform}two-columns{/if}">
	<div class="column-left">
		<fieldset id="user-login">
			<legend> Login </legend>
			{$loginform->render()}
			<br/>
			{a href="/user/forgotpassword"}Forget Password?{/a}
		</fieldset>

		{if $smarty.const.FACEBOOK_APP_ID && in_array('facebook', $backends)}
			<p>OR</p>
			{widget baseurl="/facebook/login"}
		{/if}
	</div>

	{if $registerform}
		<div class="column-right">
			<fieldset id="user-register">
				<legend> Create Account </legend>
				{$registerform->render()}
			</fieldset>
		</div>
	{/if}
</div>



{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}