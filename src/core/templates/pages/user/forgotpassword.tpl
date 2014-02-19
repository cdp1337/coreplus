{if $step == 1}
	<p class="message-tutorial">
		Please enter your email address that is registered.
		You will be sent an email containing a link to reset your password.
	</p>
	{$form->render()}
{/if}
{if $step == 2}
	<p class="message-tutorial">
		Please enter a new password, (and confirm it).
	</p>
	{$form->render()}
{/if}