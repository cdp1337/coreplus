<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_REGISTER_COPY_PASTE_COMMAND'}
</p>
<div class="configure-command">
	<pre><code class="shell">{$cmd}</code></pre>
</div>

<hr/>
<p class="message-tutorial">
	{t 'MESSAGE_TUTORIAL_GPGAUTH_REGISTER_ALT_UPLOAD'}
</p>
<div class="user-register">
	{$form->render()}
</div>


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
						window.location.href = r.redirect;
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