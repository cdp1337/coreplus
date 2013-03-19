{assign var='type' value='text'}
{include file="forms/elements/_standard_elements.tpl"}

{* And handle the necessary javascript for this element *}
{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$('#{$element->getID()}').datepicker( {$element->_javascriptconstructorstring} );
</script>{/script}