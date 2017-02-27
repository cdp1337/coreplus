<div class="{$element->getClass()} {$element->get('id')}">
	<label for="{$element->get('name')}" class="form-element-label">
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
		<select {$element->getInputAttributes()}>
			{foreach from=$element->get('options') item=title key=key}
				{if is_array($title)}
					<optgroup label="{$key}">
						{foreach $title as $subkey => $subtitle}
							<option value="{$subkey}" {if Core::CompareValues($subkey, $element->get('value'))}selected{/if}>{$subtitle}</option>
						{/foreach}
					</optgroup>
				{else}
					<option value="{$key}" {if Core::CompareValues($key, $element->get('value'))}selected{/if}>{$title}</option>
				{/if}
			{/foreach}
		</select>
	</div>
</div>

{if $element->get('readonly')}
	{* select options do not support readonly, but no reason why core plus can't ;) *}
	{script library="jqueryui.readonly"}{/script}
	{script location="foot"}<script>
		$('.{$element->get("id")}').readonly();
	</script>{/script}
{/if}
