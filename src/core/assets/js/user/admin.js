/**
 * Necessary javascript for admin-utilities in regards to the user system.
 */

/**
 * Used on /user/admin page and the user widgets.
 * This will update the user table with the appropriate status markup.
 */
function update_user_table (){
	$('.listing .user-entry').each(function(){
		var $tr = $(this),
			$status = $tr.find('.active-status');

		if($status.attr('data-useractive') == '1'){
			$status.html('<a href="#" class="user-activate-link" title="Activated"><i class="icon-ok"></i></a>');
		}
		else if($status.attr('data-useractive') == '-1'){
			$status.html('<a href="#" class="user-activate-link" title="Deactivated"><i class="icon-times"></i></a>');
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
			$tr = $(this).closest('tr'),
			status = $status.attr('data-useractive'),
			newstatus;

		if(status == 0){
			// The current status is inactivate, means the account is a new registration.
			// The expected behaviour should be to activate it.
			newstatus = 1;
		}
		else if(status == 1){
			// The current status is activate, so if the admin clicks on the link it should deactivate the user.
			newstatus = -1;
		}
		else{
			// The current status is deactivated, so re-activate the user on clicking it.
			newstatus = 1;
		}

		$.ajax({
			url: Core.ROOT_URL + 'user/activate.json',
			data: {
				user: $tr.attr('data-userid'),
				status: newstatus
			},
			dataType: 'json',
			type: 'post',
			success: function(d){
				$status.attr('data-useractive', newstatus);
				update_user_table();
			}
		});

		return false;
	});
});