{if Core::IsLibraryAvailable('JQuery')}
	{script library="jquery"}{/script}
	{script library="jqueryui"}{/script}
	{script library="jquery.form"}{/script}
	{script location="foot" src="assets/js/user/login.js"}{/script}
{/if}

<div class="user-register">
	{$form->render()}
</div>
