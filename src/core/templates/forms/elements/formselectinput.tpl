<div class="{$element->getClass()} {$element->get('id')}">
	<label for="{$element->get('name')}" class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	<div class="form-element-value">
		<select {$element->getInputAttributes()}>
			{foreach from=$element->get('options') item=title key=key}
				<option value="{$key}" {if Core::CompareValues($key, $element->get('value'))}selected{/if}>{$title}</option>
			{/foreach}
		</select>
	</div>

	<p class="form-element-description">{$element->get('description')}</p>
</div>

{if $element->get('readonly')}
	{* select options do not support readonly, but no reason why core plus can't ;) *}
	{script library="jqueryui.readonly"}{/script}
	{script location="foot"}<script>
		$('.{$element->get("id")}').readonly();
	</script>{/script}
{/if}

{if Core::IsLibraryAvailable('jquery')}
	{script library="jquery.minimalect"}{/script}
	{script location="foot"}<script>
	$(function(){
		$("select").minimalect();
	});
	</script>{/script}
{/if}
