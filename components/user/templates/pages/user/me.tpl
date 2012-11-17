<div id="user-edit-listing" style="display:none;">
	<table>
		<tr>
			<td>
				{img src="public/user/`$user->get('avatar')`" placeholder="person" width="150" height="300"}
			</td>
		</tr>
	</table>
	[ @todo show some useful information here. ]
	<br/><br/>
	<a href="#" class="edit-user-toggle">Edit Account</a>
	{a href="/user/password"}Manage Password{/a}
</div>


<div id="user-edit-form">
	{$form->render()}
	<!--<a href="#" class="edit-user-toggle">Cancel</a>-->
</div>


<script>
	$(function(){
		$('#formfileinput-avatar-action-upload input').attr('size','5');
		$('.edit-user-toggle').click(function(){
			$('#user-edit-form').toggle();
			$('#user-edit-listing').toggle();

			return false;
		});
	});
</script>