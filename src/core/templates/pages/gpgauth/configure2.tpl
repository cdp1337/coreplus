<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_CONFIGURE2_CHECK_EMAIL_FOR_INSTRUCTIONS_NOTE_IS_ENCRYPTED'}
</p>

<hr/>
<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_CONFIGURE2_ALT_UPLOAD'}
</p>
{$form->render()}

{script location="foot"}<script>
	// Check the server to see if the key has been uploaded successfully.
	var interval, conn = null;

	interval = setInterval(
		function(){
			// Clear out the previous connection.
			if(conn !== null){
				conn.abort();
			}
			conn = $.ajax({
				url: Core.ROOT_URL + 'gpgauth/jsoncheck/{$nonce}',
				method: 'GET',
				data: 'json',
				success: function(r){
					if(typeof(r.status) === 'undefined'){
						alert('Unknown return status from server!  Try submitting the form manually?');
						console.log(r);
						conn = null;
						return;
					}

					if(r.status === 'complete'){
						window.location = Core.ROOT_URL;
					}

					console.log(r);
					conn = null;
				},
				error: function(xhr, e){
					console.log('ERROR!');
					console.log(e);
					conn = null;
				}
			});
		}, 5000
	);
</script>{/script}