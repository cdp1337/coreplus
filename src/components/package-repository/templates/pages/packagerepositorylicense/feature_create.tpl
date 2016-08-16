<p class="message-tutorial">
	The feature tag MUST match exactly what is in the LICENSER.php file.
</p>

{$form->render()}

{script library="jquery"}{/script}
{script location="foot"}<script>
	$(function() {
		var $typeSelect = $('#formselectinput-model-type'),
			$options = $('#formtextareainput-model-options'),
			$optionsWrapper = $options.closest('div.formelement');
		
		$typeSelect.change(function() {
			if($(this).val() != 'enum'){
				$options.val('');
				$optionsWrapper.hide();
			}
			else{
				$optionsWrapper.show();
			}
		});
		
		$typeSelect.change();
	});
</script>{/script}