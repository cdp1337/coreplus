<fieldset class="{$group->getClass()}"{$group->getGroupAttributes()}>
	<legend> {$group->get('title')} </legend>
	{if $group->get('description')}
		<p class="formdescription">{$group->get('description')}</p>
	{/if}

	{$elements}

</fieldset>
