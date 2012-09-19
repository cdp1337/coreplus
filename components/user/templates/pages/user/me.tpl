<div id="user-edit-listing">
	<table>
		<tr>
			<td>
				{img src="`$user->get('avatar')`" placeholder="person" width="150" height="300"}
			</td>
		</tr>
	</table>
	[ @todo show some useful information here. ]
	<br/><br/>
	<a href="#" class="edit-user-toggle">Edit Account</a>
	{a href="/user/password"}Manage Password{/a}
</div>


<div id="user-edit-form" style="display:none;">
	{$form->render()}
	<a href="#" class="edit-user-toggle">Cancel</a>
</div>


<script>
	$(function(){
		$('.edit-user-toggle').click(function(){
			$('#user-edit-form').toggle();
			$('#user-edit-listing').toggle();

			return false;
		});
	});
</script>