<ul class="health-checks">
	{foreach $checks as $check}
		<li class="health-check health-check-{$check->result|strtolower}">
			{if $check->result == 'GOOD'}
				<i class="icon icon-check status-icon"></i>
			{elseif $check->result == 'SKIP'}
				<i class="icon icon-ban status-icon"></i>
			{elseif $check->result == 'WARN'}
				<i class="icon icon-exclamation-triangle status-icon"></i>
			{elseif $check->result == 'ERRR'}
				<i class="icon icon-times-circle-o status-icon"></i>
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
			
			{if $check->description}
				<a href="#" class="expandable-hint">
					<i class="icon icon-chevron-down"></i> More Details	
				</a>
				
				<a href="#" class="collapsible-hint" style="display:none;">
					<i class="icon icon-chevron-up"></i> Less Details
				</a>
				<p class="expandable-content" style="display:none;">{$check->description}</p>
			{/if}
		</li>
	{/foreach}
</ul>

{script library="jquery"}{/script}
{script location="foot"}<script>
	$(function() {
		$('.expandable-hint').click(function() {
			var $this = $(this),
				$li = $this.closest('li'),
				$less = $li.find('.collapsible-hint'),
				$details = $li.find('.expandable-content');
			
			$this.hide();
			$less.show();
			$details.show();
			
			return false;
		});

		$('.collapsible-hint').click(function() {
			var $this = $(this),
				$li = $this.closest('li'),
				$more = $li.find('.expandable-hint'),
				$details = $li.find('.expandable-content');

			$this.hide();
			$more.show();
			$details.hide();

			return false;
		});
	});
</script>{/script}