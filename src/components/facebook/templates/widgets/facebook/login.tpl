{script library="facebook"}{/script}
{css src="assets/css/facebook.css"}{/css}

{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}


<div id="fb-root"></div>
<fieldset>
	<legend> Connect from Facebook</legend>

	<div id="facebook-connecting-section" style="display:none;"></div>
	<a href="{$facebooklink}" id="facebook-login-button" class="button">
		Login with Facebook
	</a>

	<form action="{link link='/facebook/login'}" method="POST" id="facebook-login-form">
		<input type="hidden" name="login-method" value="facebook"/>
		<input type="hidden" name="access-token"/>
	</form>

</fieldset>

