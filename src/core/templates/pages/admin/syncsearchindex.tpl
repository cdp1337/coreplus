<p class="message-tutorial">
	Data on the site can be designated as searchable by the original developers.
	This makes use of several special indexes in Core which are kept updated when data is changed in real-time.
	However, sometimes this index can get out of sync or may not have been created to begin with, (data previous to 2.8.0).
	In that case, this page will re-index all that data for the search system to use.
	<br/><br/>
	Generally, you do not need to use this page but once in a blue moon, but it's here if you need it.
</p>

{if sizeof($changes)}
	<ul>
		{foreach $changes as $c}
			<li>Model {$c.name}: {$c.count} synced record(s).</li>
		{/foreach}
	</ul>
{else}
	<p class="message-info">
		There does not seem to be any indexable data present.
	</p>
{/if}