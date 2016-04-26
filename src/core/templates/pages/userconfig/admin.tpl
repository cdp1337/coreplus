{**
 * The template for administrators to edit user configuration options on the site.
 * ie: being able for SA to set which options show to users, the titles for them, etc.
 *}

{$configform->render('head')}

<div id="tabs-group">
	<ul>
		<li>
			<a href="#user-config-options" class="formtabsgroup-tab-link"><span>{t 'STRING_CONFIGURATION'}</span></a>
		</li>
		<li>
			<a href="#user-config-register-elements" class="formtabsgroup-tab-link"><span>Register Elements Enabled</span></a>
		</li>
		<li>
			<a href="#user-config-edit-elements" class="formtabsgroup-tab-link"><span>Edit Elements Enabled</span></a>
		</li>
		<li>
			<a href="#user-auth-sources">Authentication Sources</a>
		</li>
	</ul>
	
	<div id="user-config-options">
		{$configform->render('body')}
	</div>

	<div id="user-config-register-elements">
		<p class="message-tutorial">
			Select the elements to display on the registration page for new user signups.<br/><br/>
			You can also rearrange the order that they display in.
		</p>
		
		<table class="user-config-sortable-table">
			<tbody>
				{foreach $on_register_elements as $config}
					<tr class="sortable">
						<td width="50">
							<i class="icon-move" title="Drag to Rearrange" style="display:none;"></i>
						</td>
						<td>
							<label>
								<input type="checkbox" name="onregister[]" value="{$config.key}" {if $config.checked}checked="checked"{/if}/>
								{$config.title}
							</label>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div id="user-config-edit-elements">
		<p class="message-tutorial">
			Select the elements to display on the edit page for users.<br/><br/>
			You can also rearrange the order that they display in.
		</p>
		
		<table class="user-config-sortable-table">
			<tbody>
				{foreach $on_edit_elements as $config}
					<tr class="sortable">
						<td width="50">
							<i class="icon-move" title="Drag to Rearrange" style="display:none;"></i>
						</td>
						<td>
							<label>
								<input type="checkbox" name="onedit[]" value="{$config.key}" {if $config.checked}checked="checked"{/if}/>
								{$config.title}
							</label>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div id="user-auth-sources">
		<p class="message-tutorial">
			Select what authentication sources are enabled for this site.
		</p>
		
		<table class="user-config-sortable-table">
			<tbody>
				{foreach $auth_backends as $backend}
					<tr class="sortable">
						<td width="50">
							<i class="icon-move" title="Drag to Rearrange" style="display:none;"></i>
						</td>
						<td>
							<label>
								<input type="checkbox" name="authbackend[]" value="{$backend.name}" {if $backend.enabled}checked="checked"{/if}/>
								{$backend.title}
							</label>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
	<br/>
	<input type="submit" value="Save Options"/>
{$configform->render('foot')}


{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$(function(){
		$('.user-config-sortable-table tbody').sortable({
			helper: function(e, tr) {
				var $originals = tr.children();
				var $helper = tr.clone();
				$helper.children().each(function(index)
				{
					// Set helper cell sizes to match the original sizes
					$(this).width($originals.eq(index).width());
				});
				return $helper;
			},
			handle: '.icon-move'
		});

		// Don't forget to update the UI to make it look like it can be sorted.
		$('.user-config-sortable-table .icon-move').show().css('cursor', 'move');
		
		$('#tabs-group').tabs();
	});
</script>{/script}