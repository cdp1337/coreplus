{script library="jquery"}{/script}

<div class="{$element->getClass()}">
	{if $element->get('title')}
		<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
	{/if}
	
	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}
	
	<div class="formelement formradioinput">
		<label><input type="radio" name="{$element->get('name')}" class="{$dynname}_main" value="basic_anyone"/>Allow Anyone</label>
		<label><input type="radio" name="{$element->get('name')}" class="{$dynname}_main" value="basic_anonymous"/>Only Anonymous</label>
		<label><input type="radio" name="{$element->get('name')}" class="{$dynname}_main" value="basic_authenticated"/>Only Authenticated</label>
		
	</div>
	<fieldset>
		<legend>
			<label><input type="radio"name="{$element->get('name')}" class="{$dynname}_main" value="advanced"/>Advanced...</label>
		</legend>
		<div class="formelement formradioinput {$dynname}_advanced" style="display:none;">
			<label><input type="radio" name="{$dynname}_type" value="whitelist"/>Allow Only...</label>
			<label><input type="radio" name="{$dynname}_type" value="blacklist"/>Disallow Only...</label>
		</div>
		<div class="formelement formcheckboxinput {$dynname}_advanced" style="display:none;">
			<label><input type="checkbox" name="{$dynname}[]" value="anonymous"/>Anonymous Users</label>
			<label><input type="checkbox" name="{$dynname}[]" value="authenticated"/>Authenticated Users</label>
			{foreach from=$groups item='g'}
				<label><input type="checkbox" name="{$dynname}[]" value="{$g->get('id')}"/>{$g->get('name')}</label>
			{/foreach}
		</div>
	</fieldset>

	<script type="text/javascript">
		$('.{$dynname}_main').click(function(){
			if($(this).val() == 'advanced'){
				$('.{$dynname}_advanced').show();
			}
			else{
				$('.{$dynname}_advanced').hide();
			}
		});
	</script>
	
	{*
	
	<select {$element->getInputAttributes()}>
		{foreach from=$element->get('options') item=title key=key}
			<option value="{$key}" {if $key == $element->get('value')}selected{/if}>{$title}</option>
		{/foreach}
	</select>
	*}
</div>