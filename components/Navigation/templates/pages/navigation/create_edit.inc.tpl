{script name="jqueryui"}
{script name="jqueryui.nestedSortable"}

{$form->render('head')}

{$form->render('body')}

<fieldset>
	<legend> Entries </legend>
	
	<select id="add-entry-select">
		<option value="int">Internal Page</option>
		<option value="ext">External Page</option>
		<!--<option value="mailto">Mailto</option>-->
	</select>
	
	<a class="button add add-entry-btn" href="#" title="Add Entry">Add...</a>

	<!-- Create new entry heading -->
	<ul class="sortable-listing" id="entry-listings">
	</ul>
	
</fieldset>

<input type="submit" value="{$action}"/>

{$form->render('foot')}


<div class="add-entry-options add-entry-options-int" style="display:none;">
	<div class="formelement">
		<label>Page</label>
		<select name="url">
			{foreach from=$pages item='title' key='baseurl'}
				<option value="{$baseurl}">
					{$title}
				</option>
			{/foreach}
		</select>
	</div>
	
	<div class="formelement">
		<label>Label/Title</label>
		<input type="text" name="title"/>
	</div>

	<div class="formelement">
		<label>Opens in</label>
		<select name="target">
			<option value="">Current Window</option>
			<option value="_BLANK">New Window</option>
		</select>
	</div>
	
	<div class="formelement">
		<a href="#" class="button add submit-btn">Add Entry</a>
	</div>
</div>

<div class="add-entry-options add-entry-options-ext" style="display:none;">
	<select name="add-url-protocol">
		<option value="http://">http://</option>
		<option value="https://">https://</option>
		<option value="ftp://">ftp://</option>
	</select>
	<input type="text" name="add-url"/>

	Opens in
	<select name="add-openin">
		<option value="Current Window">Current Window</option>
		<option value="New Window">New Window</option>
	</select>
</div>




<script type="text/javascript">

	addcounter = 0;
	
	$(function(){
		$('.add-entry-btn').click(function(){
			$el = $('#add-entry-select').find('option:selected');
			v = $el.val();
			//$t = $('#add-entry-table');
			//$t.find('.add-entry-options').hide();
			//$t.find('.add-entry-options-' + v).show();
			$('.add-entry-options-' + v).show().dialog({
				modal: true,
				autoOpen: false,
				title: $el.html() + ' Options'
			}).dialog('open');
		
			return false;
		});
	
		$('#add-entry-select').click();
	
		$('.add-entry-options-int').find('.submit-btn').click(function(){
			// Save the data to the form for submission.
			$dialog = $('.add-entry-options-int');
			addcounter++;
			
			id = 'new' + addcounter;
			elid = 'entry-' + id;
			type = 'int';
			title = $dialog.find('input[name=title]').val();
			url = $dialog.find('select[name=url]').val();
			target = $dialog.find('select[name=target]').val();
			
			html = '<li id="' + elid + '" entryid="' + id + '">'
			     + '<div class="entry">'
			     + '<input type="hidden" name="entries[' + id + '][type]" value="' + type + '"/>'
			     + '<input type="hidden" name="entries[' + id + '][url]" value="' + url + '"/>'
			     + '<input type="hidden" name="entries[' + id + '][target]" value="' + target + '"/>'
			     + '<input type="hidden" name="entries[' + id + '][title]" value="' + title + '"/>'
			     + title
			     + '</div>'
			     + '</li>';
			
			$('#entry-listings').append(html);
			
			$dialog.dialog('close');
			return false;
		});
		
		$('#entry-listings').nestedSortable({
			disableNesting: 'no-nest',
			forcePlaceholderSize: true,
			handle: 'div',
			helper:	'clone',
			items: 'li',
			opacity: .6,
			placeholder: 'placeholder',
			tabSize: 25,
			tolerance: 'pointer',
			toleranceElement: '> div'
		});
		
		// Capture the parent form submit too!
		$('#entry-listings').closest('form').submit(function(){
			// Create a new input field for the sort order of the listings.
			$(this).append('<input type="hidden" name="entries-sorting" value="' + $('#entry-listings').nestedSortable('serialize') + '"/>');
		});
		// 
	});
</script>
