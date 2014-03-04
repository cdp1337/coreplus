{script library="jquery"}{/script}

<div class="{$element->getClass()} {$element->get('id')} clearfix">

	<label class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>


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

			<option value="advanced" {if $advanced_type == 'advanced'}selected="selected"{/if}>
				Other...
			</option>
		</select>

		<div class="formradioinput {$dynname}_advanced" style="display:none;">
			<label><input type="radio" name="{$dynname}_type" value="whitelist"/>Allow Only...</label>
			<label><input type="radio" name="{$dynname}_type" value="blacklist"/>Disallow Only...</label>
		</div>
		<div class="formcheckboxesinput {$dynname}_advanced" style="display:none;">
			{foreach from=$groups item='g'}
				<label>
					<input type="checkbox" name="{$dynname}[]" value="{$g->get('id')}" {if $g->get('checked')}checked="checked"{/if}/>{$g->get('name')}
				</label>
			{/foreach}
		</div>

		<div class="clear"></div>
	</div>

	<p class="form-element-description">{$element->get('description')}</p>

	<script type="text/javascript">
		$(function () {
			$('input[name="{$element->get('name')}"][value="advanced"]').closest('fieldset').show();
			{if $advanced_type}
				$('input[name="{$dynname}_type"][value="{$advanced_type}"]').click();
			{/if}

			{if $main_checked}
				$('input.{$dynname}_main[value={$main_checked}]').click();
			{/if}
		});
		$('.{$dynname}_main').change(function () {
			var $this = $(this),
					v = $this.val();
			if (v == 'advanced') {
				$('.{$dynname}_advanced').show();

			}
			else {
				$('.{$dynname}_advanced').hide();
			}
		});
	</script>
</div>