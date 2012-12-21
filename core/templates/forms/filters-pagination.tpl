
<div class="pagination">
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


	<div class="pagination-list">
		Page:
		{assign var='prev' value=null}
		{for $x=1; $x<=$page_max; $x++}
			{if ($x == 1) || ($x == $page_max) || ($x >= $display_min && $x <= $display_max)}
				{if $prev && ($prev+1 != $x)} ... {/if}

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