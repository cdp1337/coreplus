<li {if $class}class="{$class}"{/if}>
	{if $element->get('baseurl')}
		<a href="{$element->getResolvedURL()}" target="{$element->get('target')}">{$element->get('title')}</a>
		{else}
		<span>{$element->get('title')}</span>
	{/if}

	{if sizeof($children)}
		<ul class="sub-menu">
			{foreach from=$children item='sube'}
					{assign var='element' value=$sube.obj}
					{assign var='children' value=$sube.children}
					{assign var='class' value=$sube.class}
					{include file="widgets/navigation/_menu.inc.tpl"}
				{/foreach}
		</ul>
	{/if}
</li>
