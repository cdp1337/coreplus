{* This input type actually requires jquery to function *}
{script library="jquery"}{/script}
{script src="js/core.fileupload.js"}{/script}
{if Core::IsComponentAvailable('media-manager')}
	{css src="assets/css/mediamanager/navigator.css"}{/css}
	{script src="assets/js/mediamanager/navigator.js"}{/script}
	{script library="jqueryui.readonly"}{/script}
{/if}

<div class="{$element->getClass()}">
	<div class="formelement-labelinputgroup">
		{if $element->get('title')}
			<label>{$element->get('title')|escape}</label>
		{/if}

		<div class="clear"></div>

		<noscript>
			<input type="file" name="{$element->get('name')}"/><br/>
			(Please enable javascript to get the most out of this form)
		</noscript>

		<table class="file-input-innercontainer clearfix" id="{$element->get('id')}" accept="{$element->get('accept')}"><tr>

			<td id="{$element->get('id')}-selector" style="display:none;" class="formfileinput-selector">
				{if $element->get('value')}
					<label>
						<input type="radio" class="fileinput-selector" selectortype="current" name="{$element->get('name')}" value="{$element->get('value')}" checked="checked"/>
						Current
					</label>
				{/if}

				<label>
					<input type="radio" class="fileinput-selector" selectortype="upload" name="{$element->get('name')}" value="_upload_" {if !$element->get('value')}checked="checked"{/if}/>
					Upload
				</label>


				{if $element->get('allowlink')}
					<label>
						<input type="radio" class="fileinput-selector" selectortype="link" name="{$element->get('name')}" value=""/>
						Paste via URL
					</label>
				{/if}

				{if $browsable}
					<label>
						<input type="radio" class="fileinput-selector" selectortype="browse" name="{$element->get('name')}" value=""/>
						Browse
					</label>
				{/if}

				{if !$element->get('required')}
					<label>
						<input type="radio" class="fileinput-selector" selectortype="none" name="{$element->get('name')}" value=""/>
						None
					</label>
				{/if}
			</td>

			<td class="file-input-actions" id="{$element->get('id')}-actions" style="display:none;">
				<div class="fileinput-action" selectortype="upload">
					<!-- This will be enabled if selected. -->
					<input type="file" name="{$element->get('name')}" disabled="disabled" accept="{$element->get('accept')}"/>
				</div>
				{if $element->get('value')}
					<div class="fileinput-action" selectortype="current">
						{img file=$element->getFile() dimensions=$element->get('previewdimensions')}<br/>
						{$element->getFile()->getBaseFilename()|truncate:60}
					</div>
				{/if}
				{if $element->get('allowlink')}
					<div class="fileinput-action" selectortype="link">
						<input type="text" id="{$element->get('id')}-link-entry"/>
					</div>
				{/if}
				{if $browsable}
					<div class="fileinput-action" selectortype="browse">
						Loading...<br/>
						{img src="assets/images/loading-bar-small.gif"}
					</div>
				{/if}
				{if !$element->get('required')}
					<div class="fileinput-action" selectortype="none">
					</div>
				{/if}
			</td>

		</tr></table>

	</div>

	<div class="clear"></div>


	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>


<script type="text/javascript">
	$(function(){ Core.fileupload("{$element->get('id')}"); });
</script>