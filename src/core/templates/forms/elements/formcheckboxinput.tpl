<div class="{$element->getClass()} {$element->get('id')}">

	<label for="{$element->get('id')}" class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	<div class="form-element-value">
		<input type="checkbox" {$element->getInputAttributes()}>
	</div>

	<p class="form-element-description">{$element->get('description')}</p>

</div>