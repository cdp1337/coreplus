{script library="jquery"}{/script}

<div class="{$element->getClass()} {$element->get('id')} clearfix">

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

	<div class="form-element-value">
		<select id="{$element->get('id')}" name="{$element->get('name')}" class="{$dynname}_main">
			<option value="basic_anyone" {if $main_checked == 'basic_anyone'}selected="selected"{/if}>
				Allow Anyone
			</option>

			<option value="basic_anonymous" {if $main_checked == 'basic_anonymous'}selected="selected"{/if}>
				Allow Only Anonymous
			</option>

			<option value="basic_authenticated" {if $main_checked == 'basic_authenticated'}selected="selected"{/if}>
				Allow Only Authenticated
			</option>

			<option value="basic_admin" {if $main_checked == 'basic_admin'}selected="selected"{/if}>
				Allow Only Administrators
			</option>

			<option value="advanced" {if $main_checked == 'advanced'}selected="selected"{/if}>
				Other...
			</option>
		</select>

		<div class="formradioinput {$dynname}_advanced" style="display:none;">
			<label><input type="radio" name="{$dynname}_type" {if $advanced_type == 'whitelist' || $main_checked != 'advanced'}checked="checked"{/if} value="whitelist"/>Allow Only...</label>
			<label><input type="radio" name="{$dynname}_type" {if $advanced_type == 'blacklist'}checked="checked"{/if} value="blacklist"/>Disallow Only...</label>
		</div>
		<div class="formcheckboxesinput {$dynname}_advanced" style="display:none;">
			{foreach from=$groups item='g'}
				<label>
					<input type="checkbox" name="{$dynname}[]" value="{$g->get('id')}" {if $g->get('checked')}checked="checked"{/if}/>{$g->get('name')}
				</label>
			{/foreach}
		</div>
	</div>

	<script type="text/javascript">
		$(function () {
			{if $main_checked == 'advanced'}
				/*
				 * When the page loads, display the advanced options if that is requested by the controller.
				 * 
				 * This provides the expected behaviour to the end user of advanced options displaying by default
				 * when that is the value set from the controller.
				 */
				$('.{$dynname}_advanced').show();
			{/if}
		});
		
		// Function to show/hide the advanced options when Other... is selected.
		$('.{$dynname}_main').change(function () {
			if ($(this).val() == 'advanced') {
				$('.{$dynname}_advanced').show();
			}
			else {
				$('.{$dynname}_advanced').hide();
			}
		});
	</script>
</div>