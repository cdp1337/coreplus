<div class="{$element->getClass()}">
	{if $element->get('title')}
		<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
	{/if}
	<select {$element->getInputAttributes()}>
		{foreach from=$element->get('options') item=title key=key}
			<option value="{$key}" {if $key == $element->get('value')}selected{/if}>{$title}</option>
		{/foreach}
	</select>
	{if $element->get('description')}
		<p class="FormDescription">{$element->get('description')}</p>
	{/if}
</div>