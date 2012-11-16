<fieldset class="listing-filters collapsible screen {if !$filtersset}collapsed{/if}">
	<legend> Filters </legend>
	<form action="" method="GET">
		<div class="collapsible-contents">

			{foreach $elements as $element}
				{$element->render()}
			{/foreach}

			<div class="clear"></div>
			<a href="#" class="button reset-filters">
				<i class="icon-remove"></i>
				<span>Reset Filters</span>
			</a>
			<a href="#" class="button apply-filters" style="float:right;">
				<i class="icon-ok"></i>
				<span>Apply Filters</span>
			</a>

		</div>
	</form>
</fieldset>

{script library="jqueryui"}{/script}
{script location="foot"}<script>
    $(function(){
        $('fieldset.collapsible.collapsed').find('.collapsible-contents').hide();

        $('fieldset.collapsible legend').css('cursor', 'pointer').click(function(){
            var $this, $fieldset;

            $this = $(this);
            $fieldset = $this.closest('fieldset');

            $fieldset.toggleClass('collapsed').find('.collapsible-contents').toggle('fast');
        });

        $('.apply-filters').click(function(){
            $(this).closest('form').submit();
            return false;
        });

        $('.reset-filters').click(function(){
            $(this).closest('form').find(':input').val('');
            $(this).closest('form').submit();
            return false;
        });
    });
</script>{/script}