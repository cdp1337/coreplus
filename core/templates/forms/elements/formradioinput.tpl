<div class="{$element->getClass()}">
	{if $element->get('title')}
		<span>{$element->get('title')|escape}</span>
	{/if}
	
	{foreach from=$element->get('options') item=title key=key}
		<label>
			<input type="radio" {$element->getInputAttributes()} value="{$key}" {if $key == $element->getChecked()}checked{/if}/>
			{$title|escape}
		</label>
	{/foreach}
	
	{if $element->get('description')}
		<p class="FormDescription">{$element->get('description')}</p>
	{/if}
</div>