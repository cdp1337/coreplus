{$form->render()}

{if $use_contexts}
	{script location="foot"}<script>
		var contexts = {$context_json};

		$('#formselectinput-model-context').click(function(){
			var val = $(this).val(),
				i,
				$perms = $('input[name="permissions[]"]');

			$perms.each(function(){
				var thisval = $(this).val();

				//if(contexts[thisval] == val) $(this).closest('label').show();
				//else $(this).closest('label').hide();
				if(contexts[thisval] == val) $(this).closest('label').slideDown();
				else $(this).closest('label').slideUp();
			});

			if(val == ''){
				$('#formradioinput-model-default').slideDown();
			}
			else{
				$('#formradioinput-model-default').slideUp();
			}

		});

		$('#formselectinput-model-context').click();

	</script>{/script}
{/if}