{$form->render()}


{script library="jquery"}{/script}
{script location="foot"}<script>
	$(function(){
		$('#formselectinput-model-type').change(function(){
			if($(this).val() == 'local'){
				$('#formaccessstringinput-model-manage_articles_permission').closest('.formelement').show();
				$('#formtextinput-model-remoteurl').closest('.formelement').hide();
			}
			else{
				$('#formaccessstringinput-model-manage_articles_permission').closest('.formelement').hide();
				$('#formtextinput-model-remoteurl').closest('.formelement').show();
			}
		});

		$('#formselectinput-model-type').change();
	});
</script>{/script}