<!-- Me needs JQuery... -->
{script name="jquery" location="head"}{/script}

<ul class="navigation-menu">
{foreach from=$entries item='e'}
		{assign var='element' value=$e.obj}
		{assign var='children' value=$e.children}
		{assign var='class' value=$e.class}
		{include file="widgets/navigation/_menu.inc.tpl"}
	{/foreach}
</ul>


{script location="foot"}
// A little script to ensure the nav responds to hover states
$(function(){
$('ul.navigation-menu').find('li').mouseover(function(){ $(this).addClass('navigation-menu-over'); return true; }).mouseout(function(){ $(this).removeClass('navigation-menu-over'); return true;});
});
{/script}
