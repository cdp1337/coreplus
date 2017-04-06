<div class="{$element->getClass()} {$element->get('id')}" id="{$element->get('id')}">
	<span class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</span>

	{if $element->get('description')}
		{if strpos($element->get('description'), "\n")}
			<p class="form-element-description">{$element->get('description')}</p>
		{else}
			<span class="form-element-description">{$element->get('description')}</span>
		{/if}
	{/if}

	<div class="form-element-value clearfix">
		{foreach from=$element->get('options') item=title key=key}
			<label>
				<input type="radio" {$element->getInputAttributes()} value="{$key}" {if $key == $element->getChecked()}checked{/if}/>
				{$title|escape}
			</label>
		{/foreach}
	</div>
</div>

{if Core::IsLibraryAvailable('jquery')}
	{script library="jquery.icheck"}{/script}
	{script location="foot"}<script>
		$(function(){
			var $radio = $('input[type=radio]');

			$radio.icheck({ 'radioClass': 'iradio_flat'});

			$radio.on('ifChecked', function(event){
				$(this).closest('.fileinput-selector').click();
			});

		});
	</script>{/script}
{/if}