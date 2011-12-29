<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU AGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/agpl.html>, 
 * and please contribute back to the community :)
 */


class Controller {
	
	/**
	 * If this is not null, it is checked before the controller method is even called.
	 * @var string 
	 */
	public static $AccessString = null;
	
	public function __construct(){
		
	}

}
