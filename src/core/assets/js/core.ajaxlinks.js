
$('body').on('click', '.ajax-link', function(){
	var $link = $(this),
		title = $link.attr('title') ? $link.attr('title') : 'Page',
		$window = $('<div>Loading ' + title + '...</div>'),
		$body = $('body'),
		width;
	
	// Calculate the inner width of the window.
	// If too small, then just make the dialog 100% of the width.
	width = $body.width() > 900 ? 850 : $body.width();

	$body.append($window);

	$window.dialog({
		modal: true,
		title: title,
		width: width + 'px',
		// This has been moved up here because it's breaking on the newest version of jQuery when on load. 
		position: { my: "center top", at: "center top+50", of: window },
		close: function(){ $window.remove(); }
	});

	$window.load(
		$link.attr('href'),
		function(responseText){
			// This is required to recenter the window.
			//$window.dialog('option', 'position', { my: "center", at: "center", of: window });
			// This is commented out because it seems to be breaking on the newest version of jQuery,
			// at least when on the /mediamanagernavigator system.
		}
	);

	return false;
});