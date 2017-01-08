<p class="message-info">
	{if $registerform}
		{t 'MESSAGE_PLEASE_LOG_IN_OR_REGISTER_TO_VIEW'}
	{else}
		{t 'MESSAGE_PLEASE_LOG_IN_TO_VIEW'}
	{/if}
</p>

<!--
	As you may have guessed by looking at the id...
	keep this line in here else your javascript login WILL BREAK!
-->
<div id="user-login-placeholder-for-javascript-because-otherpages-may-have-an-error"></div>

<div id="user-login-center" class="user-login-center">
	{assign var='jqueryAvailable' value=Core::IsLibraryAvailable('jquery-full')}
	
	{if $jqueryAvailable}
		{*
		 * Only display the UI tabs if jQuery is available.
		 *}
		<ul>
			{foreach $drivers as $name => $d}
				<li>
					<a href="#login-auth-{$name}">
						<i class="icon icon-{$d->getAuthIcon()}"></i>
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
		<fieldset id="user-login-register" class="user-login-register">

			<div class="user-login-section-heading clearfix">
				{t 'MESSAGE_REGISTER_A_NEW_ACCOUNT'}
			</div>

			<br/>

			{**
			 * An alternative to this if you so please is to do:
			 *
			 * {$drivers.datastore->renderRegister()}
			 * <some-markup/>
			 * {$drivers.facebook->renderRegister()}
			 *
			 * The default will simply render every authentication driver enabled on the system.
			 *}

			{foreach from=$drivers key=$name item='d' name='driver'}
				{** Only render the first driver in the list. *}
				{if $smarty.foreach.driver.first}
					<div class="user-register-include user-authdriver-{$name}">
						{$d->renderRegister()}
					</div>
				{/if}
			{/foreach}

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
