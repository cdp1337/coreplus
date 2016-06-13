{$form->render()}

<table class="listing">
	{foreach $files as $f}
		<tr>
			<td>{$f.filename}</td>
			<td>
				{if $f.warning}
					{$f.warning}
				{else}
					<ul class="controls">
						<li>
							{a href="`$f.view_url`" title="t:STRING_VIEW"}
								<i class="icon icon-view"></i>
								<span>{t 'STRING_VIEW'}</span>
							{/a}
						</li>
						<li>
							{if $f.page->exists()}
								{a href="`$f.edit_url`" title="t:STRING_UPDATE"}
									<i class="icon icon-edit"></i>
									<span>{t 'STRING_UPDATE'}</span>
								{/a}
							{else}
								{a href="`$f.edit_url`" title="t:STRING_REGISTER_PAGE"}
									<i class="icon icon-plus"></i>
									<span>{t 'STRING_REGISTER_PAGE'}</span>
								{/a}
							{/if}
						</li>
					</ul>
				{/if}
			</td>
		</tr>
	{/foreach}
</table>