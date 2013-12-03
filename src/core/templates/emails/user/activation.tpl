<h1>Registration to {$sitename} Approved!</h1>

<p>
	Your account on {$sitename} has been approved by an administrator.
	{if $user->get('password')}
		To login, visit <a href="{$loginurl}">the login page</a>
		and enter your email and password.
	{else}
		To login, visit <a href="{$setpasswordlink}">{$setpasswordlink}</a> (expires in about a week).
		<br/><br/>
		If you do not set a password within a week, <a href="{$loginurl}">simply login</a> using this email address
		and leave the password field blank and you will receive another
		email with a new set password link.
	{/if}
</p>