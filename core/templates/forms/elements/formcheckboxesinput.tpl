<div class="{$element->getClass()}">
	{if $element->get('title')}
		<span>{$element->get('title')|escape}</span>
	{/if}
	
	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
	
	{foreach from=$element->get('options') item=title key=key}
		<label>
			<input type="checkbox" {$element->getInputAttributes()} value="{$key}" {if in_array($key, $element->get('value'))}checked="checked"{/if}/>
			{$title|escape}
		</label>
	{/foreach}
</div>