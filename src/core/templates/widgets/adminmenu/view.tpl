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
						{foreach $gdat.children as $title => $page}
							<li>
								{a href=$page->get('baseurl') title="`$title|escape`"}{$title}{/a}
							</li>
						{/foreach}
					</ul>
				</li>
			{/foreach}

			{**
			 * @var $languages =  array (size=5)
			 *      'key'      => string 'de_DE' (length=5)
			 *      'title'    => string 'German (Germany)' (length=16)
			 *      'country'  => string 'DE' (length=2)
			 *      'image'    => string 'assets/images/iso-country-flags/de.png' (length=38)
			 *      'selected' => boolean false
			 *}
			{if sizeof($languages)}
				<li class="has-sub">
					<span>{t 'STRING_CHANGE_LANGUAGE'}</span>
					<ul class="admin-bar-language-selection sub-menu">
						{foreach $languages as $lang}
							<li>
								<a href="#" title="{t 'STRING_SET_LANGUAGE_TO_S' $lang.title}" class="set-lang-target" data-lang="{$lang.key}">
									{img src="`$lang.image`" dimensions="28x28" alt="`$lang.country`"}
									<span>{$lang.title}</span>
								</a>
							</li>
						{/foreach}
					</ul>
				</li>
			{/if}
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
		{script library="jquery"}{/script}
		{script library="js.cookie"}{/script}

		{script location="foot"}<script>
			var $bar = $('#admin-bar');
			// Fail safe on mobile platforms where hoverintent won't work or if it's not available.
			$bar.find('span').click(function(){
				$(this).closest('li').find('ul').toggle();
			});

			// Make the language selection do something!
			$bar.find('.set-lang-target').click(function() {
				Cookies.remove('LANG')
				Cookies.set('LANG', $(this).data('lang'));
				//$.cookie('LANG', $(this).data('lang'));
				Core.Reload();
				return false;
			});
		</script>{/script}
	{/if}

{/if}