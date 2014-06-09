$(function(){
	var $body = $('body'),
		defaults = {
			proxyicon: 'cog',
			proxytext: 'Controls',
			proxyforce: false,
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
				proxyicon: $original.data('proxy-icon'),
				proxytext: $original.data('proxy-text'),
				proxyforce: $original.data('proxy-force'),
				position: $original.data('position')
			}, i;

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
		if(controlcount > 3 || options.proxyforce){
			$clone.html('<li><i class="icon-' + options.proxyicon + '"></i>' + (options.proxytext ? ' ' + options.proxytext : '') + '</li>');
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

				if(position == 'bottom'){
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