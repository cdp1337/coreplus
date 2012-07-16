/**
 * Any core javascript that needs to be in the foot of the document.
 * This is just for core systems and functions; components and plugins can have your own file!
 */


(function(){
	/**
	 * Below are some jquery-specific functions, only applicable if jquery is loaded already.
	 * This will NOT be required by default because jquery is not a requirement of Core,
	 * simply a recommendation.
	 */
	if(typeof jQuery != 'undefined'){
		// The confirm text for links.  Useful for templates and systems that want to use that system without actually
		// using the smarty function.
		jQuery('body').on('click', 'a.confirmlink', function(){
			var confirmtext = $(this).attr('confirm'),
				confirmhref = $(this).attr('href');
			if(!confirmtext) confirmtext = 'Are you sure?';

			if(confirm(confirmtext)){
				Core.PostURL(confirmhref);
			}

			return false;
		});
	}
})();