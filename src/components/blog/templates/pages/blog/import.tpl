<h2>Overview</h2>
<ul>
	<li>Added: {$added}</li>
	<li>Updated: {$updated}</li>
	<li>Skipped: {$skipped}</li>
	<li>Deleted: {$deleted}</li>
</ul>

<h2>Changelog</h2>
{if $changelog}
	{$changelog}
{else}
	No changes
{/if}
