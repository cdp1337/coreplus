{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script location="foot" src="assets/js/user/login.js"}{/script}
{/if}

{script library="facebook"}{/script}
{script src="assets/js/facebook/login.js"}{/script}
{css src="assets/css/facebook.css"}{/css}


<div class="user-login">
	<div id="fb-root"></div>
	<div id="facebook-connecting-section" style="display:none;"></div>
	<a href="#" scope="email" style="display:none" id="facebook-login-button">
		<i class="icon-facebook"></i> Login with Facebook
	</a>

	<noscript>
		<a href="{$facebooklink}">Login with Facebook</a>
	</noscript>

	<form action="{link link='/facebook/login'}" method="POST" id="facebook-login-form">
		<input type="hidden" name="redirect"/>
		<input type="hidden" name="login-method" value="facebook"/>
		<input type="hidden" name="access-token"/>
	</form>
</div>