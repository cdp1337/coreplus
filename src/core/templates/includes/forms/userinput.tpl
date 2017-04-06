<div class="{$element->getClass()} {$element->get('id')} clearfix">
	<label class="form-element-label" for="{$element->get('id')}-ac">
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

	<div class="user-input-wrapper form-element-value">
		{if $can_lookup}
			<div class="user-indicator">
				<span class="user-valid-indicator" title="User is valid!">
					<i class="icon icon-ok"></i>
				</span>
				<span class="user-invalid-indicator" title="No user account linked.">
					<i class="icon icon-exclamation-circle"></i>
				</span>
			</div>

			<input type="text"{$element->getInputAttributes()} id="{$element->get('id')}-ac" value="{$username}"/>
			<input type="hidden" name="{$element->get('name')}" id="{$element->get('id')}" value="{$element->get('value')}"/>
		{else}
			<input type="text"{$element->getInputAttributes()} name="{$element->get('name')}" id="{$element->get('id')}-ac" value="{$element->get('value')}"/>
		{/if}
	</div>
</div>

{if $can_lookup && Core::IsLibraryAvailable('jqueryui')}

	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="Core.Strings"}{/script}
	{script location="foot"}<script>
		$(function(){

			var
				elementid = "{$element->getID()}-ac", hiddenid = "{$element->getID()}",
				$element, $hidden, $parent, lastval, inactive;

			$element = $('#' + elementid);
			$hidden = $('#' + hiddenid);
			$parent = $element.closest('.formelement');

			inactive = $element.data('include-inactive') ? $element.data('include-inactive') : 0;

			// Gogo autocomplete!
			$element.autocomplete({
				source: Core.ROOT_URL + 'form/pagemetas/autocompleteuser.ajax?inactive=' + inactive,
				minLength: 2,
				select: function( event, ui ) {

					if(ui.item){
						// This is a bit different because the value is actually going to a different field.
						$hidden.val(ui.item.id);
						$(this).val(ui.item.label);
						$parent.removeClass('user-invalid').addClass('user-valid');
						$hidden.data('is-valid', true);
						lastval = $element.val();

						// Trigger any change watcher on the hidden input, just in case there is one.
						$hidden.trigger('change');

						// The return false is to prevent jqueryui from setting the value to the id of the user.
						// I want the label instead, (set above).
						return false;
					}
					else{
						// Just clear out the user id.
						$hidden.val('');
						$parent.removeClass('user-valid').addClass('user-invalid');
						$hidden.data('is-valid', false);
						lastval = $element.val();

						// Trigger any change watcher on the hidden input, just in case there is one.
						$hidden.trigger('change');
					}
				}
				// ui-autocomplete-loading
			});

			// On changing the username, the authorid should be blanked out automatically!
			//$element.change(function(){
			$element.keyup(function(){
				var ev = $element.val();

				// The key did not cause a change, just return.
				if(lastval == ev) return;
				lastval = ev;

				if(ev.indexOf('@') != -1 && ev.indexOf('.') != -1){
					// Allow entering an email address instead.
					$hidden.val(ev);
					$parent.removeClass('user-invalid').addClass('user-valid');
					$hidden.data('is-valid', false);

					// Trigger any change watcher on the hidden input, just in case there is one.
					$hidden.trigger('change');
				}
				else{
					$hidden.val('');
					$parent.removeClass('user-valid').addClass('user-invalid');
					$hidden.data('is-valid', false);

					// Trigger any change watcher on the hidden input, just in case there is one.
					$hidden.trigger('change');
				}
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