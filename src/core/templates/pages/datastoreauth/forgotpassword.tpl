{if $step == 1}
	<p class="message-tutorial">
		Please enter your email address that is registered.
		You will be sent an email containing a link to set or reset your password.
	</p>
	{$form->render()}
{/if}
{if $step == 2}
	<p class="message-tutorial">
		Please enter a new secure password, (and confirm it).
		{if $requirements}
			<br/><br/>
			Also be aware that it must been the following requirements:<br/>
			{$requirements}
		{/if}
	</p>
	{$form->render()}
{/if}