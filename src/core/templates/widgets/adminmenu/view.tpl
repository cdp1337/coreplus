{if count($groups)}

	{css src="css/admin/adminbar.css"}{/css}

	<div id="admin-bar" class="screen clearfix">
		<ul>


			{foreach $groups as $gdat}
				<li class="has-sub">
					{if $gdat.href}
						{a href="`$gdat.href`"}
							<span>{$gdat.title}</span>
						{/a}
					{else}
						<span>{$gdat.title}</span>
					{/if}

					<ul class="sub-menu">
						{foreach from=$gdat.children item=page}
							<li>
								{a href=$page->get('baseurl')}{$page->get('title')}{/a}
							</li>
						{/foreach}
					</ul>
				</li>
			{/foreach}

		</ul>

		{widget baseurl="/user/login"}

	</div>

	{if Core::IsLibraryAvailable('jqueryui') && Core::IsLibraryAvailable('jquery.hoverintent')}
		{script library="jqueryui"}{/script}
		{script library="jquery.hoverintent"}{/script}

		{script}<script>
			function hoverOver(){
				$(this).find('ul.sub-menu').slideDown(250);
			}

			function hoverOut(){
				$(this).find('ul.sub-menu').slideUp(250);
			}

			$(function(){
				if ( $.fn.hoverIntent) {
					$('li.has-sub').hoverIntent({
						over: hoverOver,
						out: hoverOut,
						timeout: 120
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

	{if Core::IsLibraryAvailable("jquery")}
		{script location="foot"}<script>
		// Fail safe on mobile platforms where hoverintent won't work or if it's not available.
		$('#admin-bar').find('span').click(function(){
			$(this).closest('li').find('ul').toggle();
		});
		</script>{/script}
	{/if}

{/if}