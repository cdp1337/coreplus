var logintimer, $container, $btn, checkresult;

checkresult = function(result){
	var $error, $result;
	clearInterval(logintimer);

	// I need to determine if the result is the actual HTML or an object containing the response.
	if(result instanceof Object){
		$result = $(result.responseText);
	}
	else{
		$result = $(result);
	}

	// Was there an error here?
	// Was the page returned a login page?  If so it'll probably have an error and will
	// absolutely have an id... :p
	if($result.find('#user-login-placeholder-for-javascript-because-otherpages-may-have-an-error').length > 0){
		// Chances are it has an error.
		$error = $result.find('.message-error');
		$container.before($error);
		$container.replaceWith($result.find('#user-login'));
		initialize_form();
		return;
	}

	$btn.removeAttr('disabled').val('OK!');
	Core.Reload();
}

initialize_form = function(){
	$container = $('#user-login');
	$btn = $container.find('input[type=submit]');

	$container.find('form').ajaxForm({
		beforeSubmit: function(){
			logintimer = setInterval(function(){
				if($btn.val() == 'Processing ....'){
					$btn.val('Processing ');
				}
				else{
					$btn.val($btn.val() + '.');
				}
			}, 250);

			$('.message-error').fadeOut('slow', function(){ $(this).remove(); });
			$btn.attr('disabled', 'disabled').val('Processing ');
		},
		success: checkresult,
		// If a 403 header was sent on the initial page load, it'll be retrieved as an error.
		// Damn jquery
		error: checkresult
	});
};


$(function(){
	// The login input should have focus by default.
	$('#formtextinput-email').focus();
	// Initialize the ajax submission form.
	initialize_form();
});
