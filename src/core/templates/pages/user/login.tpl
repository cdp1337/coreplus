{css src="assets/css/user.css"}{/css}


<div id="login-center" class="user-login">

	<fieldset id="login-existing" class="user-login-existing">
		<em>Login to your existing account.</em>
	</fieldset>

	{**
	 * An alternative to this if you so please is to do:
	 *
	 * {$drivers.datastore->renderLogin()}
	 * <some-markup/>
	 * {$drivers.facebook->renderLogin()}
	 *
	 * The default will simply render every authentication driver enabled on the system.
	 *}

	{foreach $drivers as $name => $d}
		<div class="user-login-include user-authdriver-{$name}">
			{$d->renderLogin()}
		</div>
	{/foreach}


	<div class="clear"></div>

	{if $allowregister}
		<fieldset id="user-login" class="user-login-register">
			{a class="register-account button" href="/user/register"}Register Account{/a}

			<em>Like this site? Sign up for an account!</em>
		</fieldset>
	{/if}

</div>
