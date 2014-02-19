<div class="user-loginfull-widget">
	{if $user}
		<h2>WELCOME {$user->getDisplayName()}</h2>
		{a href="/user/me"}
			{img src="`$user.avatar`" placeholder="person" width="100" height="200"}
		{/a}<br/>

		{a href="/User/Logout"}Logout{/a}
	{else}
		{**
		 * An alternative to this if you so please is to do:
		 *
		 * {$drivers.datastore->renderLogin()}
		 * <some-markup/>
		 * {$drivers.facebook->renderLogin()}
		 *
		 * The default will simply render every authentication driver enabled on the system.
		 *}

		<h2>SIGN IN</h2>

		{foreach $drivers as $name => $d}
			<div class="user-login-include user-authdriver-{$name}">
				{$d->renderLogin()}
			</div>
		{/foreach}


		<div class="clear"></div>

		{if $allowregister}
			{a class="register-account" href="/user/register"}Register Account{/a}
		{/if}
	{/if}

</div>
