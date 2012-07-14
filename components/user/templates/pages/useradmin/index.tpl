{script library="jquery"}{/script}

<table class="listing">
	<tr>
		<th width="40"></th>
		<th>Email</th>
		<th>Active</th>
		<th width="80">&nbsp;</th>
	</tr>
	{foreach $users as $user}
		<tr userid="{$user.id}" class="user-entry">
			<td>
				{if $user->get('admin')}
					<i class="icon-key" title="Admin Account"></i>
				{/if}
				{if $user->get('backend') == 'datastore'}
					<i class="icon-hdd" title="Datastore Backend"></i>
				{/if}
				{if $user->get('backend') == 'facebook'}
					<i class="icon-facebook" title="Facebook Backend"></i>
				{/if}
			</td>

			<td>{$user->get('email')}</td>

			<td class="active-status" useractive="{$user.active}">
				<noscript>
					{if $user->get('active')}
						<i class="icon-ok" title="Activated"></i>
					{else}
						<i class="icon-exclamation-sign" title="Not Activated"></i>
					{/if}
				</noscript>
			</td>
			<td>
				<ul class="controls">
					<li class="edit">
						{a href="/user/edit/`$user.id`"}
							<i class="icon-edit"></i>
							<span>Edit</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>
	{/foreach}
</table>

<script>

	function update_user_table (){
		$('.listing .user-entry').each(function(){
			var $tr = $(this),
				$status = $tr.find('.active-status');

			if($status.attr('useractive') == '1'){
				$status.html('<a href="#" class="user-activate-link" title="Activated"><i class="icon-ok"></i></a>');
			}
			else{
				$status.html('<a href="#" class="user-activate-link" title="Not Activated"><i class="icon-exclamation-sign"></i></a>');
			}
		});
	}

	$(function(){
		// Update the table first of all.
		update_user_table();

		$('.listing').on('click', '.user-activate-link', function(){
			var $status = $(this).closest('.active-status'),
				$tr = $(this).closest('tr');

			$.ajax({
				url: Core.ROOT_URL + 'useradmin/activate.json',
				data: {
				      user: $tr.attr('userid'),
				      status: ($status.attr('useractive') != '1') // It needs to be whatever it's currently not...
				},
				dataType: 'json',
				type: 'post',
				success: function(d){
					$status.attr('useractive', d.active);
					update_user_table();
				}
			});
		});
	});
</script>