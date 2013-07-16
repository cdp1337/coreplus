{include file="forms/elements/formselectinput.tpl"}

{script library="jquery"}{/script}
{script library="jquery.form"}{/script}
{script library="jqueryui.readonly"}{/script}
{script location="foot"}<script>
	$(function(){
		var id = "{$element->getID()}",
			$element = $('#' + id),
			elementname = $element.attr('name'),
			$form = $element.closest('form'),
			$formid = $form.find('#formhiddeninput-___formid');

		$element.on('change', function(){
			$form.readonly(true);
			// Save the form and reload.
			//$form.attr('action', Core.ROOT_URL + 'form/savetemporary.ajax');
			$form.attr('action', Core.ROOT_URL + 'form/pageinsertables/update.ajax');
			$form.ajaxSubmit(function(){
				Core.Reload();
			});
		});
	});
</script>{/script}