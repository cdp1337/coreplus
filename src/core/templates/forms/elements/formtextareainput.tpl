<div class="{$element->getClass()}">
	<label class="form-element-label" for="{$element->get('name')}">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	{if $element->get('description')}
		{if strpos($element->get('description'), "\n")}
			<p class="form-element-description">{$element->get('description')}</p>
		{else}
			<span class="form-element-description">{$element->get('description')}</span>
		{/if}
	{/if}

	<div class="form-element-value">
		<textarea{$element->getInputAttributes()}>{$element->get('value')}</textarea>
	</div>
</div>