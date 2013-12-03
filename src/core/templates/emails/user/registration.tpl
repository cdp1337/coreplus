<h1>Welcome to {$sitename}!</h1>

{if $user->get('active')}
	<p>
		You now have an account on {$sitename}.
		{if $user->get('password')}
			To login, visit <a href="{$loginurl}">the login page</a>
			and enter your email and password.
		{else}
			To login, visit <a href="{$loginurl}">the login page</a>
			and enter your email.  After doing so, you will be able to set a password.
		{/if}
	</p>
{else}
	<p>
		Your account is pending activation on {$sitename}.  Once activated, you will receive another email
		and will be able to login with your email and password.
	</p>
{/if}
