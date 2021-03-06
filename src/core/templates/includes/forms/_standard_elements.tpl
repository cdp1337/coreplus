{**
 * The main template for most form elements.  This was copied to the default theme because the description is moved to
 * after the input in this theme.
 *}

<div class="{$element->getClass()} {$element->get('id')}">

	<label for="{$element->get('id')}" class="form-element-label">
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
		<input type="{$type}"{$element->getInputAttributes()}>
	</div>
</div>