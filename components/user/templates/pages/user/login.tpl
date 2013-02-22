<fieldset id="user-login">
	<legend> Login</legend>
	{$form->render()}
	<br/>
	{a href="/User/ForgotPassword"}Forgot Password{/a}
	{if $allowregister}
		<br/><br/>
		{a href="/User/Register"}Register Account{/a}
	{/if}
</fieldset>


{if $smarty.const.FACEBOOK_APP_ID && in_array('facebook', $backends)}
	<p>OR</p>
	{widget baseurl="/facebook/login"}
{/if}

{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}
