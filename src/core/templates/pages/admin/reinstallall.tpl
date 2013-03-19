{if !sizeof($changes)}
	<p>No changes required!</p>
{else}

	{if sizeof($errors) > 0}
		{foreach $errors as $e}
			<p class="message-error">
				Error while processing {$e.type} {$e.name}: <br/>
				{$e.message}
			</p>
		{/foreach}
	{/if}
	<ul>
		{foreach from=$changes item='change'}
			<li>{$change}</li>
		{/foreach}
	</ul>
{/if}
