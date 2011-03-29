{script name="jqueryui"}{/script}
{script name="jqueryui.nestedSortable"}{/script}

{$form->render('head')}

{$form->render('body')}

<fieldset>
	<legend> Entries </legend>
	
	<select id="add-entry-select">
		<option value="int">Internal Page</option>
		<option value="ext">External Page</option>
		<option value="none">Text Only</option>
		<!--<option value="mailto">Mailto</option>-->
	</select>
	
	<a class="button add add-entry-btn" href="#" title="Add Entry">Add...</a>

	<!-- Create new entry heading -->
	<ul class="sortable-listing" id="entry-listings">
		{if isset($entries)}
			<!-- CRITICAL NOTE, make sure this HTML remains in sync with the javsacript logic below!!! -->
			{foreach from=$entries item="e"}
				<li id="entry-{$e->get('id')}" entryid="{$e->get('id')}" parentid="{$e->get('parentid')}">
					<div class="entry">
						<input type="hidden" name="entries[{$e->get('id')}][type]" value="{$e->get('type')}"/>
						<input type="hidden" name="entries[{$e->get('id')}][url]" value="{$e->get('baseurl')}"/>
						<input type="hidden" name="entries[{$e->get('id')}][target]" value="{$e->get('target')}"/>
						<input type="hidden" name="entries[{$e->get('id')}][title]" value="{$e->get('title')}"/>
						{$e->get('title')}
						<a href="#" class="edit-entry-link" style="float:right;">(edit entry)</a>
					</div>
				</li>
			{/foreach}
		{/if}
	</ul>
	
</fieldset>

<input type="submit" value="{$action}"/>

{$form->render('foot')}


<div class="add-entry-options add-entry-options-int" style="display:none;">
	<input type="hidden" name="id"/>
	<input type="hidden" name="type" value="int"/>
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
		<a href="#" class="button add submit-btn">Add/Update Entry</a>
	</div>
</div>

<div class="add-entry-options add-entry-options-ext" style="display:none;">
	<input type="hidden" name="id"/>
	<input type="hidden" name="type" value="ext"/>
	
	<div class="formelement">
		<label>URL</label>
		<input type="text" name="url"/>
		<!--<p class="formdescription">Please ensure to include the http:// or other protocol.</p>-->
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
		<a href="#" class="button add submit-btn">Add/Update Entry</a>
	</div>
</div>

<div class="add-entry-options add-entry-options-none" style="display:none;">
	<input type="hidden" name="id"/>
	<input type="hidden" name="type" value="none"/>
	<input type="hidden" name="url" value=""/>
	<input type="hidden" name="target" value=""/>
	
	<div class="formelement">
		<label>Label/Title</label>
		<input type="text" name="title"/>
	</div>
	
	<div class="formelement">
		<a href="#" class="button add submit-btn">Add/Update Entry</a>
	</div>
</div>




<script type="text/javascript">

	addcounter = 0;
	
	$(function(){
		$('#add-entry-select').click();
		
		// Capture the add new entry link.
		$('.add-entry-btn').click(function(){
			addcounter++;
			
			$el = $('#add-entry-select').find('option:selected');
			
			id = 'new' + addcounter;
			type = $el.val();
			title = '';
			url = '';
			target = '';
			$dialog = $('.add-entry-options-' + type);
			
			// Populate the entries in.
			$dialog.find('input[name=id]').val(id);
			$dialog.find('input[name=title]').val(title);
			$dialog.find(':input[name=url]').val(url);
			$dialog.find(':input[name=target]').val(target);
			
			// Open the dialog
			$dialog.show().dialog({
				modal: true,
				autoOpen: false,
				title: 'New ' + $el.html() + ' Options',
				width: '500px'
			}).dialog('open');
		
			return false;
		});
		
		// Capture click events for the edit link.
		$('#entry-listings a.edit-entry-link').live('click', function(){
			$this = $(this);
			id = $this.closest('li').attr('entryid');
			type = $this.closest('div').find('input[name="entries[' + id + '][type]"]').val();
			title = $this.closest('div').find('input[name="entries[' + id + '][title]"]').val();
			url = $this.closest('div').find('input[name="entries[' + id + '][url]"]').val();
			target = $this.closest('div').find('input[name="entries[' + id + '][target]"]').val();
			$dialog = $('.add-entry-options-' + type);
			
			// Populate the entries in.
			$dialog.find('input[name=id]').val(id);
			$dialog.find('input[name=title]').val(title);
			$dialog.find(':input[name=url]').val(url);
			$dialog.find(':input[name=target]').val(target);
			
			// Open the dialog
			$dialog.show().dialog({
				modal: true,
				autoOpen: false,
				title: title + ' Options',
				width: '500px'
			}).dialog('open');
		});
	
		// Capture the save entry options event.
		$('.add-entry-options').find('.submit-btn').click(function(){
			// Save the data to the form for submission.
			$dialog = $(this).closest('.add-entry-options');
			
			id = $dialog.find('input[name=id]').val();
			elid = 'entry-' + id;
			type = $dialog.find('input[name=type]').val();
			title = $dialog.find('input[name=title]').val();
			url = $dialog.find(':input[name=url]').val();
			target = $dialog.find(':input[name=target]').val();
			
			
			innerhtml = '<input type="hidden" name="entries[' + id + '][type]" value="' + type + '"/>'
			     + '<input type="hidden" name="entries[' + id + '][url]" value="' + url + '"/>'
			     + '<input type="hidden" name="entries[' + id + '][target]" value="' + target + '"/>'
			     + '<input type="hidden" name="entries[' + id + '][title]" value="' + title + '"/>'
			     + title
				 + '<a href="#" class="edit-entry-link" style="float:right;">(edit entry)</a>';
			 
			 // Does this element already exist on the form?
			 if($('#' + elid).length){
				 $('#' + elid + ' div.entry').html(innerhtml);
			 }
			 // Guess not...
			 else{
				 html = '<li id="' + elid + '" entryid="' + id + '">'
			     + '<div class="entry">'
			     + innerhtml
			     + '</div>'
			     + '</li>';
			
				$('#entry-listings').append(html);
			 }
			 
			
			$dialog.dialog('close');
			return false;
		});
		
		// Run through any statically placed elements and sort them appropriately.
		// This is mandatory for the "edit" function of the template.
		$('#entry-listings').find('li[parentid!=0]').each(function(){
			// Move this element to it's rightful parent!
			var $this = $(this);
			var parentid = $this.attr('parentid');
			if(!$('#entry-listings').find('li[entryid=' + parentid + '] ol').length){
				// Add a sub level to this element.
				$('#entry-listings').find('li[entryid=' + parentid + ']').append('<ol/>');
			}
			// And do the move!
			$('#entry-listings').find('li[entryid=' + parentid + '] ol').append($this);
		});
		
		// Do the actual sortable logic
		$('#entry-listings').nestedSortable({
			disableNesting: 'no-nest',
			forcePlaceholderSize: true,
			handle: 'div.entry',
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
