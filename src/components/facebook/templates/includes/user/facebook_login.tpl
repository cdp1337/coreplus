{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script location="foot" src="assets/js/user/login.js"}{/script}
{/if}

{script library="facebook"}{/script}
{css src="assets/css/facebook.css"}{/css}


<div class="user-login">
	<div id="fb-root"></div>
	<a href="{$facebooklink}" id="facebook-login-button" class="button">
		Login with Facebook
	</a>

	<form action="{link link='/facebook/login'}" method="POST" id="facebook-login-form">
		<input type="hidden" name="redirect"/>
		<input type="hidden" name="login-method" value="facebook"/>
		<input type="hidden" name="access-token"/>
	</form>
</div>