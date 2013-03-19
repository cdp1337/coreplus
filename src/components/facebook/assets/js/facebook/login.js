facebook_login = function(access_token){
	$('input[name="access-token"]').val(access_token);
	$('#facebook-login-form').submit();

	$('#facebook-login-button').html('Logging in...');
}



$(function(){
	// Because the facebook link is a javascript-powered widget... only
	// show it if javascript is enabled.
	$('#facebook-connecting-section').html('Connecting...').show();

	var waitforfbcounter = 0,
		waitforfb;

	waitforfb = setInterval(function(){
		waitforfbcounter++;

		if(Core.FB.ready){
			clearInterval(waitforfb);

			$('#facebook-connecting-section').hide();

			// User already logged in?  That's all then!
			if(Core.User.authenticated) return;

			$('#facebook-login-button').show().click(function(){
				console.log('clicked the facebook login button.  here we GO!');

				var scope = $(this).attr('scope');
				if(!scope) scope = 'email'; // Default.

				FB.login(function(response) {
					if (response.authResponse) {
						console.log('Welcome!  Fetching your information.... ');
						facebook_login(response.authResponse.accessToken);
					}
					else {
						console.log('User cancelled login or did not fully authorize.');
					}
				},  {scope: scope});

				return false;
			});

			/*
			 FB.Event.subscribe('auth.authResponseChange', function(response){
			 console.log('auth.authResponseChange', response);
			 console.log(clickedbutton);
			 // Only log the user in if they actually clicked the button!
			 if(!clickedbutton) return;

			 if(response.authResponse){
			 facebook_login(response.authResponse.accessToken);
			 return;
			 }

			 });
			 */
			return;
		}

		// only wait for so long.
		// 30 iterations will be about 3 seconds at 100ms each.
		// This shouldn't be too low, because it also includes the actual FB
		// connection in the javascript API.
		if(waitforfbcounter > 32){
			$('#facebook-connecting-section').html('Unable to connect to Facebook.');
			clearInterval(waitforfb);
			return;
		}
	}, 250);

});