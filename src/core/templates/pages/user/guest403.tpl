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
	{if sizeof($drivers) > 1}
		{assign var='column_count' value='col-2'}
		{assign var='form_orientation' value='vertical'}
	{else}
		{assign var='column_count' value='col-1'}
		{assign var='form_orientation' value='horizontal'}
	{/if}

	<fieldset id="user-login-existing" class="user-login-existing clearfix {$column_count}">
		<div class="user-login-section-heading clearfix">
			{t 'MESSAGE_LOG_IN_TO_EXISTING_ACCOUNT'}
		</div>

		{**
		 * An alternative to this if you so please is to do:
		 *
		 * {$drivers.datastore->renderLogin()}
		 * <some-markup/>
		 * {$drivers.facebook->renderLogin()}
		 *
		 * The default will simply render every authentication driver enabled on the system.
		 *}

		{foreach $drivers as $name => $d}
			<div class="user-login-include user-authdriver-{$name}">
				<div class="user-authdriver-title">{$d->getAuthTitle()}</div>
				{$d->renderLogin(['orientation' => {$form_orientation}])}
			</div>
		{/foreach}
	</fieldset>

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

{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}