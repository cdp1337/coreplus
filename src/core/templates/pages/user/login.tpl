<div id="user-login-center" class="user-login-center">
	{**
	 * An alternative to this if you so please is to do:
	 *
	 * {$drivers.datastore->renderLogin()}
	 * <some-markup/>
	 * {$drivers.facebook->renderLogin()}
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
					<a href="#login-auth-{$name}">
						<i class="icon-{$d->getAuthIcon()}"></i>
						<span>{$d->getAuthTitle()}</span>
					</a>
				</li>
			{/foreach}
		</ul>
	{/if}

	{foreach $drivers as $name => $d}
		<div class="user-login-include user-authdriver-{$name}" id="login-auth-{$name}">
			{$d->renderLogin()}
		</div>
	{/foreach}

	{if $allowregister}

		<hr/>
		
		<fieldset id="user-login-register" class="user-login-register">
			{a class="register-account button" href="/user/register"}Sign up for an account!{/a}
		</fieldset>
	{/if}
</div>

{if $jqueryAvailable}
	{script library="jqueryui"}{/script}
	{script location="foot"}<script>
		$(function() {
			$('#user-login-center').tabs();
		});
	</script>{/script}
{/if}