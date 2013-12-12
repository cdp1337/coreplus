{css src="assets/css/user.css"}{/css}


<div id="register-center" class="user-register">

	{**
	 * An alternative to this if you so please is to do:
	 *
	 * {$drivers.datastore->renderRegister()}
	 * <some-markup/>
	 * {$drivers.facebook->renderRegister()}
	 *
	 * The default will simply render every authentication driver enabled on the system.
	 *}

	{foreach $drivers as $name => $d}
		<div class="user-register-include user-authdriver-{$name}">
			{$d->renderRegister()}
		</div>
	{/foreach}

	<div class="clear"></div>

	{a class="login-account" href="/user/login"}Already have an account?{/a}

</div>
