{if $current_key}
	<p class="message-info">
		Your current GPG key is {$current_key}.
		Below is a list of any other key that was located that is currently attached to your email.
	</p>
{/if}

<p class="message-tutorial">
	Select your GPG key and submit the form to have instructions sent to your email.
	<br/><br/>
	If your key does not appear, simply reload the page once or twice.  Keys are pulled from remote servers and may not be immediately available.
</p>

{$form->render()}