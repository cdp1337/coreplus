{foreach from=$widgets item='w'}
	<div class="widget-source" baseurl="{$w->get('baseurl')}">
		{$w->get('title')} ({$w->get('baseurl')})
	</div>
{/foreach}
