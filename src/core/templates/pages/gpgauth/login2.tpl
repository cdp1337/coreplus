<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_LOGIN2_COPY_PASTE_COMMAND_TO_LOGIN'}
	Run the following command with GPG and your private key and paste in the results.
	This will verify that you have access to the private key.
	<br/><br/>
	You have approximately 5 minutes before this login expires.
</p>

<div class="configure-command">
<pre><code class="shell">{$cmd}</code></pre>
</div>

<hr/>
<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_LOGIN2_ALT_UPLOAD'}
</p>
{$form->render()}

{css}<style>
	div.CodeMirror {
		height: auto;
	}
</style>{/css}
{script library="codemirror_shell"}{/script}

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
						// The controller will see that it's complete and login the user.
						// I can't do that from javascript, silly user!
						// Why are you even reading this comment?  Get back to work!
						window.location.reload();
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
		}, 3000
	);

	$(function() {
		var $code = $('.configure-command'),
			$pre = $code.find('code');

		CodeMirror(
			$code[0],
			{
				value: $pre.text(),
				readOnly: true
			}
		);

		$pre.hide();
	});
</script>{/script}