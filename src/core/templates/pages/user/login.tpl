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
			Existing Account
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
			{a class="register-account button" href="/user/register"}Sign up for an account!{/a}
		</fieldset>
	{/if}

</div>
