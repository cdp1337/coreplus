{script name="jquery" location="head"}{/script}

{css}<style>
	.horizontal-layout {
		z-index: 2;
		position: relative;
	}
	.horizontal-layout li {
		display: inline-block;
		list-style: none;
		position: relative;
	}
	.horizontal-layout li a,
	.horizontal-layout li span {
		padding: 0 0.5em;
	}
	.horizontal-layout li ul {
		/* Single nested menu item */
		display: none;
		position: absolute;
		margin: 0;
	}
	
	.horizontal-layout li ul li ul {
		/* Double nested menu item */
		left: 99%;
		top: 0;
	}
</style>{/css}

<ul class="navigation-menu horizontal-layout">
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


{if Core::IsLibraryAvailable('jqueryui') && Core::IsLibraryAvailable('jquery.hoverintent')}
	{script library="jqueryui"}{/script}
	{script library="jquery.hoverintent"}{/script}

	{script location="foot"}<script>
		$(function(){
			if ( $.fn.hoverIntent) {
				$('.horizontal-layout li').hoverIntent({
					over: function(){
						$(this).find('ul').first().slideDown(250);
					},
					out: function(){
						$(this).find('ul').first().slideUp(250);
					},
					timeout: 120
				});
			}
		});
	</script>{/script}
{else}
	{css}<style>
		.horizontal-layout li:hover > ul {
			display: block;
		}
	</style>{/css}
{/if}