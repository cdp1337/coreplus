{* This input type actually requires jquery to function *}
{script library="jquery"}{/script}
{script src="js/core.fileupload.js"}{/script}

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

		<div class="file-input-innercontainer">

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
				{if $element->get('allowlink')}
					<label>
						<input type="radio" id="{$element->get('id')}-selector-link" name="{$element->get('name')}" value=""/>
						Paste via URL
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

			<div class="file-input-actions" id="{$element->get('id')}-actions" style="display:none;">
				<div id="{$element->get('id')}-action-upload">
					<!-- This will be enabled if selected. -->
					<input type="file" name="{$element->get('name')}" disabled="disabled"/>
				</div>
			{if $element->get('value')}
				<div id="{$element->get('id')}-action-current">
					{file_thumbnail file=$element->getFile() dimensions=$element->get('previewdimensions')}
					{$element->getFile()->getBaseFilename()}
				</div>
			{/if}
			{if $element->get('allowlink')}
				<div id="{$element->get('id')}-action-link">
					<input type="text" id="{$element->get('id')}-link-entry"/>
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

			<div class="clear"></div>
		</div>

		<div class="clear"></div>

	</div>

	<div class="clear"></div>


	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>


<script type="text/javascript">
	$(function(){ Core.fileupload("{$element->get('id')}", "{$element->get('value')}"); });
</script>