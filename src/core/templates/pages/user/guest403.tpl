<p class="message-info">
	Please login {if $registerform}or create an account {/if} to view this page.
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
			Login to your existing account.
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
				{$d->renderLogin(['orientation' => {$form_orientation}])}
			</div>
		{/foreach}
	</fieldset>

	{if $allowregister}
		<fieldset id="user-login-register" class="user-login-register">

			<div class="user-login-section-heading clearfix">
				Sign up for an account!
			</div>

			<br/>

			{$registerform->render()}

		</fieldset>
	{/if}
</div>

{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script src="assets/js/user/login.js"}{/script}
{/if}