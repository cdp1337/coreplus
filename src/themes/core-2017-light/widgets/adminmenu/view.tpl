{if count($groups)}

	{css src="css/admin/adminbar.css"}{/css}

	<div id="admin-bar" class="screen">
		<div class="admin-bar-anchor" id="admin-bar-anchor">
			{img src="assets/images/logos/coreplus.svg" inline="1"}
			<span title="Click to Expand/Hide">
				{t 'STRING_ADMIN_MENU'}
				<i class="icon icon-chevron-down expandable-hint"></i>
				<i class="icon icon-chevron-up collapsible-hint"></i>
			</span>
		</div>
		<noscript>Please enable javascript to use the admin menu</noscript>
		<ul class="admin-bar-menu" id="admin-bar-menu">
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
								{if $page->getLogo()}
									{a href=$page->get('baseurl') title="`$title|escape`" class="has-image"}
									{*
									 * Ensure that all optional attributes are defined here because we do not want
									 * the system polling the database for user-definable image attributes
									 * for a bunch of simple page icons!
									 *
									 * `alt` is a great example; it queries the database if left empty.
									 *}
										{img file=$page->getLogo() dimensions="24x24" alt="`$title|escape`"}
										{$title}
									{/a}
								{else}
									{a href=$page->get('baseurl') title="`$title|escape`" class="no-image"}{$title}{/a}
								{/if}
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
	
	{if Core::IsLibraryAvailable('jquery') && Core::IsLibraryAvailable('jquery.hoverintent')}
		{script library="jquery"}{/script}
		{script library="jquery.hoverintent"}{/script}
	{/if}
	
	{script location="foot"}<script>
		(function () {
			"use strict";
			
			var bar = document.getElementById('admin-bar'),
				anchor = document.getElementById('admin-bar-anchor'),
				menu = document.getElementById('admin-bar-menu'),
				activeLink = null,
				i, n, ii, nn, swapClasses, hasClass, removeClass, addClass, toggleClass;
			
			/**
			 * Swap classes on a given element, removing the old one and ensuring the new one exists.
			 * 
			 * @param DOMNode el
			 * @param string oldClass
			 * @param string newClass
			 */
			function swapClasses(el, oldClass, newClass){
				var sp = el.attributes.class.value.split(" "), idx;

				if((idx = sp.indexOf(oldClass)) !== -1){
					// Drop the old one.
					sp.splice(idx, 1);
				}

				if(sp.indexOf(newClass) === -1){
					// Append the new one.
					sp.push(newClass);
				}

				// And stamp everything back down!
				el.attributes.class.value = sp.join(" ");
			};
			
			function removeClass(el, className){
				var sp = el.attributes.class.value.split(" "), idx;
				
				if((idx = sp.indexOf(className)) !== -1){
					// Drop the old one.
					sp.splice(idx, 1);
					// And stamp everything back down!
					el.attributes.class.value = sp.join(" ");
				}
			};
			
			function addClass(el, className){
				var sp = el.attributes.class.value.split(" "), idx;
				
				if((idx = sp.indexOf(className)) === -1){
					// Append the new one.
					sp.push(className);
					// And stamp everything back down!
					el.attributes.class.value = sp.join(" ");
				}
			};
			
			function toggleClass(el, className){
				var sp = el.attributes.class.value.split(" "), idx;
				
				if((idx = sp.indexOf(className)) !== -1){
					// This class name exists in the stack, remove it.
					sp.splice(idx, 1);
				}
				else{
					// Or it doesn't, so add it.
					sp.push(className);
				}
				
				// And stamp everything back down!
				el.attributes.class.value = sp.join(" ");
			}
			
			/**
			 * Check if a given node has a class.
			 * 
			 * @param DOMNode el
			 * @param string className
			 * @returns bool
			 */
			function hasClass(el, className){
				var c = el.attributes.class.value;
				
				// Easiest check; the class is one and only one, and matches.
				if(c === className) return true;
				
				// Another easy check, it's empty.
				if(c === "") return false;
				
				// Not one of the easy ones?... do the extra work of iterating through it.
				return c.split(" ").indexOf(className) !== -1;
			};
			
			function openMenu(el){
				// Ensure the bar is set to "deep" anytime a menu is opened.
				addClass(bar, 'deep');
				addClass(el, 'active');
				// Remember this link in the event that it needs to be closed externally.
				activeLink = el;
				
				// is jQuery available for pretty effects?
				if(typeof $ === 'function'){
					var $subMenu = $(el).find('ul.sub-menu');
					$subMenu.hide().slideDown(300);
				}
			}
			
			function closeMenu(el){
				// Close the currently active menu; if another is reopened immediately,
				// this variable will get overwritten from that open call anyway.
				activeLink = null;
				
				// is jQuery available for pretty effects?
				if(typeof $ === 'function'){
					$(el).find('ul.sub-menu').slideUp(300, function(){
						if(activeLink === null){
							// All links closed!
							removeClass(bar, 'deep');
						}
						removeClass(el, 'active');
					});
				}
				else{
					// Inversely close the bar when a link is closed.
					removeClass(bar, 'deep');
					removeClass(el, 'active');
				}
			}
			
			// Setup the listeners for this anchor; it is meant to toggle the admin menu on mobile.
			anchor.addEventListener('click', function(){
				toggleClass(bar, 'touch-active');
				removeClass(bar, 'deep');
				
				if(activeLink !== null){
					removeClass(activeLink, 'active');
				}
			});

			// Run through the menu to find each li.has-sub link; these will contain the
			// event onClick for toggling the menu.
			// Done manually because jQuery may not be available.
			for(i = 0; i < menu.childNodes.length; i++){
				// Alias for shorter code
				n = menu.childNodes[i];
				if(n.nodeName === 'LI' && hasClass(n, 'has-sub')){
					n.addEventListener('click', function(){
						if(hasClass(this, 'active')){
							closeMenu(this);
						}
						else{
							// Close the previous one if there is one open!
							if(activeLink !== null){
								closeMenu(activeLink);
							}
							openMenu(this);
						}
					});
				}
			}
			
			// Use hoverOver for the mouse over effects, (only if jQuery is available).
			if(typeof $ === 'function' && $.fn.hoverIntent){
				$(bar).find('li.has-sub').hoverIntent({
					over: function(){
						if(!hasClass(bar, 'touch-active')){
							openMenu(this);
						}
					},
					out: function(){
						if(!hasClass(bar, 'touch-active')){
							closeMenu(this);
						}
					},
					timeout: 120
				});
			}
		})();
	</script>{/script}

	{if Core::IsLibraryAvailable("jquery") && Core::IsLibraryAvailable("js.cookie")}
		{script library="jquery"}{/script}
		{script library="js.cookie"}{/script}

		{script location="foot"}<script>
			// Make the language selection do something!
			$('#admin-bar').find('.set-lang-target').click(function() {
				Cookies.remove('LANG');
				Cookies.set('LANG', $(this).data('lang'));
				//$.cookie('LANG', $(this).data('lang'));
				Core.Reload();
				return false;
			});
		</script>{/script}
	{/if}

{/if}