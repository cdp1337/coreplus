{* This input type actually requires jquery to function *}
{script library="jquery"}{/script}
{script src="js/core.fileupload.js"}{/script}
{if Core::IsComponentAvailable('media-manager')}
	{css src="assets/css/mediamanager/navigator.css"}{/css}
	{script src="assets/js/mediamanager/navigator.js"}{/script}
	{script library="jqueryui.readonly"}{/script}
{/if}

<div class="{$element->getClass()}">
	<label class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	{if $element->get('description')}
		{if strpos($element->get('description'), "\n")}
			<p class="form-element-description">{$element->get('description')}</p>
		{else}
			<span class="form-element-description">{$element->get('description')}</span>
		{/if}
	{/if}

	<noscript>
		<input type="file" name="{$element->get('name')}" accept="{$element->get('accept')}"/><br/>
		(Please enable javascript to get the most out of this form)
	</noscript>

	<table class="file-input-innercontainer clearfix form-element-value" id="{$element->get('id')}">
		<tr>
			<td id="{$element->get('id')}-selector" style="display:none;" class="formfileinput-selector">
				{if $element->get('value')}
					<label>
						<input type="radio" class="fileinput-selector" data-selectortype="current" name="{$element->get('name')}" value="{$element->get('value')}" checked="checked"/>
						Current
					</label>
				{/if}

				<label>
					<input type="radio" class="fileinput-selector" data-selectortype="upload" name="{$element->get('name')}" value="_upload_" {if !$element->get('value') && $element->get('required')} checked="checked"{/if}/>
					Upload
				</label>


				{if $element->get('allowlink')}
					<label>
						<input type="radio" class="fileinput-selector" data-selectortype="link" name="{$element->get('name')}" value=""/>
						Paste via URL
					</label>
				{/if}

				{if $browsable}
					<label>
						<input type="radio" class="fileinput-selector" data-selectortype="browse" name="{$element->get('name')}" value=""/>
						Browse
					</label>
				{/if}
				
				{if !$element->get('required')}
					<label>
						<input type="radio" class="fileinput-selector" data-selectortype="none" name="{$element->get('name')}" value=""{if !$element->get('value')} checked="checked"{/if}/>
						None
					</label>
				{/if}
			</td>

			<td class="file-input-actions" id="{$element->get('id')}-actions" style="display:none;">
				<div class="fileinput-action" data-selectortype="upload">
					<!-- This will be enabled if selected. -->
					<input type="file" name="{$element->get('name')}" disabled="disabled" accept="{$element->get('accept')}"/>
				</div>
				{if $element->get('value')}
					<div class="fileinput-action" data-selectortype="current">
						{img file=$element->getFile() dimensions=$element->get('previewdimensions')}<br/>
						{$element->getFile()->getBaseFilename()|truncate:60}
					</div>
				{/if}
				{if $element->get('allowlink')}
					<div class="fileinput-action" data-selectortype="link">
						URL: <input type="text" id="{$element->get('id')}-link-entry"/>
					</div>
				{/if}
				{if $browsable}
					<div class="fileinput-action" data-selectortype="browse" data-basedir="{$element->get('basedir')}" data-accept="{$element->get('accept')}">
						Loading...<br/>
						{img src="assets/images/loading-bar-small.gif"}
					</div>
				{/if}
				{if !$element->get('required')}
					<div class="fileinput-action" data-selectortype="none">
					</div>
				{/if}
			</td>

		</tr>
	</table>
</div>


<script type="text/javascript">
	$(function(){ Core.fileupload("{$element->get('id')}"); });
</script>