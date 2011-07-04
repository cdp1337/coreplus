{if !sizeof($changes)}
	<p>No changes required!</p>
{else}
	<ul>
		{foreach from=$changes item='change'}
			<li>{$change}</li>
		{/foreach}
	</ul>
{/if}
