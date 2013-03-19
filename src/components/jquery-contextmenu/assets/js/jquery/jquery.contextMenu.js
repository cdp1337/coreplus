/**
 * jQuery Context Menu Plugin
 *
 * @link http://abeautifulsite.net/2008/09/jquery-context-menu-plugin/
 *
 * @version 1.01~core1
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @author Cory S.N. LaViska - A Beautiful Site (http://abeautifulsite.net/)
 *
 * Terms of Use
 *
 * This plugin is dual-licensed under the GNU General Public License
 *   and the MIT License and is copyright A Beautiful Site, LLC.
 *
 * @license MIT License
 * @license GPL
 */
if(jQuery)( function() {
	$.extend($.fn, {

		contextMenu: function(options, callback) {
			// Defaults
			o = $.extend({
				menu: null,
				inSpeed: 150,
				outSpeed: 75
			}, options);

			if(!o.menu){
				$.error('No context menu defined');
				return false;
			}

			// 0 needs to be -1 for expected results (no fade)
			if( o.inSpeed == 0 ) o.inSpeed = -1;
			if( o.outSpeed == 0 ) o.outSpeed = -1;
			// Loop each context menu

			$(this).each( function() {
				var el = $(this),
					offset = $(el).offset(),
					menu;

				// Is the menu already a jquery object?
				if(typeof o.menu.jquery == 'undefined'){
					menu = $('#' + o.menu);
				}
				else{
					menu = o.menu;
				}

				// Add contextMenu class
				menu.addClass('contextMenu');
				// Simulate a true right click
				$(this).mousedown( function(e) {
					var evt = e;
					evt.stopPropagation();
					$(this).mouseup( function(e) {
						e.stopPropagation();
						var srcElement = $(this);
						$(this).unbind('mouseup');
						if( evt.button == 2 ) {
							// Hide context menus that may be showing
							$(".contextMenu").hide();

							if( $(el).hasClass('disabled') ) return false;

							// Detect mouse position
							var d = { }, x, y;
							if( self.innerHeight ) {
								d.pageYOffset = self.pageYOffset;
								d.pageXOffset = self.pageXOffset;
								d.innerHeight = self.innerHeight;
								d.innerWidth = self.innerWidth;
							} else if( document.documentElement &&
								document.documentElement.clientHeight ) {
								d.pageYOffset = document.documentElement.scrollTop;
								d.pageXOffset = document.documentElement.scrollLeft;
								d.innerHeight = document.documentElement.clientHeight;
								d.innerWidth = document.documentElement.clientWidth;
							} else if( document.body ) {
								d.pageYOffset = document.body.scrollTop;
								d.pageXOffset = document.body.scrollLeft;
								d.innerHeight = document.body.clientHeight;
								d.innerWidth = document.body.clientWidth;
							}
							(e.pageX) ? x = e.pageX : x = e.clientX + d.scrollLeft;
							(e.pageY) ? y = e.pageY : y = e.clientY + d.scrollTop;

							// Show the menu
							$(document).unbind('click');
							menu.css({ top: y, left: x }).fadeIn(o.inSpeed);
							// Hover events
							menu.find('A').mouseover( function() {
								menu.find('LI.hover').removeClass('hover');
								$(this).parent().addClass('hover');
							}).mouseout( function() {
									menu.find('LI.hover').removeClass('hover');
								});

							// Keyboard
							$(document).keypress( function(e) {
								switch( e.keyCode ) {
									case $.ui.keyCode.UP:
										if( menu.find('LI.hover').size() == 0 ) {
											menu.find('LI:last').addClass('hover');
										} else {
											menu.find('LI.hover').removeClass('hover').prevAll('LI:not(.disabled)').eq(0).addClass('hover');
											if( menu.find('LI.hover').size() == 0 ) menu.find('LI:last').addClass('hover');
										}
										break;
									case $.ui.keyCode.DOWN:
										if( menu.find('LI.hover').size() == 0 ) {
											menu.find('LI:first').addClass('hover');
										} else {
											menu.find('LI.hover').removeClass('hover').nextAll('LI:not(.disabled)').eq(0).addClass('hover');
											if( menu.find('LI.hover').size() == 0 ) menu.find('LI:first').addClass('hover');
										}
										break;
									case $.ui.keyCode.ENTER:
										menu.find('LI.hover A').trigger('click');
										break;
									case $.ui.keyCode.ESCAPE:
										$(document).trigger('click');
										break
								}
							});

							// When items are selected
							menu.find('A').unbind('click');
							menu.find('LI:not(.disabled) A').click( function() {
								$(document).unbind('click').unbind('keypress');
								$(".contextMenu").hide();
								// Callback
								if( callback ) callback( $(this).attr('href').substr(1), $(srcElement), { x: x - offset.left, y: y - offset.top, docX: x, docY: y } );
								return false;
							});

							// Hide bindings
							setTimeout( function() { // Delay for Mozilla
								$(document).click( function() {
									$(document).unbind('click').unbind('keypress');
									menu.fadeOut(o.outSpeed);
									return false;
								});
							}, 0);
						}
					});
				});

				// Disable text selection
				if( $.browser.mozilla ) {
					menu.each( function() { $(this).css({ 'MozUserSelect' : 'none' }); });
				} else if( $.browser.msie ) {
					menu.each( function() { $(this).bind('selectstart.disableTextSelect', function() { return false; }); });
				} else {
					menu.each(function() { $(this).bind('mousedown.disableTextSelect', function() { return false; }); });
				}
				// Disable browser context menu (requires both selectors to work in IE/Safari + FF/Chrome)
				$(el).add($('UL.contextMenu')).bind('contextmenu', function() { return false; });

			});
			return $(this);
		},

		// Disable context menu items on the fly
		disableContextMenuItems: function(o) {
			if( o == undefined ) {
				// Disable all
				$(this).find('LI').addClass('disabled');
				return( $(this) );
			}
			$(this).each( function() {
				if( o != undefined ) {
					var d = o.split(',');
					for( var i = 0; i < d.length; i++ ) {
						$(this).find('A[href="' + d[i] + '"]').parent().addClass('disabled');

					}
				}
			});
			return( $(this) );
		},

		// Enable context menu items on the fly
		enableContextMenuItems: function(o) {
			if( o == undefined ) {
				// Enable all
				$(this).find('LI.disabled').removeClass('disabled');
				return( $(this) );
			}
			$(this).each( function() {
				if( o != undefined ) {
					var d = o.split(',');
					for( var i = 0; i < d.length; i++ ) {
						$(this).find('A[href="' + d[i] + '"]').parent().removeClass('disabled');

					}
				}
			});
			return( $(this) );
		},

		// Disable context menu(s)
		disableContextMenu: function() {
			$(this).each( function() {
				$(this).addClass('disabled');
			});
			return( $(this) );
		},

		// Enable context menu(s)
		enableContextMenu: function() {
			$(this).each( function() {
				$(this).removeClass('disabled');
			});
			return( $(this) );
		},

		// Destroy context menu(s)
		destroyContextMenu: function() {
			// Destroy specified context menus
			$(this).each( function() {
				// Disable action
				$(this).unbind('mousedown').unbind('mouseup');
			});
			return( $(this) );
		}

	});
})(jQuery);