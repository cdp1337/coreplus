{script name="Core.Strings"}{/script}
{script name="jqueryui"}{/script}
{script name="jqueryui.nestedSortable"}{/script}
{script src="js/navigation/manager.js"}{/script}

{$form->render('head')}

{$form->render('body')}

<fieldset>
	<legend> Entries </legend>
	
	<a class="button add add-entry-int-btn" href="#" title="Add Internal Link">Add Internal Link</a>
	<a class="button add add-entry-ext-btn" href="#" title="Add External Link">Add External Link</a>
	<a class="button add add-entry-none-btn" href="#" title="Add Text Label">Add Text Label</a>

	<!-- Create new entry heading -->
	<ul class="sortable-listing" id="entry-listings"></ul>
	
</fieldset>

<input type="submit" value="{$action}"/>

{$form->render('foot')}

{if isset($entries)}
	<script type="text/javascript">
		$(function(){
			{foreach from=$entries item='e'}
				NavigationManager.addEntry({
					id:     "{$e->get('id')}",
					type:   "{$e->get('type')}",
					url:    "{$e->get('baseurl')}",
					target: "{$e->get('target')}",
					title:  "{$e->get('title')}",
					parent: "{$e->get('parentid')}",
				});
			{/foreach}
		});
	</script>
{/if}


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
