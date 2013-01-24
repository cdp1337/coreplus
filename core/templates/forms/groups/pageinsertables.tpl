<fieldset class="{$group->getClass()}"{$group->getGroupAttributes()}>
	<legend> {$group->get('title')} </legend>
	{if $group->get('description')}
		<p class="formdescription">{$group->get('description')}</p>
	{/if}

	{$elements}

</fieldset>

{script library="jquery"}{/script}
{script location="foot"}<script>
	var elementname = "{$group->get('name')}",
		$form = $('#formpagepageselectinput-' + elementname + '_page_template').closest('form'),
		$fieldset = $('#formpagepageselectinput-' + elementname + '_page_template').closest('fieldset');

	// The .on is needed because the fieldset gets overwritten with the contents of the dynamic content.
	$form.on('change', '#formpagepageselectinput-' + elementname + '_page_template', function(){
		var $form = $(this).closest('form'),
			$formid = $('#formhiddeninput-___formid'),
			newval = $(this).val();

		$.ajax({
			url: Core.ROOT_URL + 'form/pageinsertables/update.ajax',
			type: 'post',
			dataType: 'json',
			data: {
				templatename: newval,
				formid: ($formid.length) ? $formid.val() : null,
				elementname: elementname
			},
			success: function(response){
				var $newhtml = $(response.html);
				console.log($newhtml.html());
				$fieldset.html($newhtml.html());

				// This is a total hack, but it works... leave the alone!
				if(typeof tinymce != 'undefined'){
					$fieldset.find('textarea.tinymce').tinymce(Core.TinyMCEDefaults);
				}
			}
		});
	});
</script>{/script}