{assign var='type' value='text'}
{include file="includes/forms/_standard_elements.tpl"}


{if Core::IsLibraryAvailable('jqueryui')}
	{* And handle the necessary javascript for this element *}
	{script library="jqueryui"}{/script}
	{script library="jqueryui.timepicker"}{/script}
	{script location="foot"}
	<script>
		$(function(){
			$('#{$element->getID()}')
				.datetimepicker( {$element->_javascriptconstructorstring} )
				.on('click', function(){
					$(this).datetimepicker('show');
				});
		});
	</script>
	{/script}
{/if}