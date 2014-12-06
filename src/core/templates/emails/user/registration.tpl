<h1>Welcome to {$sitename}!</h1>

{if $user->get('active')}
	<p>
		You now have an account on {$sitename} and can login by visiting <a href="{$loginurl}">the login page</a>.
	</p>

	<p>
		{if $password}
			{* Remember, this is the generated password. *}
			A password has been generated automatically for you.
			Please use the following password to login, and you will be able to change it once logged in.
			<br/><br/>
			Password: {$password}
		{elseif $user->get('password')}
			Please use your password already set to login.
			For security reasons, your password will never be sent via email, but you will be able to reset it should you need to.
		{else}
			Please visit the login page and enter your email.
			Upon doing so and following the instructions, you will be able to set a password.
		{/if}
	</p>
{else}
	<p>
		Your account is pending activation on {$sitename}.  Once activated, you will receive another email
		and will be able to login with your email and password.
	</p>
{/if}
