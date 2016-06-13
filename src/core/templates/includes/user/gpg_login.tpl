{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}

<div class="user-login">
	{$form->render('head')}
	{$form->render('body')}
	<button>
		<i class="icon icon-lock"></i>
		<span>Sign in with GPG</span>
	</button>

	{a href="/gpgauth/reset"}
		Set/Reset GPG Key
	{/a}

	{$form->render('foot')}
</div>