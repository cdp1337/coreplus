{if sizeof($elements)}
	<fieldset class="listing-filters collapsible {if !$filtersset}collapsed screen{/if}">
		<div class="fieldset-title">
			{t 'STRING_FILTERS'}
			<i class="icon icon-chevron-down expandable-hint"></i>
			<i class="icon icon-chevron-up collapsible-hint"></i>
		</div>

		<div class="collapsible-contents screen">
			{if $readonly}
				{foreach $elements as $element}
					{if $element->get('value')}
						{$element->get('title')}:
						{$element->getValueTitle()}
					{/if}
				{/foreach}
				{else}
				<form action="" method="GET">
					{foreach $elements as $element}
						{$element->render()}
					{/foreach}

					<div class="clear"></div>
					<a href="#" class="button reset-filters">
						<i class="icon icon-times"></i>
						<span>{t 'STRING_RESET'}</span>
					</a>
					<!-- Render a submit button so 'Enter' works... -->
					<input type="submit" style="display:none;"/>
					<a href="#" class="button apply-filters">
						<i class="icon icon-ok"></i>
						<span>{t 'STRING_APPLY'}</span>
					</a>
				</form>
			{/if}
		</div>


		{if $filtersset}
			<div class="print">
			{* The printable display for filters *}
				{foreach $elements as $element}
					{if $element->get('value')}
						{$element->get('title')}:
						{$element->getValueTitle()}
						&nbsp;&nbsp;&nbsp;
					{/if}

				{/foreach}
			</div>
		{/if}
	</fieldset>
{/if}

{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$(function(){
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

{if $hassort}
	{css}<style>
		.column-sortable th[sortkey] { cursor: pointer; }
		.column-sortable th i { float: right; }
		.column-sortable th i.other { visibility: hidden; }
		.column-sortable th:hover i.other { visibility: visible; }
		.column-sortable th:hover i.current { visibility: hidden; }
	</style>{/css}

	{script location="foot"}<script type="text/javascript">
		var $columnsortabletable = $('.column-sortable'),
			sortkey = "{$sortkey}",
			sortdir ="{$sortdir}",
			sortother = (sortdir == 'up' ? 'down' : 'up');

		$('.column-sortable th[sortkey]').each(function(){
			var $th = $(this);

			// Make sure it has a useful title.
			if(!$th.attr('title')) $th.attr('title', 'Sort by ' + $th.html());

			if($th.attr('sortkey') == sortkey){
				$th.append('<i class="icon icon-sort-' + sortdir + ' current"></i>');
				$th.append('<i class="icon icon-sort-' + sortother + ' other"></i>');
			}
			else{
				$th.append('<i class="icon icon-sort other"></i>');
			}
		});

		$('.column-sortable th[sortkey]').click(function(){
			var $th = $(this), newkey, newdir, req;

			if($th.attr('sortkey') == sortkey){
				// Set the dir
				newkey = sortkey;

				if(sortdir == 'up') newdir = 'down';
				else newdir = 'up';
			}
			else{
				newkey = $th.attr('sortkey');
				newdir = sortdir;
			}

			req = 'sortkey=' + newkey + '&sortdir=' + newdir;

			window.location.search = '?' + req;
		});
	</script>{/script}
{/if}