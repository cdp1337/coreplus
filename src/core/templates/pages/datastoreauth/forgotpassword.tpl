{if $step == 1}
	{if $current}
		<p class="message-tutorial">
			Instructions will be sent to {$current} when you click on the button below.
			{if $can_change_email}
				If this email address is incorrect, please set your correct email first via your edit account link.
			{else}
				If this email address is incorrect, please contact a site admin to have your email corrected first.
			{/if}
		</p>
	{else}
		<p class="message-tutorial">
			Please enter your email address that is registered.
			You will be sent an email containing a link to set or reset your password.
		</p>
	{/if}
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