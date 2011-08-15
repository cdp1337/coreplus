facebook_login = function(access_token){
	$('input[name="access-token"]').val(access_token);
	$('#facebook-login-form').submit();
	
	$('#facebook-login-button').html('Logging in...');
}



$(function(){
	// Because the facebook link is a javascript-powered widget... only
	// show it if javascript is enabled.
	$('#facebook-connecting-section').html('Connecting to Facebook.').show();
	
	var waitforfbcounter = 0;
	var waitforfb = setInterval(function(){
		waitforfbcounter++;
		
		if(typeof FB != 'undefined' && FB._userStatus != 'unknown'){
			clearInterval(waitforfb);
			
			$('#facebook-connecting-section').hide();
			$('#facebook-login-button').show();
			
			FB.Event.subscribe('auth.login', function(response) {
				//console.log(response);

				if(response.status == 'connected'){
					facebook_login(response.authResponse.accessToken);
					return;
				}
			});

			// Is the user already logged in?
			FB.getLoginStatus(function(response){
				if(response.status == 'connected'){
					// Override the login button.
					$('#facebook-login-button').click({ accessToken:response.authResponse.accessToken }, function(e){
						facebook_login(e.data.accessToken);
						return false;
					});
				}
			});
			
			return;
		}
		
		// only wait for so long.
		// 30 iterations will be about 3 seconds at 100ms each.
		// This shouldn't be too low, because it also includes the actual FB 
		// connection in the javascript API.
		if(waitforfbcounter > 30){
			$('#facebook-connecting-section').html('Unable to contact Facebook.');
			clearInterval(waitforfb);
			return;
		}
		
		// Show some minimal amount of user feedback.
		if(waitforfbcounter % 5 == 0){
			$('#facebook-connecting-section').append('.');
		}
	}, 100);
	
});