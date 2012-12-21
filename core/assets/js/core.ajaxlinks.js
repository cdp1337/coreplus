
$('.ajax-link').click(function(){
	var $link = $(this),
		title = $link.attr('title') ? $link.attr('title') : 'Page';
		$window = $('<div>Loading ' + title + '...</div>'),
		$body = $('body');

	$body.append($window);

	$window.dialog({
		modal: true,
		title: title,
		width: '850px',
		position: 'center',
		close: function(){ $(this).remove(); }
	});

	$window.load(
		$link.attr('href'),
		function(responseText){
			// This is required to recenter the window.
			$window.dialog('option', 'position', 'center');
		}
	);

	return false;
});