/**
 * Any core javascript that needs to be in the foot of the document.
 * This is just for core systems and functions; components and plugins can have your own file!
 *
 * Note, Core is already defined in the header.
 */

(function(){

	var _getParameterObject = null, _getParameters;

	_getParameters = function(){
		if(_getParameterObject !== null) return _getParameterObject;

		var arr, i, ar2, p1, p2, idx;

		_getParameterObject = {};

		if(!window.location.search) return _getParameterObject;

		arr = window.location.search.slice(1).split('&');
		for(i=0; i<arr.length; i++){
			ar2 = arr[i].split('=');
			ar2[0] = decodeURIComponent(ar2[0]);
			ar2[1] = decodeURIComponent(ar2[1]);

			if(ar2[0].match(/\[.+\]/)){
				idx = ar2[0].indexOf('[');
				p1 = ar2[0].substr(0, idx);
				p2 = ar2[0].substr(idx+1, ar2[0].length-idx-2);
				if(typeof _getParameterObject[p1] == 'undefined') _getParameterObject[p1] = {};
				_getParameterObject[p1][p2] = ar2[1];
			}
			else if(ar2[0].match(/\[\]/)){
				p1 = ar2[0].substr(0, ar2[0].length-2);
				if(typeof _getParameterObject[p1] == 'undefined') _getParameterObject[p1] = [];
				_getParameterObject[p1].push(ar2[1]);
			}
			else{
				_getParameterObject[ar2[0]] = ar2[1];
			}
		}

		return _getParameterObject;
	};

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

	Core.Request = {
		/**
		 * Get the GET request parameters (or single parameter)
		 *
		 * @param key
		 *
		 * @return object|string
		 */
		get: function(key){
			var o = _getParameters();

			if(key){
				return (typeof o[key] != 'undefined') ? o[key] : null;
			}
			else{
				return o;
			}
		},

		/**
		 * Set the GET request parameters based on an object.  (The object is converted to a valid query string).
		 *
		 * @param obj
		 */
		set: function(obj){
			// gogo jquery!
			if(typeof jQuery != 'undefined'){
				window.location.search = '?' + jQuery.param(obj);
			}
			else{
				// Ummm..... :/
			}
		}
	};

	/**
	 * Just a simple function to reload the current page.
	 *
	 * @constructor
	 */
	Core.Reload = function(){
		window.location.reload();
	};

	/**
	 * Define console as an object so console.logs don't break in IE.
	 */
	if(typeof console == 'undefined'){
		console = {};
	}
	if(typeof console.log == 'undefined'){
		console.log = function(){ /* This page purposefully left blank :p */ }
	}

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

		/**
		 * Just a simple script to spruce up the message-* blocks with a bit of flair.
		 */
		var types = [
				{ class: 'error',    icon: 'exclamation-sign' },
				{ class: 'info',     icon: 'info-sign' },
				{ class: 'note',     icon: 'asterisk' },
				{ class: 'success',  icon: 'ok-sign' },
				{ class: 'tutorial', icon: 'question-sign' }
			],
			i;

		for(i in types){
			jQuery('.message-' + types[i].class).each(function(){
				jQuery(this).prepend('<span class="message-background-icon"><i class="icon-' + types[i].icon + '"></i></span>');
			});
		}
	}

})();