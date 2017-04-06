{assign var="jquery_available" value=Core::IsLibraryAvailable('jquery')}

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
		<input type="checkbox" {$element->getInputAttributes()}>
	</div>
</div>

{if $jquery_available}
	{script library="jquery.icheck"}{/script}
	{script location="foot"}<script>
		$(function(){
			$('input[type=checkbox]').icheck({ 'checkboxClass': 'icheckbox_flat'});
		});
	</script>{/script}
{/if}