{**
 * The shared template for article creation and updates.
 *}

{$form->render('head')}
{$form->render('body')}


{if Core::IsComponentAvailable('facebook')}
	{script library="facebook"}{/script}
	{script library="jquery"}{/script}
	{css src="assets/css/facebook.css"}{/css}


	{script location="foot"}<script type="text/javascript">
		var $fbpostsel = $('#formselectinput-facebook_post'),
			$authbutton = $('<a href="#" class="facebook-connect facebook-button" onclick="authFacebook(); return false;">Authorize Facebook</a>');

		$fbpostsel.before($authbutton);
		$fbpostsel.hide();

		Core.FB.onReady(function(){
			if(Core.FB.access_token){
				FB.api('/me/permissions?access_token=' + Core.FB.access_token, function(response) {
					if(response.error){
						console.log(response);
					}
					else{
						displayPostOptions();
					}
				});
			}
		});

		$('.facebook-post-to-select').change(function(){
			changeCheck();
		});

		$('#formselectinput-model-status').change(function(){
			changeCheck();
		});

		function changeCheck(){
			var $fbpostsel = $('#formselectinput-facebook_post'),
				fbval = $fbpostsel.length ? $fbpostsel.val() : '',
				$publishedsel = $('#formselectinput-model-status'),
				pubval = $publishedsel.val();


			if(pubval == 'draft' && fbval != ''){
				if(confirm('By publishing this to Facebook, the status will automatically be changed to "Published".  Ok?')){
					$publishedsel.val('published');
				}
				else{
					$fbpostsel.val('')
				}
			}
		}

		function displayPostOptions(){
			var $fbpostsel = $('#formselectinput-facebook_post');

			$('.facebook-connect').hide();
			$fbpostsel.show();
			$('.post-option-self').attr('value', Core.FB.id + ':' + Core.FB.access_token);

			// Get a list of pages to post to
			FB.api('/me/accounts?access_token=' + Core.FB.access_token, function(response) {
				if(response.error){
					console.log(response);
				}
				else{
					// Remove all options to start.
					$fbpostsel.html('');

					// Now, append the first option.
					$fbpostsel.append('<option value="">-- Do Not Post --</option>');

					// And the "my" facebook feed.
					$fbpostsel.append('<option value="__self__">Post to my wall feed</option>');

					for(i=0; i<response.data.length; i++){
						// Applications cannot be posted to, silly rabbit!
						if(response.data[i].category == 'Application') continue;

						// See if this user can post even.
						if(response.data[i].perms.indexOf('CREATE_CONTENT') == -1) continue;

						$fbpostsel.append('<option value="' + response.data[i].id + ':' + response.data[i].access_token + '">Post to ' + response.data[i].name + '</option>');
					}
				}
			});
		}

		function authFacebook() {
			if(!Core.FB.ready){
				alert('Facebook has not loaded yet, have you configured it yet?');
				return false;
			}

			FB.login(function(response) {
				if (response.authResponse) {
					// response.authResponse.accessToken
					// response.authResponse.userID
					$.ajax({
						url: Core.ROOT_URL + 'user/linkfacebook.json',
						type: 'POST',
						data: {
							token: response.authResponse.accessToken,
							id: response.authResponse.userID
						}
					});

					Core.FB.access_token = response.authResponse.accessToken;
					Core.FB.id = response.authResponse.userID;

					displayPostOptions();

					console.log(response);
				} else {
					console.log('User cancelled login or did not fully authorize.');
				}
			}, { scope: 'manage_pages, publish_actions, publish_stream' });
		}

		$('#formfileinput-model-image-action-upload input').attr('size','5');

	</script>{/script}
{/if}

{if $article->exists()}
	<input type="submit" value="Update Article"/>
{else}
	<input type="submit" value="Create Article"/>
{/if}

{$form->render('foot')}