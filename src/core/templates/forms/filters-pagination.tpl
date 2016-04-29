
<div class="pagination screen">
	<div class="pagination-pager">

		{if $page_current > 1}
			<a href="?page=1" title="First Page">
				<i class="icon-fast-backward"></i>
			</a>
			<a href="?page={$page_current-1}" title="Page {$page_current-1}">
				<i class="icon-backward"></i>
			</a>
			{*
		{else}
			<a href="#" title="First Page" class="disabled" onclick="return false;">
				<i class="icon-fast-backward"></i>
			</a>
			<a href="#" title="Page 1" class="disabled" onclick="return false;">
				<i class="icon-backward"></i>
			</a>
			*}
		{/if}

		{t 'STRING_PAGE_N' $page_current}

		{if $page_current < $page_max}
			<a href="?page={$page_current+1}" title="{t 'STRING_PAGE_N' $page_current+1}">
				<i class="icon-forward"></i>
			</a>
			<a href="?page={$page_max}" title="Last Page">
				<i class="icon-fast-forward"></i>
			</a>
			{*
		{else}
		<a href="#" title="Page {$page_current+1}" class="disabled" onclick="return false;">
			<i class="icon-forward"></i>
		</a>
		<a href="#" title="Last Page" class="disabled" onclick="return false;">
			<i class="icon-fast-forward"></i>
		</a>
			*}
	{/if}
</div>

	{if sizeof($limit_options)}
		{* Limit qty changing is enabled! *}
		{t 'STRING_DISPLAYING'}
		
		<select class="pagination-limit-selector" onchange="window.location.search = '?limit=' + this.options[this.selectedIndex].value;">
			{foreach $limit_options as $k}
				<option value="{$k}"{if $k == $limit} selected="selected"{/if}>{$k}</option>
			{/foreach}
		</select>
		
		{t 'STRING_OF_N_RECORDS' $records_total}
	{else}
		{* Limit qty changing is disabled, render the read-only view instead. *}
		{t 'STRING_DISPLAYING_N_THROUGH_N_OF_N_RECORDS' $records_start $records_end $records_total}
	{/if}
	


	<div class="pagination-list">
		{t 'STRING_PAGE'}:
		{assign var='prev' value=null}

		{**
		 * This is the new automatic jump system.
		 * Only ~8 page jumps at most are displayed on the interface.
		 *}
		{if $page_max > 100000}
			{*
			 * /50000 * 5000 is to preserve mods of 5000, for readibility
			 * This follows the same idea of the 10000 count, only with an even wider gap in numbers,
			 * producing an even smaller set of links.
			 *}
			{assign var='jumpcount' value=($page_max/30000)|ceil*5000}
		{elseif $page_max > 10000}
			{*
			 * /5000 * 500 is to preserve mods of 500, for readibility
			 * since there are so many zeros in these large numbers though, the base divisible number is slightly smaller,
			 * to make the resulting jump larger, and therefore less numbers cluttering the screen.
			 *}
			{assign var='jumpcount' value=($page_max/4000)|ceil*500}
		{elseif $page_max > 1000}
			{*
			 * /500 * 50 is to preserve mods of 50, for readibility
			 *}
			{assign var='jumpcount' value=($page_max/500)|ceil*50}
		{elseif $page_max > 100}
			{*
			 * /50 * 5 is to preserve mods of 5, for readibility
			 *}
			{assign var='jumpcount' value=($page_max/50)|ceil*5}
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