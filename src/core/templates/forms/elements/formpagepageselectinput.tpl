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
			$form.ajaxSubmit(function(r){
				var d = new Date(), r = Core.Request.get(), i, l = window.location.href;

				for(i in r){
					if(i == 'date'){
						// It's in the query string, replace it.
						l = l.replace(/([\?&]date=)[0-9]*/, '$1' + d.getTime());
						window.location.href = l;
						return;
					}
				}

				if(l.indexOf('?') == -1){
					window.location.href = l + '?date=' + d.getTime();
				}
				else{
					window.location.href = l + '&date=' + d.getTime();
				}
			});

			// Just in case there was an error on the submission, it's still locked out...
			setTimeout(function(){ Core.Reload(); }, 5000);
		});
	});
</script>{/script}