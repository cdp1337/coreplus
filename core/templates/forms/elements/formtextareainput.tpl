<div class="{$element->getClass()}">
	<div class="formelement-labelinputgroup">
		{if $element->get('title')}
			<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
		{/if}

		<textarea{$element->getInputAttributes()}>{$element->get('value')}</textarea>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>

	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>