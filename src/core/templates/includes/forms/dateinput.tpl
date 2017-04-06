{assign var='type' value='text'}
{include file="includes/forms/_standard_elements.tpl"}

{if Core::IsLibraryAvailable('jqueryui')}
	{* And handle the necessary javascript for this element *}
	{script library="jqueryui"}{/script}
	{script location="foot"}<script>
		$('#{$element->getID()}').datepicker( {$element->_javascriptconstructorstring} );
	</script>{/script}
{/if}