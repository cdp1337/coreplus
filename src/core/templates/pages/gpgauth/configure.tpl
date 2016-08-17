{if $current_key}
	<p class="message-success">
		{t 'MESSAGE_SUCCESS_GPGAUTH_GPG_KEY_S_CURRENTLY_ENABLED' $current_key|gpg_fingerprint}
	</p>
{/if}
<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_CONFIGURE_COPY_PASTE_COMMAND_TO_UPLOAD_PUBLIC_KEY'}
</p>

<div class="configure-command">
	<pre><code class="bash">{$cmd}</code></pre>	
</div>

<hr/>
<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_CONFIGURE_ALT_UPLOAD'}
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
						console.log('Unknown return status from server!');
						console.log(r);
						conn = null;
						return;
					}
					
					if(r.status === 'complete' && typeof(r.redirect) !== 'undefined'){
						// Yay!  Redirect to the next page.
						window.location = r.redirect;
					}
					else if(r.status === 'complete'){
						clearInterval(interval);
						alert('Upload complete, please check your email.');
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
		}, 10000
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