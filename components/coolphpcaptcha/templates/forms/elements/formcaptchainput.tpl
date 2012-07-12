{script library="jquery"}{/script}

<div class="{$element->getClass()}">
	{if $element->get('title')}
		<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
	{/if}
	{* This is actually a bug I do believe... you should be able to request blah.png *}
	<img src="{link href='/simplecaptcha'}.png"/>
	<a href="#" onclick="$(this).closest('div').find('img').attr('src', '{link href='/simplecaptcha'}.png?date=' + (new Date()).getTime()); return false;">
		<i class="icon-refresh"></i>
		<span>Reload Image</span>
	</a>

	<br/>
	<input type="text"{$element->getInputAttributes()}>
	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
</div>