// Core should already be defined.

/**
 * Create a POST request in the browser without ajax.
 * Allows for a traditional page load, only via POST.
 *
 * @param u URL to request via POST
 * @constructor
 */
Core.PostURL = function(u){
	var doc = window.document,
		form = doc.createElement('form');

	window.document.body.appendChild(form);
	form.action = u;
	form.method = 'POST';
	form.submit();
};

/**
 * Just a simple function to reload the current page.
 *
 * @constructor
 */
Core.Reload = function(){
	window.location.reload();
};

if(typeof console == 'undefined'){
	console = {};
}
if(typeof console.log == 'undefined'){
	console.log = function(){ /* This page purposefully left blank :p */ }
}