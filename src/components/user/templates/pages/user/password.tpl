{css src="assets/css/user.css"}{/css}

{if $complexity.enabled}
	<p class="message-tutorial">
		Please ensure that the new password meets the following complexity requirements:<br/><br/>
		{"<br/>"|implode:$complexity.messages}
	</p>
{/if}

{$form->render()}