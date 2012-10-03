<fieldset id="user-login">
	<legend> Login</legend>
	{$form->render()}
	<br/>
	{a href="/User/ForgotPassword"}Forgot Password{/a}
	{if $allowregister}
		<br/><br/>
		{a href="/User/Register"}Register Account{/a}
	{/if}
</fieldset>


{if $smarty.const.FACEBOOK_APP_ID && in_array('facebook', $backends)}
	<p>OR</p>
	<div id="fb-root"></div>
	<fieldset>
		<legend> Connect from Facebook</legend>

		<div id="facebook-connecting-section" style="display:none;"></div>
		<a href="#" scope="email" style="display:none" id="facebook-login-button">
			Login with Facebook
		</a>

		<noscript>
			<a href="{$facebooklink}">Login with Facebook</a>
		</noscript>

		<form action="{link link='/User/Login'}" method="POST" id="facebook-login-form">
			<input type="hidden" name="login-method" value="facebook"/>
			<input type="hidden" name="access-token"/>
		</form>

	</fieldset>

	{script library="jquery"}{/script}
	{script library="facebook"}{/script}
	{script src="js/user/login.js"}{/script}
{/if}

{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	<script>
		var logintimer, $container, $btn;

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
				success: function(result){
					var $error;
					clearInterval(logintimer);

					// Was there an error here?
					$error = $(result).find('.message-error');
					if($error.length > 0){
						$container.before($error);
						$container.replaceWith($(result).find('#user-login'));
						initialize_form();
						return;
					}

					$btn.removeAttr('disabled').val('OK!');
					Core.Reload();
				}
			});
		}

		initialize_form();
	</script>
{/if}

{script location="foot"} document.getElementById('formtextinput-email').focus(); {/script}