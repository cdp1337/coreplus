<div class="{$element->getClass()} {$element->get('id')} clearfix">
	<label class="form-element-label" for="{$element->get('id')}-ac">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	<div class="user-input-wrapper form-element-value">
		{if $can_lookup}
			<div class="user-indicator">
				<span class="user-valid-indicator" title="User is valid!">
					<i class="icon-ok"></i>
				</span>
				<span class="user-invalid-indicator" title="No user account linked.">
					<i class="icon-exclamation-circle"></i>
				</span>
			</div>

			<input type="text"{$element->getInputAttributes()} id="{$element->get('id')}-ac" value="{$username}"/>
			<input type="hidden" name="{$element->get('name')}" id="{$element->get('id')}" value="{$element->get('value')}"/>
		{else}
			<input type="text"{$element->getInputAttributes()} name="{$element->get('name')}" id="{$element->get('id')}-ac" value="{$element->get('value')}"/>
		{/if}
	</div>

	<p class="form-element-description">{$element->get('description')}</p>

</div>

{if $can_lookup}

	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="Core.Strings"}{/script}
	{script location="foot"}<script>
	$(function(){

		var
			elementid = "{$element->getID()}-ac", hiddenid = "{$element->getID()}",
			$element, $hidden, $parent, lastval;

		$element = $('#' + elementid);
		$hidden = $('#' + hiddenid);
		$parent = $element.closest('.formelement');

		// Gogo autocomplete!
		$element.autocomplete({
			source: Core.ROOT_URL + 'form/pagemetas/autocompleteuser.ajax',
			minLength: 2,
			select: function( event, ui ) {

				if(ui.item){
					// This is a bit different because the value is actually going to a different field.
					$hidden.val(ui.item.id);
					$(this).val(ui.item.label);
					$parent.removeClass('user-invalid').addClass('user-valid');
					lastval = $element.val();
					// The return false is to prevent jqueryui from setting the value to the id of the user.
					// I want the label instead, (set above).
					return false;
				}
				else{
					// Just clear out the user id.
					$hidden.val('');
					$parent.removeClass('user-valid').addClass('user-invalid');
					lastval = $element.val();
				}
			}
			// ui-autocomplete-loading
		});

		// On changing the username, the authorid should be blanked out automatically!
		//$element.change(function(){
		$element.keyup(function(){

			// The key did not cause a change, just return.
			if(lastval == $element.val()) return;

			$hidden.val('');
			$parent.removeClass('user-valid').addClass('user-invalid');
			lastval = $element.val();
		});

		// Initial load
		if($hidden.val()){
			$parent.addClass('user-valid');
		}
		else{
			$parent.addClass('user-invalid');
		}
		lastval = $element.val();

	});

	</script>{/script}

{/if}