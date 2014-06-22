{script library="facebook"}{/script}
{css src="assets/css/facebook.css"}{/css}

{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script src="assets/js/facebook/login.js"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}


<div id="fb-root"></div>
<fieldset>
	<legend> Connect from Facebook</legend>

	<div id="facebook-connecting-section" style="display:none;"></div>
	<a href="#" scope="email" style="display:none" id="facebook-login-button">
		Login with Facebook
	</a>

	<noscript>
		<a href="{$facebooklink}">Login with Facebook</a>
	</noscript>

	<form action="{link link='/facebook/login'}" method="POST" id="facebook-login-form">
		<input type="hidden" name="login-method" value="facebook"/>
		<input type="hidden" name="access-token"/>
	</form>

</fieldset>

