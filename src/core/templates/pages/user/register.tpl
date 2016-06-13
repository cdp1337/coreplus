<div id="register-center" class="user-register">
	{**
	 * An alternative to this if you so please is to do:
	 *
	 * {$drivers.datastore->renderRegister()}
	 * <some-markup/>
	 * {$drivers.facebook->renderRegister()}
	 *
	 * The default will simply render every authentication driver enabled on the system.
	 *}
	
	{assign var='jqueryAvailable' value=Core::IsLibraryAvailable('jquery-full')}

	{if $jqueryAvailable}
		{*
		 * Only display the UI tabs if jQuery is available.
		 *}
		<ul>
			{foreach $drivers as $name => $d}
				<li>
					<a href="#register-auth-{$name}">
						<i class="icon icon-{$d->getAuthIcon()}"></i>
						<span>{$d->getAuthTitle()}</span>
					</a>
				</li>
			{/foreach}
		</ul>
	{/if}
	
	{foreach $drivers as $name => $d}
		<div class="user-register-include user-authdriver-{$name}" id="register-auth-{$name}">
			{$d->renderRegister()}
		</div>
	{/foreach}

	<hr/>
	
	<fieldset id="user-login-register" class="user-login-register">
		{a class="login-account" href="/user/login" class="button"}Already have an account?{/a}
	</fieldset>
	
</div>

{if $jqueryAvailable}
	{script library="jqueryui"}{/script}
	{script location="foot"}<script>
		$(function() {
			$('#register-center').tabs();
		});
	</script>{/script}	
{/if}
