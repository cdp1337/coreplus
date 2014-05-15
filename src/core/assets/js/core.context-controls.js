$(function(){
	var $body = $('body'),
		defaults = {
			proxyicon: 'cog',
			proxytext: 'Controls',
			proxyforce: false
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
				proxyicon: $original.data('proxy-icon'),
				proxytext: $original.data('proxy-text'),
				proxyforce: $original.data('proxy-force')
			}, i;

		for(i in options){
			if(options[i] == undefined){
				options[i] = defaults[i];
			}
		}



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
		if(controlcount > 3 || options.proxyforce){
			$clone.html('<li><i class="icon-' + options.proxyicon + '"></i>' + (options.proxytext ? ' ' + options.proxytext : '') + '</li>');
		}

		$clone
			.mouseenter(function(){
				// Show this element's companion context menu always when the mouse enters its space.
				var o = $clone.offset();

				if($currentopen){
					$currentopen.hide();
				}

				$original.css(
					{
						top: o.top + 'px',
						left: (o.left + $clone.innerHeight()) + 'px'
					}
				).show();

				$currentopen = $original;
				$currentover = $clone;
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