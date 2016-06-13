{assign var='type' value='text'}

<div class="{$element->getClass()} {$element->get('id')}">

	<label for="{$element->get('id')}" class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	

	{if $element->get('autocomplete') && $element->get('autocomplete') != 'on' && $element->get('autocomplete') != 'off'}
		{* values should be an array of value => label *}
		{if is_array($element->get('value'))}
			{assign var='values' value=$element->get('value')}
		{else}
			{assign var='values' value=[$element->get('value') => $element->get('value')]}
		{/if}

		{if $element->get('multiple')}
			{* Multiple also has a standard element because the existing values are written at the end. *}
			<div class="form-element-value">
				<input type="{$type}"{$element->getInputAttributes()}>
			</div>
			
			<div class="form-element-autocomplete-multiple-values">
				{foreach $values as $v => $l}
					<div class="autocomplete-value">
						<input type="hidden" name="{$element->get('name')}" value="{$v|escape}"/>
						<a href="#" class="remove-autocomplete-entry" title="Remove Entry"><i class="icon icon-trash-o"></i></a>
						{$l}
					</div>
				{/foreach}
			</div>
		{else}
			{**
			 * Singular autocomplete field is the main exception here.
			 * It has the label of the value as its value field to provide feedback to the user that something is saved.
			 *}
			<div class="form-element-value">
				<input type="{$type}"{$element->getInputAttributes()} value="{current($values)}">
			</div>
			
			<input id="{$element->getID()}-ac" type="hidden" name="{$element->get('name')}" value="{key($values)}"/>
		{/if}
	{else}
		{* Standard element! *}
		<div class="form-element-value">
			<input type="{$type}"{$element->getInputAttributes()}>
		</div>
	{/if}

	{if $element->get('description')}
		<p class="form-element-description">{$element->get('description')}</p>
	{/if}
</div>

{if $element->get('autocomplete') && $element->get('autocomplete') != 'on' && $element->get('autocomplete') != 'off'}

	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="Core.Strings"}{/script}
	{script location="foot"}<script>
		$(function(){
			var
				elementid = "{$element->getID()}",
				hiddenid  = "{$element->getID()}-ac",
				multiple  = {if $element->get('multiple')}true{else}false{/if},
				name      = "{$element->get('name')}",
				$element, $hidden, $parent;

			$element = $('#' + elementid);
			$parent = $element.closest('.formelement');

			if(multiple){
				$hidden = $parent.find('.form-element-autocomplete-multiple-values');
				$hidden.on('click', '.remove-autocomplete-entry', function() {
					$(this).closest('.autocomplete-value').remove();
					return false;
				});
			}
			else{
				$hidden = $('#' + hiddenid);
			}


			// Remap the name and value from the displayed input over to the hidden one.
			$element.attr('name', '');

			// Gogo autocomplete!
			$element.autocomplete({
				source: "{$element->get('autocomplete')}",
				minLength: 2,
				select: function( event, ui ) {
					if(ui.item){
						if(multiple){
							$hidden.append('<div class="autocomplete-value"><input type="hidden" name="' + name + '" value="' + ui.item.value + '"/><a href="#" class="remove-autocomplete-entry" title="Remove Entry"><i class="icon icon-trash-o"></i></a> ' + ui.item.label + '</div>');
							$element.val('');
						}
						else{
							// This is a bit different because the value is actually going to a different field.
							$hidden.val(ui.item.value);
							$element.val(ui.item.label);
						}
						// The return false is to prevent jqueryui from setting the value to the id of the user.
						// I want the label instead, (set above).
						return false;
					}
					else{
						if(multiple){
							// Clear out the input box
							$element.val('');
						}
						else{
							// Clear out the input box and hidden value for singles.
							$hidden.val('');
							$element.val('');
						}
					}
				},
				change: function(event, ui){
					if(!ui.item){
						// This only needs to be called if nothing was selected, (the above logic will handle that!)
						if(multiple){
							// Clear out the input box
							$element.val('');
						}
						else{
							// Clear out the input box and hidden value for singles.
							$hidden.val('');
							$element.val('');
						}
					}
				}
				// ui-autocomplete-loading
			});
		});

	</script>{/script}

{/if}