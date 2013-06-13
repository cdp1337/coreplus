{if count($groups)}

	{css src="css/admin/adminbar.css"}{/css}

	<div id="admin-bar" class="screen">
		<ul>


			{foreach $groups as $gname => $pages}
				<li class="has-sub">
					<span>{$gname}</span>
					<ul class="sub-menu">
						{foreach from=$pages item=page}
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
		{script library="jquery.cookie"}{/script}
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