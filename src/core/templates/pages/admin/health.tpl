<ul class="health-checks">
	{foreach $checks as $check}
		<li class="health-check health-check-{$check->result|strtolower}">
			{if $check->result == 'GOOD'}
				<i class="icon-check"></i>
			{elseif $check->result == 'SKIP'}
				<i class="icon-ban"></i>
			{elseif $check->result == 'WARN'}
				<i class="icon-exclamation-triangle"></i>
			{elseif $check->result == 'ERRR'}
				<i class="icon-times-circle-o"></i>
			{/if}
			
			{if $check->link}
				{if $check->result == 'GOOD'}
					{a href="`$check->link`" title="t:STRING_MORE_INFORMATION"}{$check->title}{/a}
				{else}
					{a href="`$check->link`" title="t:STRING_FIX"}{$check->title}{/a}
				{/if}
			{else}
				{$check->title}
			{/if}
		</li>
	{/foreach}
</ul>
