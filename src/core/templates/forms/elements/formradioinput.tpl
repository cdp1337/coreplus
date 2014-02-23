<div class="{$element->getClass()} {$element->get('id')}" id="{$element->get('id')}">
	<span class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</span>

	<div class="form-element-value clearfix">
		{foreach from=$element->get('options') item=title key=key}
			<label>
				<input type="radio" {$element->getInputAttributes()} value="{$key}" {if $key == $element->getChecked()}checked{/if}/>
				{$title|escape}
			</label>
		{/foreach}
	</div>

	<p class="form-element-description">{$element->get('description')}</p>
</div>