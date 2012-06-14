{*
 * This template and the widget that uses it is deprecated as of 2012.06.13.
 * Please use widgets/user/login.tpl instead.
 *}

<div class="userloginwidget">
	{if $loggedin}
		Welcome {$user->getDisplayName()}!  {a href="/User/Logout"}Logout{/a}?
	{/if}
	{if !$loggedin}
		Welcome {$user->getDisplayName()}!  
		Please {a href="/User/Login"}Login{/a}
		{if $allowregister}
			or {a href="/User/Register"}Register{/a}
		{/if}
	{/if}
</div>