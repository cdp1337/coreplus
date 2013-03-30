{if count($pages)}

	{css src="css/admin/adminbar.css"}{/css}

<div id="admin-bar">
	<ul>
		<li class="has-sub">
			<i class="icon-cogs"></i>
			<ul class="sub-menu">
				{foreach from=$pages item=page}
					<li>
						{a href=$page->get('baseurl')}{$page->get('title')}{/a}
					</li>
				{/foreach}
			</ul>
		</li>
	</ul>

	{widget baseurl="/user/login"}

</div>

	{if Core::IsLibraryAvailable('jqueryui')}
		{script library="jqueryui"}{/script}
		{script library="jquery.cookie"}{/script}
		{script library="jquery.hoverintent"}{/script}

	{/if}

	{script}<script>
		function hoverOver(){
			$(this).find('ul.sub-menu').slideDown(350);
		}

		function hoverOut(){
			$(this).find('ul.sub-menu').slideUp(350);
		}

		$(function(){
			if ( $.fn.hoverIntent) {
				$('li.has-sub').hoverIntent({
					over: hoverOver,
					out: hoverOut,
					timeout: 320
				});
			} else {
				$('li.has-sub').hover(
					function(){
						hoverOver();
					},
					function(){
						hoverOut();
					}
				);
			}
		});
	</script>{/script}

{/if}