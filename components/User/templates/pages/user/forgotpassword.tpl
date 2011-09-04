{if isset($error)}
	<p class="message-error">{$error}</p>
{/if}

<p>Forget your password?</p>
<p>Enter your email to be sent a link which will allow you to reset the password.</p>
<form action="" method="POST">
	<input type="text" name="email"/>
	<input type="submit" value="Send Link"/>
</form>