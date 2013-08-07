// They generally start on page one.  However if the request had page=12, then start on that page!
var
	currentpage = parseInt(Core.Request.get('page') || 1),
	loadingnow = null,
	scrollhandler = null,
	$reftarget = $('#bottomofthelisting');

scrollhandler = function(direction){
	// Are there no more results?
	if(currentpage == null) return;

	// If this page is currently loading, don't try to reload again!
	if(loadingnow == currentpage) return;

	// If it's scrolling up... why do I care?
	if(direction != 'down') return;

	// yay, the user is scrolling down... load the next set of results!
	currentpage += 1; // Increment
	loadingnow = currentpage; // and remember

	$.ajax({
		url: '?page=' + currentpage,
		success: function(data){
			// Yay, it's loaded... I can clear the results.
			loadingnow = null;

			// Clear out the waypoint... for some reason it's not picking up on the moved node after appending.
			$reftarget.waypoint('destroy');

			var $articles = $(data).find('.blog-article');
			if($articles.length == 0){
				// There aren't any more results :/
				currentpage = null;
				return;
			}

			// Append them!
			$articles.each(function(){
				$('.blog-listing').append(this);
			});

			// And now I can reinitialize the waypoints.
			$reftarget.waypoint({
				offset: '110%',
				handler: scrollhandler
			});

		}
	});
};


$reftarget.waypoint({
	offset: '110%',
	handler: scrollhandler
});

// And hide the pagination.
$('.pagination').hide();