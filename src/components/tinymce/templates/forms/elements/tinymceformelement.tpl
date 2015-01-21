{script library="tinymce"}{/script}
<div class="{$element->getClass()} {$element->get('id')}">
	<label class="form-element-label" for="{$element->get('name')}">{$element->get('title')|escape}</label>
	
	<p class="form-element-description">{$element->get('description')}</p>

	<div class="form-element-value">
		<textarea{$element->getInputAttributes()} name="{$element->get('name')}" >{$element->get('value')}</textarea>
	</div>
</div>