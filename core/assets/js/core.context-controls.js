$(function(){
	$('.controls-hover').each(function(){
		var $original = $(this),
			$clone = $original.clone(),
			$wrapper = $('<div style="position:relative;"/>');

		$original.after($wrapper);

		// Now, I can move them both into the wrapper.
		$original.appendTo($wrapper);
		$clone.appendTo($wrapper);

		// I'm going to do modifications to the original, since that may have events already bound to it.
		$original.addClass('context-menu');

		// Ensure the clone width is correct.
		$original.css('min-width', $wrapper.width());

		// And hide it!  (it'll be shown on hover)
		$original.hide();

		// If there are more than 3 options, hide the rest.
		if($clone.find('li').length > 3){
			$clone.html('<li><i class="icon-cog"></i> Controls</li>');
		}

		$wrapper
			.mouseover(function(){
				$original.show();
			})
			.mouseout(function(){
				$original.hide();
			});
	});
});