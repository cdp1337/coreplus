<div class="{$element->getClass()} {$element->get('id')} checkboxes-toggleable" id="{$element->get('id')}">

	<span class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}

		<span class="checkboxes-toggle checkboxes-toggle-check" style="display:none;" title="Check All">
			<span>Check All</span>
			<i class='icon-check'></i>
		</span>
		<span class="checkboxes-toggle checkboxes-toggle-uncheck" style="display:none;" title="Uncheck All">
			<span>Uncheck All</span>
			<i class='icon-check'></i>
		</span>
	</span>

	<div class="form-element-value clearfix">
		{foreach from=$element->get('options') item=title key=key}
			<label>
				<input type="checkbox" {$element->getInputAttributes()} value="{$key}" {if is_array($element->get('value')) && in_array($key, $element->get('value'))}checked="checked"{/if}/>
				{$title|escape}
			</label>
		{/foreach}
	</div>

	<p class="form-element-description">{$element->get('description')}</p>
</div>

{script library="jquery"}{/script}

{script location="foot"}<script>

	$(function(){

		$('.checkboxes-toggleable').each(function(){
			// Should this set be checked or unchecked by default?
			var allchecked = true,
				$this = $(this),
				$checktoggle = $this.find('.checkboxes-toggle-check'),
				$unchecktoggle = $this.find('.checkboxes-toggle-uncheck'),
				$inputs = $this.find('input');

			$inputs.each(function(){
				if(!$(this).is(':checked')){
					allchecked = false;
					return false;
				}
			});

			if(allchecked){
				// All children checkboxes are checked... show the uncheck option.
				$unchecktoggle.show();
			}
			else{
				// There is at least one checkbox that is unchecked.  Display the check all option.
				$checktoggle.show();
			}

			// Now, I can bind the click events on the toggle options.
			$unchecktoggle.click(function(){
				$inputs.each(function(){
					$(this).prop('checked', false);

					if( $(this).parent().hasClass('icheckbox_flat') ) {
						$(this).iCheck('update');
					}
				});
				$unchecktoggle.toggle();
				$checktoggle.toggle();
			});

			$checktoggle.click(function(){
				$inputs.each(function(){
					$(this).prop('checked', true);

					if( $(this).parent().hasClass('icheckbox_flat') ) {
						$(this).iCheck('update');
					}
				});
				$unchecktoggle.toggle();
				$checktoggle.toggle();
			});
		});
	});

</script>{/script}