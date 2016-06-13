{if $filters->getTotalCount() == 0 && !$filters->hasSet()}
	<p class="message-info">No records found!</p>
{else}
	{$filters_rendered}
	{$pagination_rendered}

	{if $edit_form !== null}
		{$edit_form->render('head')}
		{$edit_form->render('body')}
	{/if}

	<table {$table_attributes}>
		<tr>
			{foreach $columns as $c}
				{** @var ListingTable/Column $c *}
				{$c->getTH()}
			{/foreach}

			{* One extra column for the control links. *}
			<th>
				<a href="#" class="control-column-selection" title="Show / Hide Columns"><i class="icon icon-columns"></i></a>

				{$controls->fetch()}

			</th>
		</tr>

		{if $edit_form !== null}
			<tr class="edit edit-record-buttons">
				<td colspan="{sizeof($columns) + 1}">
					<a href="#" class="control-edit-toggle button">Cancel</a>
					<input type="submit" value="Save Quick Edit"/>
				</td>
			</tr>
		{/if}
	{*
	NO end of table here, this will be a fragment for only rendering the top part of the table.
	</table>
	*}
{/if}
