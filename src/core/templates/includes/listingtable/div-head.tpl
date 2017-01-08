{if $filters->getTotalCount() == 0 && !$filters->hasSet() && !$manual_records}
	<p class="message-info">No records found!</p>
{else}
	{$filters_rendered}
	{$pagination_rendered}

	{* Primary listing table start.  Yup, it's just a div! *}
	<div {$attributes}>
		
	{*
	NO end of div here, this will be a fragment for only rendering the top part of the table.
	</table>
	*}
{/if}
