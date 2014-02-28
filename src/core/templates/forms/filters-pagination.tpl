
<div class="pagination screen">
	<div class="pagination-pager">

		{if $page_current > 1}
			<a href="?page=1" title="First Page">
				<i class="icon-fast-backward"></i>
			</a>
			<a href="?page={$page_current-1}" title="Page {$page_current-1}">
				<i class="icon-backward"></i>
			</a>
		{else}
			<a href="?page=1" title="First Page" class="disabled">
				<i class="icon-fast-backward"></i>
			</a>
			<a href="?page=1" title="Page 1" class="disabled">
				<i class="icon-backward"></i>
			</a>
		{/if}

		Page {$page_current}

		{if $page_current < $page_max}
			<a href="?page={$page_current+1}" title="Page {$page_current+1}">
				<i class="icon-forward"></i>
			</a>
			<a href="?page={$page_max}" title="Last Page">
				<i class="icon-fast-forward"></i>
			</a>
		{else}
			<a href="?page={$page_max}" title="Page {$page_current+1}" class="disabled">
				<i class="icon-forward"></i>
			</a>
			<a href="?page={$page_max}" title="Last Page" class="disabled">
				<i class="icon-fast-forward"></i>
			</a>
		{/if}
	</div>

	{if $records_total > $records_current}
		Displaying {$records_start}-{$records_end} of {$records_total} records.
	{/if}


	<div class="pagination-list">
		Page:
		{assign var='prev' value=null}

		{**
		 * This is the new automatic jump system.
		 * Only 10 page jumps at most are displayed on the interface.
		 *}
		{if $page_max > 100000}
			{assign var='jumpcount' value=($page_max/50000)|floor*5000}
		{elseif $page_max > 10000}
			{assign var='jumpcount' value=($page_max/5000)|floor*500}
		{elseif $page_max > 1000}
			{assign var='jumpcount' value=($page_max/500)|floor*50}
		{elseif $page_max > 100}
			{assign var='jumpcount' value=($page_max/50)|floor*5}
		{else}
			{assign var='jumpcount' value='10'}
		{/if}

		{for $x=1; $x<=$page_max; $x++}
			{if ($x == 1) || ($x == $page_max) || ($x >= $display_min && $x <= $display_max) || $x % $jumpcount == 0}
				{if $prev && ($prev+1 != $x)} .. {/if}

				{if $x == $page_current}

					<b>{$x}</b>

				{else}
					<a href="?page={$x}" title="Page {$x}">{$x}</a>
				{/if}

				{assign var='prev' value=$x}
			{/if}
		{/for}
	</div>

	<div class="clear"></div>
</div>