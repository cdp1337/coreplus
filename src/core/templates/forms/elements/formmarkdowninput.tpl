<div class="{$element->getClass()}">
	<label class="form-element-label" for="{$element->get('name')}">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	<div class="form-element-value">
		<textarea{$element->getInputAttributes()}>{$element->get('value')}</textarea>
	</div>

	<p class="form-element-description">{$element->get('description')}</p>
</div>

{if Core::IsLibraryAvailable('codemirror')}
	{script library="codemirror_md"}{/script}
	{script location="foot"}<script>
		var editor = CodeMirror.fromTextArea(document.getElementById("{$element->get('id')}"), {
			lineNumbers: true
		});
	</script>{/script}
{/if}