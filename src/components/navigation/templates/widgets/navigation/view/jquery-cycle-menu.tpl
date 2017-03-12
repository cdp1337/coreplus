{script name="jquery"}{/script}
{script name="jquery.cycle2"}{/script}

{function name=menu}
	{foreach $items as $i}
			{if $i.obj->getPageModel() && $i.obj->get('baseurl')}
				{* This link has a linked page model; retrieve some extra information from it. *}
				{assign var=image value=$i.obj->getPageModel()->getLogo()}
				<a href="{$i.obj->getResolvedURL()}" target="{$i.obj->get('target')}" title="{$i.obj->get('title')|escape}" {if $i.class}class="{$i.class}"{/if}>
					{img file=$image placeholder="generic" alt=$i.obj->get('title') dimensions="`$display_settings.image_dimensions`"}
				</a>
			{/if}
			{if sizeof($i.children)}
				{call name=menu items=$i.children}
			{/if}
	{/foreach}
	</ul>
{/function}

<div 
	class="navigation-menu navigation-menu-jquery-cycle cycle-slideshow"
	data-cycle-slides="> a"
	data-cycle-pause-on-hover="true"
	data-cycle-timeout=3000
>
	{call name=menu items=$entries}
</div>
