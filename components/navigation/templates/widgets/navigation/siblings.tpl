<ul class="navigation-menu">
{foreach from=$entries item='e'}
		{assign var='element' value=$e.obj}
		{assign var='children' value=$e.children}
		{assign var='class' value='first'}
		{include file="widgets/navigation/_menu.inc.tpl"}
	{/foreach}
</ul>