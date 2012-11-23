<div class="{$element->getClass()}">
	{if $element->get('title')}
		<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
	{/if}

	<textarea{$element->getInputAttributes()}>{$element->get('value')}</textarea>

	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>