/**
 * Provides some string manipulations that can be used throughout.
 */


(function(){

	Core.Email = {
		/**
		 * Cleanup a string and ensure it can make a valid URL.
		 *
		 * @param string
		 * @return string
		 */

		Assemble: function(nodeid){

			var node   = document.getElementById(nodeid),
				user   = Core.Strings.rot13( node.dataset.user ),
				domain = node.dataset.domain,
				tld    = node.dataset.tld,
				address = user + '@' + domain + '.' + tld;

			node.innerHTML = address;
			node.setAttribute('href', 'mailto:' + address);
		}
	};

	// String.prototype.assemble = function(){
	// 	return Core.Strings.assemble(this.toString());
	// };

})();
