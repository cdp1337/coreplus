{$form->render('head')}
{$form->render('body')}

{if Core::IsComponentAvailable('facebook')}
	{script library="facebook"}{/script}
	{script library="jquery"}{/script}

	<fieldset>
		<legend> Facebook Integration </legend>
		<div class="facebook-connect">
			<a href="#" class="button" onclick="authFacebook(); return false;">Authorize Facebook</a>
		</div>
		<div class="facebook-post" style="display:none;">
			Post to facebook:
			<select class="facebook-post-to-select" name="facebook_post">
				<option value="">-- Do not post --</option>
				<option value="" class="post-option-self">My Wall Feed</option>
			</select>
		</div>
	</fieldset>


	{script location="foot"}<script type="text/javascript">
		Core.FB.onReady(function(){
			if(Core.FB.access_token){
				FB.api('/me/permissions?access_token=' + Core.FB.access_token, function(response) {
					if(response.error){
						console.log(response);
					}
					else{
						displayPostOptions();
						// Has the user allowed the necessary permissions already?
						//if(typeof response.data[0].publish_stream != 'undefined' && response.data[0].publish_stream){
//
//						}
					}
				});
			}
		});

		function displayPostOptions(){
			$('.facebook-connect').hide();
			$('.facebook-post').show();
			$('.post-option-self').attr('value', Core.FB.id + ':' + Core.FB.access_token);

			// Get a list of pages to post to
			FB.api('/me/accounts?access_token=' + Core.FB.access_token, function(response) {
				if(response.error){
					console.log(response);
				}
				else{
					for(i=0; i<response.data.length; i++){
						// Applications cannot be posted to, silly rabbit!
						if(response.data[i].category == 'Application') continue;

						// See if this user can post even.
						if(response.data[i].perms.indexOf('CREATE_CONTENT') == -1) continue;

						$('.facebook-post-to-select').append('<option value="' + response.data[i].id + ':' + response.data[i].access_token + '">' + response.data[i].name + '</option>');
					}
				}
			});
		}

		function authFacebook() {
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

	</script>{/script}
{/if}

<input type="submit" value="Create Article"/>

{$form->render('foot')}