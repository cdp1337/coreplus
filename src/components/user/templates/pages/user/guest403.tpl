{css src="assets/css/user.css"}{/css}

<p class="message-info">
	Please login {if $registerform}or create an account {/if} to view this page.
</p>

<!--
	As you may have guessed by looking at the id...
	keep this line in here else your javascript login WILL BREAK!
-->
<div id="user-login-placeholder-for-javascript-because-otherpages-may-have-an-error"></div>

<div id="login-center">

	<fieldset id="login-existing">
		<em>Login to your existing account.</em>
	</fieldset>

	<fieldset class="left">
		{$loginform->render()}

		{a class="login-forgot" href="/User/ForgotPassword"}Forgot Password?{/a}
	</fieldset>

	{if $smarty.const.FACEBOOK_APP_ID && in_array('facebook', $backends)}
		<div id="login-divider"></div>

		<fieldset class="right">
			{widget baseurl="/facebook/login"}
		</fieldset>
	{/if}

	<div class="clear"></div>

	{if $allowregister}
		<fieldset id="user-login">
			{a class="register-account" href="/User/Register"}Register Account{/a}

			<em>Like this site? Sign up for an account!</em>
		</fieldset>
	{/if}
</div>

{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}