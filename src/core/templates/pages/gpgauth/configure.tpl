{if $current_key}
	<p class="message-success">
		You have the GPG key {$current_key} currently enabled.  Use this page to change it.
	</p>
{/if}
<p class="message-tutorial">
	If you do not already have GPG setup on your local computer, please @todo INSTRUCTIONS HERE<br/><br/>
	You can automatically upload your public key by executing the following commands:
</p>
<pre>{$cmd}</pre>
<hr/>
<p class="message-tutorial">
	Alternatively, you can upload your public key manually by pasting it in below and clicking submit.
</p>
{$form->render()}