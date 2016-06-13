<div id="user-edit-listing">
	
	<aside class="user-side-info">
		{include 'includes/user/view_sidebar.tpl'}

		<br/>
		<a href="#" class="edit-user-toggle">Edit Account</a>
		<br/><br/>	
	</aside>
	
	<section class="user-primary-info">
		{include 'includes/user/view_primarybar.tpl'}
		<hr/>
		
		{widgetarea name="User Large Widgets" installable="User" user=$user}
	</section>
</div>


<div id="user-edit-form" style="display:none;">
	{$form->render()}
	<br/>
	<a href="#" class="edit-user-toggle">Cancel</a>
</div>

<script>
	$(function(){
		//$('#formfileinput-avatar-action-upload input').attr('size','5');

		$('.edit-user-toggle').click(function(){
			$('#user-edit-form').toggle();
			$('#user-edit-listing').toggle();

			return false;
		});

		$('.reveal-hidden-value').click(function() {
			$($(this).attr('href')).toggle();
			return false;
		});
	});
</script>