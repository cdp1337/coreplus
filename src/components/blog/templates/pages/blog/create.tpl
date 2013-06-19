{$form->render()}


{script library="jquery"}{/script}
{script}<script>
	$(function(){
		$('#formselectinput-model-type').change(function(){
			if($(this).val() == 'local'){
				$('#formaccessstringinput-model-manage_articles_permission').closest('.formelement').show();
				$('#formtextinput-model-remote_url').closest('.formelement').hide();
			}
			else{
				$('#formaccessstringinput-model-manage_articles_permission').closest('.formelement').hide();
				$('#formtextinput-model-remote_url').closest('.formelement').show();
			}
		});

		$('#formselectinput-model-type').change();
	});
</script>{/script}