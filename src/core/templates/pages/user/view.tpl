<div id="user-edit-listing">
	
	<aside class="user-side-info">
		{include 'includes/user/view_sidebar.tpl'}
	</aside>

	<section class="user-primary-info">
		{include 'includes/user/view_primarybar.tpl'}
		<hr/>

		{widgetarea name="User Large Widgets" installable="User" user=$user}
	</section>
</div>
