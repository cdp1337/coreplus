{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script location="foot" src="assets/js/user/login.js"}{/script}

	{if $is_manager}
		{script location="foot"}<script>
			$(function() {
				var $pwgen = $('#formcheckboxinput-pwgen'),
					$pwfields = $('.formpasswordinput-pass, .formpasswordinput-pass2');

				$pwgen.click(function() {
					if($(this).is(':checked')){
						// Hide the password fields.
						$pwfields.hide();
					}
					else{
						$pwfields.show();
					}
				});
			});
		</script>{/script}
	{/if}
{/if}

<div class="user-register">
	{$form->render()}
</div>
