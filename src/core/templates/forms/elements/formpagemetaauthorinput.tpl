{assign var='type' value='text'}
{include file="forms/elements/_standard_elements.tpl"}

{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script library="Core.Strings"}{/script}
{script location="foot"}<script>
	$(function(){

		var
			elementid = "{$element->getID()}", hiddenid,
			$element, $hidden;

		hiddenid = elementid.replace('formpagemetaauthorinput', 'formhiddeninput') + "id";
		$element = $('#' + elementid);
		$hidden = $('#' + hiddenid);

		// Gogo autocomplete!
		$element.autocomplete({
			source: Core.ROOT_URL + 'form/pagemetas/autocompleteuser.ajax',
			minLength: 2,
			select: function( event, ui ) {

				if(ui.item){
					// This is a bit different because the value is actually going to a different field.
					$hidden.val(ui.item.id);
					$(this).val(ui.item.label);
					// The return false is to prevent jqueryui from setting the value to the id of the user.
					// I want the label instead, (set above).
					return false;
				}
				else{
					// Just clear out the user id.
					$hidden.val('');
				}
			}
			// ui-autocomplete-loading
		});

		// On changing the username, the authorid should be blanked out automatically!
		$element.change(function(){
			$hidden.val('');
		});

	});
	// formpagemetasinput-page-metas-author

</script>{/script}