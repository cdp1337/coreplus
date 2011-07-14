{* This input type actually requires jquery to function *}
{script library="jquery"}{/script}

<div class="{$element->getClass()}">
	{if $element->get('title')}
		<label>{$element->get('title')|escape}</label>
	{/if}
	
	<noscript>
		<input type="file" name="{$element->get('name')}"/><br/>
		(Please enable javascript to get the most out of this form)
	</noscript>
	
	<div id="{$element->get('id')}-selector" style="display:none;" class="formfileinput-selector">
		<label>
			<input type="radio" id="{$element->get('id')}-selector-upload" name="{$element->get('name')}" value="_upload_"/>
			Upload
		</label>
		
		{if $element->get('value')}
			<label>
				<input type="radio" id="{$element->get('id')}-selector-current" name="{$element->get('name')}" value="{$element->get('value')}"/>
				Current
			</label>
		{/if}
		{if $element->get('browsable')}
			<label>
				<input type="radio" id="{$element->get('id')}-selector-browse" name="{$element->get('name')}" value="{$element->get('value')}"/>
				Browse Server
			</label>
		{/if}
		{if !$element->get('required')}
			<label>
				<input type="radio" id="{$element->get('id')}-selector-none" name="{$element->get('name')}" value=""/>
				None
			</label>
		{/if}
	</div>
	
	<div id="{$element->get('id')}-actions" style="display:none;">
		<div id="{$element->get('id')}-action-upload">
			<input type="file" name="{$element->get('name')}"/>
		</div>
		{if $element->get('value')}
			<div id="{$element->get('id')}-action-current">
				{file_thumbnail file=$element->getFile() dimensions=$element->get('previewdimensions')}
				{$element->getFile()->getBaseFilename()}
			</div>
		{/if}
		{if $element->get('browsable')}
			<div id="{$element->get('id')}-action-browse">
				(not supported yet)
			</div>
		{/if}
		{if !$element->get('required')}
			<div id="{$element->get('id')}-action-none">
			</div>
		{/if}
	</div>
	
	<div style="clear:both;"></div>
	
	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>


<script type="text/javascript">
	Core = (window.Core)? window.Core : { version:'1.0.0' };
	
	Core.fileupload = function(idprefix, current){
		// Because "this" gets overwritten frequently by events and calls...
		var fileupload = this;
		
		// The respective elements for this system are:
		// -action-upload, -action-current, -action-browse, -action-none
		
		this.$selector = $('#' + idprefix + '-selector');
		
		this.hasUpload = ($('#' + idprefix + '-selector-upload').length > 0);
		this.hasCurrent = ($('#' + idprefix + '-selector-current').length > 0);
		this.hasBrowse = ($('#' + idprefix + '-selector-browse').length > 0);
		this.hasNone = ($('#' + idprefix + '-selector-none').length > 0);

		// Only show the selector if there is more than 1 label to select...
		if(this.$selector.find('label').length > 1){
			this.$selector.show();
			$('#' + idprefix + '-actions').children('div').hide();
			$('#' + idprefix + '-actions').show();
		}
		else{
			$('#' + idprefix + '-actions').show();
		}
			
		if(this.hasUpload){
			$('#' + idprefix + '-selector-upload').change(function(){
				$('#' + idprefix + '-actions').children('div').hide();
				$('#' + idprefix + '-action-upload').show();
			});
		}
		
		if(this.hasCurrent){
			$('#' + idprefix + '-selector-current').change(function(){
				$('#' + idprefix + '-actions').children('div').hide();
				$('#' + idprefix + '-action-current').show();
			});
		}
		
		if(this.hasNone){
			$('#' + idprefix + '-selector-none').change(function(){
				$('#' + idprefix + '-actions').children('div').hide();
				$('#' + idprefix + '-action-none').show();
			});
		}
		
		if(this.hasBrowse){
			$('#' + idprefix + '-selector-browse').change(function(){
				$('#' + idprefix + '-actions').children('div').hide();
				$('#' + idprefix + '-action-browse').show();
			});
		}
		
		if(current){
			// There is currently an image selected, enable that option.
			$('#' + idprefix + '-selector-current').attr('checked', 'true').change();
		}
	}
	$(function(){
		var fileobj = new Core.fileupload("{$element->get('id')}", "{$element->get('value')}");
	});
</script>