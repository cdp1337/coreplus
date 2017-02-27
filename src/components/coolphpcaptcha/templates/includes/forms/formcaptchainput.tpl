{script library="jquery"}{/script}

<div class="{$element->getClass()}">
	<label for="{$element->get('name')}" class="form-element-label">
		{$element->get('title')|escape}

		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}

		<a class="reload-captcha" href="#" onclick="$(this).closest('div').find('img').attr('src', '{link href='/simplecaptcha.png'}?date=' + (new Date()).getTime()); return false;">
			<i class="icon icon-refresh"></i>
			<span>Reload Image</span>
		</a>

	</label>

	<div class="form-element-value clearfix">
		<img src="{link href='/simplecaptcha.png'}"/>
		<input type="text"{$element->getInputAttributes()} placeholder="Letters from Image">
	</div>

	<p class="form-element-description">{$element->get('description')}</p>
</div>