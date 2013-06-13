<h1>Registration to {$sitename} Approved!</h1>

<p>
	Your account on {$sitename} has been approved by an administrator.
	{if $user->get('password')}
		To login, visit <a href="{$loginurl}">the login page</a>
		and enter your email and password.
	{else}
		To login, visit <a href="{$loginurl}">the login page</a>
		and enter your email.  After doing so, you will be able to set a password.
	{/if}
</p>