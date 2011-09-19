<fieldset>
	<legend> Login </legend>
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
	<fieldset>
		<legend> Connect from Facebook </legend>
		
		<div id="facebook-connecting-section" style="display:none;"></div>
		<fb:login-button scope="email" style="display:none" id="facebook-login-button">
			Login with Facebook
		</fb:login-button>
		
		<noscript>
			<a href="{$facebooklink}">Login with Facebook</a>
		</noscript>
		
		<form action="{link link='/User/Login'}" method="POST" id="facebook-login-form">
			<input type="hidden" name="login-method" value="facebook"/>
			<input type="hidden" name="access-token"/>
		</form>
		
	</fieldset>
	
	{script library="jquery"}{/script}
	{script library="facebook"}{/script}
	{script src="js/user/login.js"}{/script}
{/if}