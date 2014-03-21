{script library="jquery"}{/script}
{script library="facebook"}{/script}
{script src="assets/js/facebook/login.js"}{/script}
{css src="assets/css/facebook.css"}{/css}


<div id="fb-root"></div>
<fieldset>
	<legend> Connect from Facebook</legend>

	<div id="facebook-connecting-section" style="display:none;"></div>
	<a href="#" scope="email" style="display:none" id="facebook-login-button">
		Enable Facebook
	</a>

	<noscript>
		<a href="{$facebooklink}">Enable Facebook</a>
	</noscript>

	<form action="{link link='/facebook/enable'}" method="POST" id="facebook-login-form">
		<input type="hidden" name="login-method" value="facebook"/>
		<input type="hidden" name="access-token"/>
	</form>

</fieldset>