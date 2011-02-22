<div class="{$element->getClass()}">
	{if $element->get('title')}
		<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
	{/if}
	<img src="{link href='/SimpleCaptcha'}"/><br/>
	<input type="text"{$element->getInputAttributes()}>
	{if $element->get('description')}
		<p class="FormDescription">{$element->get('description')}</p>
	{/if}
</div>