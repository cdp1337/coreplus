{$form->render()}

{if $use_contexts}
	{script location="foot"}<script>
		var contexts = {$context_json},
			contextupdatefn;

		contextupdatefn = function(){
			var $inp = $('#formselectinput-model-context').length == 1 ? $('#formselectinput-model-context') : $('#formhiddeninput-model-context'),
				val = $inp.val(),
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
		}

		$('#formselectinput-model-context').click(contextupdatefn);

		contextupdatefn();

	</script>{/script}
{/if}
