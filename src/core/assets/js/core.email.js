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

		assemble: function($node){

			var id      = $node.attr('id'),
				user    = Core.Strings.rot13( $node.attr('user') ),
				domain  = $node.attr('domain'),
				tld     = $node.attr('tld');

			var address = user + '@' + domain + '.' + tld;

			$node.html(address).attr('href', 'mailto:' + address);
		}

	};

	String.prototype.assemble = function(){
		return Core.Strings.assemble(this.toString());
	};

})();
