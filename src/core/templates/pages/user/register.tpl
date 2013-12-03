{css src="assets/css/user.css"}{/css}

{$form->render()}


{if $use_contexts}
	{include file="includes/user/edit_create_contextgroups.tpl"}
{/if}