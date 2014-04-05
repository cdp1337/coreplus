<div class="content-quickdraft-widget">
	<h3>Quick Draft</h3>

	{$form->render()}

	{if sizeof($drafts) > 0}
		<h3>Drafts</h3>
		{foreach $drafts as $page}
			<div class="draft-page">
				<span class="draft-page-title">
					{a href="`$page.editurl`"}
						{$page.title}
					{/a}
				</span>
				<span class="draft-page-updated">{date $page.updated}</span>
				{**
				 * Until I can determine a good way to retrieve only the content from the source application,
				 * (and not the many widgets/addons that may be on that application's body)
				 *}

				{*
				<span class="draft-page-preview">{$page->getTeaser(true)|truncate:200}</span>
				*}
				{a href="`$page.deleteurl`" confirm="Really delete `$page.title|escape`?" title="Delete Draft"}
					<i class="icon-times"></i>
				{/a}
			</div>
		{/foreach}
	{/if}
</div>