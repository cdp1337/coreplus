<div class="{$element->getClass()} {$element->get('id')}">

	<span class="checkboxes-label">{$element->get('title')|escape} <span class="checkboxes-toggle">Check All <i class='icon-check'></i></span></span>

	{foreach from=$element->get('options') item=title key=key}
		<label>
			<input type="checkbox" {$element->getInputAttributes()} value="{$key}" {if is_array($element->get('value')) && in_array($key, $element->get('value'))}checked="checked"{/if}/>
			{$title|escape}
		</label>
	{/foreach}

	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>

{script library="jquery"}{/script}

{script location="foot"}<script>

	$(function(){

		var checkstate = false;

		$('.checkboxes-toggle').click(function(){

			var $checkboxes = $(this).closest('.formcheckboxesinput').find('input[type=checkbox]');

			if(!checkstate) {
				$checkboxes.each(function(){
					$(this).prop('checked', true);
				});
				checkstate = true;
				$(this).html("Uncheck All <i class='icon-check'></i>");
			} else {
				$checkboxes.each(function(){
					$(this).prop('checked', false);
				});
				checkstate = false;
				$(this).html("Check All <i class='icon-check'></i>");
			}

		});

	});

</script>{/script}