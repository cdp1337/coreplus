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