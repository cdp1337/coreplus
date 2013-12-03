<div class="userloginwidget">
	{if $loggedin}
		Welcome
		{a href="/user/me"}
			{$user->getDisplayName()}
		{/a}
		!
		{a href="/User/Logout"}Logout{/a}?
	{else}
		Welcome {$user->getDisplayName()}!
		Please {a href="/User/Login"}Login{/a}
		{if $allowregister}
			or {a href="/User/Register"}Register{/a}
		{/if}
	{/if}
</div>