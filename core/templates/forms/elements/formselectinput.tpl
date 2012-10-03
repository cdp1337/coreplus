<div class="{$element->getClass()} {$element->get('id')}">
	{if $element->get('title')}
		<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
	{/if}
	
	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
	
	<select {$element->getInputAttributes()}>
		{foreach from=$element->get('options') item=title key=key}
			<option value="{$key}" {if $key == $element->get('value')}selected{/if}>{$title}</option>
		{/foreach}
	</select>
</div>