<div class="userloginwidget">
	{if $loggedin}
		{a href="/user/me"}{t 'STRING_WELCOME' modifier='ucfirst'} {$user->getDisplayName()}{/a}!  {a href="/user/logout"}{t 'STRING_LOGOUT'}{/a}?
	{else}
		{a href="/user/login" assign="login_text"}{t 'STRING_LOGIN'}{/a}
		{a href="/user/register" assign="register_text"}{t 'STRING_REGISTER'}{/a}

		{t 'STRING_WELCOME' modifier='ucfirst'} {$user->getDisplayName()}!
		{if $allowregister}
			{t 'MESSAGE_PLEASE_S_OR_S' "`$login_text`" "`$register_text`"}
		{else}
			{t 'MESSAGE_PLEASE_S' "`$login_text`"}
		{/if}
	{/if}
</div>