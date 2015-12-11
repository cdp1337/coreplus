<div class="user-login">
	{$form->render()}

	{a class="login-forgot" href="/datastoreauth/forgotpassword"}{t 'STRING_FORGOT_PASSWORD'}{/a}

	{if Core::IsLibraryAvailable('JQuery')}
		{script library="jquery"}{/script}
		{script library="jqueryui"}{/script}
		{script library="jquery.form"}{/script}
		{script location="foot" src="assets/js/user/login.js"}{/script}
	{/if}

	<!--
		As you may have guessed by looking at the id...
		keep this line in here else your javascript login WILL BREAK!
	-->
	<div id="user-login-placeholder-for-javascript-because-otherpages-may-have-an-error"></div>
</div>