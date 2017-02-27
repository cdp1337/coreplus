{**
 * The template for administrators to edit user configuration options on the site.
 * ie: being able for SA to set which options show to users, the titles for them, etc.
 *}

<form action="" method="POST">
	<fieldset>
		<legend> User Config Options </legend>

		<table id="user-config-admin-table">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th>Name/Title</th>
					<th>On Registration</th>
					<th>On Edit</th>
					<th>Required?</th>
				</tr>
			</thead>
			<tbody>
				{foreach $configs as $config}
					<tr class="sortable">
						<td>
							<i class="icon-move" title="Drag to Rearrange" style="display:none;"></i>
						</td>
						<td>
							<input type="text" name="name[{$config.key}]" value="{$config.name|escape}"/>
						</td>
						<td>
							<input type="checkbox" name="onregistration[{$config.key}]" {if $config.onregistration}checked="checked"{/if}/>
						</td>
						<td>
							<input type="checkbox" name="onedit[{$config.key}]" {if $config.onedit}checked="checked"{/if}/>
						</td>
						<td>
							<input type="checkbox" name="required[{$config.key}]" {if $config.required}checked="checked"{/if}/>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend> Configs </legend>

		{$configform->render('body')}
	</fieldset>


	<br/>
	<input type="submit" value="Save Options"/>
</form>


{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$(function(){
		$('#user-config-admin-table tbody').sortable({
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
		$('#user-config-admin-table .icon-move').show().css('cursor', 'move');
	});
</script>{/script}