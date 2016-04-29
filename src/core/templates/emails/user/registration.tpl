<h1>Welcome to {$sitename}!</h1>

{if $user.active}
	<p>
		You now have an account on {$sitename} and can login by visiting <a href="{$loginurl}">the login page</a>.
	</p>

	<p>
		{if $user.password_raw}
			{* Remember, this is the generated password. *}
			A password has been generated automatically for you.
			Please use the following password to login, and you will be able to change it once logged in.
			<br/><br/>
			Password: {$user.password_raw}
		{elseif $user.password}
			Please use your password already set to login.
			For security reasons, your password will never be sent via email, but you will be able to reset it should you need to.
		{/if}
	</p>
{else}
	<p>
		Your account is pending activation on {$sitename}.
		You will receive another email once your account is activated.
	</p>
{/if}
