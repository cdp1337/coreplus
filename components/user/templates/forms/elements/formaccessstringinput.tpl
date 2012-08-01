{script library="jquery"}{/script}

<div class="{$element->getClass()}">
{if $element->get('title')}
	<span>{$element->get('title')|escape}</span>
{/if}

{if $element->get('description')}
	<p class="formdescription">{$element->get('description')}</p>
{/if}
	<div>
		<label>
			<input type="radio" name="{$element->get('name')}" class="{$dynname}_main"
			       value="basic_anyone" {if $main_checked == 'basic_anyone'}checked="checked"{/if}/>
			Allow Anyone
		</label>
		<label>
			<input type="radio" name="{$element->get('name')}" class="{$dynname}_main"
			       value="basic_anonymous" {if $main_checked == 'basic_anonymous'}checked="checked"{/if}/>
			Only Anonymous
		</label>
		<label>
			<input type="radio" name="{$element->get('name')}" class="{$dynname}_main"
			       value="basic_authenticated" {if $main_checked == 'basic_authenticated'}checked="checked"{/if}/>
			Only Authenticated
		</label>
		<label>
			<input type="radio" name="{$element->get('name')}" class="{$dynname}_main"
			       value="basic_admin" {if $main_checked == 'basic_admin'}checked="checked"{/if}/>
			Only Administrators
		</label>
	</div>
	<!-- This fieldset is hidden from browsers that may not have JS enabled. -->
	<fieldset style="display:none;">
		<legend>
			<label>
				<input type="radio" name="{$element->get('name')}" class="{$dynname}_main"
				       value="advanced" {if $advanced_type == 'advanced'}checked="checked"{/if}/>
				Advanced...
			</label>
		</legend>
		<div class="formelement formradioinput {$dynname}_advanced" style="display:none;">
			<label><input type="radio" name="{$dynname}_type" value="whitelist"/>Allow Only...</label>
			<label><input type="radio" name="{$dynname}_type" value="blacklist"/>Disallow Only...</label>
		</div>
		<div class="formelement formcheckboxinput {$dynname}_advanced" style="display:none;">
		{foreach from=$groups item='g'}
			<label>
				<input type="checkbox" name="{$dynname}[]" value="{$g->get('id')}" {if $g->get('checked')}checked="checked"{/if}/>{$g->get('name')}
			</label>
		{/foreach}
		</div>
	</fieldset>


	<script type="text/javascript">
		$(function () {
			$('input[name="{$element->get('name')}"][value="advanced"]').closest('fieldset').show();
		{if $advanced_type}
			$('input[name={$dynname}_type][value={$advanced_type}]').click();
		{/if}

		{if $main_checked}
			$('input.{$dynname}_main[value={$main_checked}]').click();
		{/if}
		});
		$('.{$dynname}_main').click(function () {
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