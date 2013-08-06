<div class="{$element->getClass()}">
	{if $element->get('title')}
		<span class="radio-label">{$element->get('title')|escape}</span>
	{/if}

	<div class="formradioinput-options">
		{foreach from=$element->get('options') item=title key=key}
			<label>
				<input type="radio" {$element->getInputAttributes()} value="{$key}" {if $key == $element->getChecked()}checked{/if}/>
				{$title|escape}
			</label>
		{/foreach}
	</div>

	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>