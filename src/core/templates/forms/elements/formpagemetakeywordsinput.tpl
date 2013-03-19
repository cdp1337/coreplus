<div class="{$element->getClass()} {$element->get('id')}">
	<div class="formelement-labelinputgroup">
		{if $element->get('title')}
			<label for="{$element->get('id')}">{$element->get('title')|escape}</label>
		{/if}

		<div class="keywords-multi-select">
			<input type="text"{$element->getInputAttributes()}>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>

	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}

</div>


{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script library="Core.Strings"}{/script}
{script location="foot"}<script>
	$(function(){

		var elementid = "{$element->getID()}", hiddenid,
			$keywords = $('#' + elementid),
			$hidden,
			$keywordsdiv = $keywords.closest('.keywords-multi-select'),
			keywordsinputname = $keywords.attr('name'),
			$form,
			$fieldset, allkeywords = [];

		// Trim off the last two [] if present.
		if(keywordsinputname.indexOf('[]') != -1){
			keywordsinputname = keywordsinputname.replace(/\[\]$/, '');
		}
		// Ok, then I need to update the original input and add them!
		else{
			$keywords.attr('name', $keywords.attr('name') + '[]');
		}

		$keywordsdiv.click(function(){
			$keywords.focus();
		});

		$keywords.addKeyword = function(keyword, title){
			var html = '';


			// Did they leave title blank?
			if(title == null){
				title = keyword;
				keyword = Core.Strings.toURL(keyword);
			}

			// Remove any pesky spaces that may be around the word(s).
			keyword = Core.Strings.trim(keyword);
			title = Core.Strings.trim(title);


			if(keyword == '') return;

			// quotes may not play nicely...
			keyword = keyword.replace(/"/g, '&quot;');
			title = title.replace(/"/g, '&quot;');

			// Already in the array?
			if(allkeywords.indexOf(keyword) != -1) return;

			// The beginning div
			html = '<div class="keywords-multi-select-option" keyword="' + keyword + '">' +
				// The keyword span
				'<span class="keywords-multi-select-option-keyword">' + title + '</span>' +
				// The remove icon
				' <a href="#" title="Click to remove keyword" class="keywords-multi-select-remove-link"><i class="icon-remove-circle"></i></a>' +
				// The hidden inputs, necessary for the form since it is a form after all :p
				'<input type="hidden" name="' + keywordsinputname + '[' + keyword + ']" value="' + title + '"/>' +
				// Closing the div.
				'</div>';

			allkeywords.push(keyword);

			$keywords.before(html);

		};

		$keywords.addKeywords = function(arrayOfKeywords){
			var i;
			for(i in arrayOfKeywords){
				$keywords.addKeyword(arrayOfKeywords[i]);
			}
		};

		// On initial page load, grab all the terms and add them via the fancy interface :)
		{foreach $element->get('value') as $keyword => $value}
			$keywords.addKeyword("{$keyword}", "{$value}");
		{/foreach}

		$keywords.keydown(function(e){
			switch(e.keyCode){
				case $.ui.keyCode.COMMA:
				case $.ui.keyCode.ENTER:
					$keywords.addKeyword($(this).val());
					$(this).val('');

					return false;
			}

		});

		$keywordsdiv.on('click', '.keywords-multi-select-remove-link', function(){
			var $option = $(this).closest('.keywords-multi-select-option');

			// Don't forget to remove the term from the list of allkeywords too.
			allkeywords.splice(allkeywords.indexOf( $option.attr('keyword') ), 1);

			$option.remove();

			return false;
		});

		$keywords.autocomplete({
			source: Core.ROOT_URL + 'form/pagemetas/autocompletekeyword.ajax',
			minLength: 2,
			select: function( event, ui ) {

				if(ui.item){
					$keywords.addKeyword(ui.item.id, ui.item.label);
					$(this).val('');
					// The return false is to prevent jqueryui from setting the value to the id of the keyword.
					return false;
				}
				else{
					// Just clear out the user id.
					$(this).val('');
				}
			}
			// ui-autocomplete-loading
		});

	});
	// formpagemetasinput-page-metas-author

</script>{/script}