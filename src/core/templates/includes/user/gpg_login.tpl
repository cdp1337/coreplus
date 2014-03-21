{$form->render('head')}
{$form->render('body')}
<button>
	<i class="icon-lock"></i>
	<span>Sign in with GPG</span>
</button>

{a href="/gpgauth/reset"}
	Set/Reset GPG Key
{/a}

{$form->render('foot')}
