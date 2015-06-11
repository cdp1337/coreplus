$(function(){
	var $body = $('body'),
		defaults = {
			proxyIcon: 'cog',
			proxyIconAnimation: 'spin',
			proxyText: 'Controls',
			proxyforce: null,
			position: 'right'
		},
		$currentopen = null,
		$currentover = null,
		timer = null;

	//$('.controls-hover').each(function(){
	$('.controls').each(function(){
		// The original menu, this will become contextual.
		var $original = $(this),
		// A clone of it, this will be flattened and act as the anchor.
			$clone = $original.clone(),
		// The main wrapper
		//$wrapper = $('<div class="controls-hover-wrapper"/>'),
			controlcount = $clone.find('li').length,
			options = {
				proxyIcon: $original.data('proxy-icon'),
				proxyText: $original.data('proxy-text'),
				proxyForce: $original.data('proxy-force'),
				position: $original.data('position'),
				proxyIconAnimation: $original.data('proxy-icon-animation')
			}, i, proxyclass, useProxy;

		// Legacy options.
		if($original.data('spin')){
			options.proxyIconAnimation = 'spin';
		}
		else if($original.data('bounce')){
			options.proxyIconAnimation = 'bounce';
		}
		else if($original.data('float')){
			options.proxyIconAnimation = 'float';
		}

		for(i in options){
			if(options[i] == undefined){
				options[i] = defaults[i];
			}
		}

		// Transpose the appropriate defaults to the object.
		$original.data('position', options.position);



		// Append the clone to the end of the original's parent.
		// This should be in the same spot, if it isn't for some reason,
		// wrap your <ul class="controls"/> with an empty <div/>.
		$original.parent().append($clone);

		// And move the original to the end of the body.
		$body.append($original);

		// I'm going to do modifications to the original, since that may have events already bound to it.
		$original.addClass('context-menu').addClass('controls-hover');

		// This helps fine-tune the styles a little.
		if(controlcount == 1){
			$original.addClass('context-menu-one-link');
		}
		else{
			$original.addClass('context-menu-many-links');
		}

		// Ensure the clone width is correct.
		$original.css('min-width', $clone.width());

		// And hide it!  (it'll be shown on hover)
		$original.hide();

		// If there are more than 3 options, hide the rest.
		if (
			(options.proxyForce === null && controlcount > 3) ||
			options.proxyForce === true ||
			options.proxyForce === 1 ||
			options.proxyForce === '1'
		) {
			useProxy = true;
		}
		else {
			useProxy = false;
		}

		if(useProxy){

			proxyclass = 'controls-proxy-icon icon-' + options.proxyIcon;
			if(options.proxyIconAnimation){
				proxyclass += ' icon-' + options.proxyIconAnimation;
				$clone.addClass('controls-animated');
			}

			$clone.addClass('controls-proxy').html('<li><i class="' + proxyclass + '"></i>' + (options.proxyText ? ' ' + options.proxyText : '') + '</li>');
		}

		if(options.proxyIconAnimation){
			setTimeout(function($o){
				$o.find('.controls-proxy-icon').removeClass('icon-' + options.proxyIconAnimation);
			}, 1000, $clone);
		}

		$clone
			.mouseenter(function(){
				// Show this element's companion context menu always when the mouse enters its space.
				var co = $clone.offset(),
					bw = $('body').width(),
					padding = 5,
					position = $original.data('position'),
					nc, oo, ow;

				if($currentopen){
					$currentopen.hide();
				}

				if(position === 'bottom'){
					nc = {
						top: (co.top + $clone.height()) + 'px',
						left: co.left + 'px'
					};
				}
				else{
					// Left is the default, so it's last just in case the user does something silly.
					nc = {
						top: co.top + 'px',
						left: (co.left + $clone.innerHeight()) + 'px'
					};
				}

				$original.css( nc );

				$original.show();

				// Ensure that the dialog doesn't open past the edge of the window.
				oo = $original.offset();
				ow = $original.width();
				if(oo.left + ow + padding > bw){
					$original.css({ left: (bw - padding - ow) + 'px' });
				}

				$currentopen = $original;
				$currentover = $clone;

				if(options.proxyIconAnimation) {
					$currentover.find('.controls-proxy-icon').addClass('icon-' + options.proxyIconAnimation);
					setTimeout(function ($o) {
						$o.find('.controls-proxy-icon').removeClass('icon-' + options.proxyIconAnimation);
					}, 1000, $currentover);
				}
			})
			.mouseleave(function(){
				if(timer){
					clearTimeout(timer);
				}
				timer = setTimeout(function(){
					if($currentover == null && $currentopen){
						$currentopen.fadeOut();
					}
				}, 500);

				$currentover = null;
			});

		$original
			.mouseenter(function(){
				$currentover = $original;
			})
			.mouseleave(function(){
				$currentover = null;
				if(timer){
					clearTimeout(timer);
				}
				timer = setTimeout(function(){
					if($currentover == null && $currentopen){
						$currentopen.fadeOut();
					}
				}, 500);
			});
	});
});