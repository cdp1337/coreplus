<div class="{$element->getClass()} {$element->get('id')}">
	{if $element->get('title')}
		<label for="{$element->get('id')}">{$element->get('title')|escape}</label>
	{/if}

	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}

	<input type="{$type}"{$element->getInputAttributes()}>

</div>