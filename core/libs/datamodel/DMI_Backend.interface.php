<?php
/**
 * Interface that all backends should share.
 * 
 * @package Core
 * @subpackage Datamodel
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

/**
 *
 * @author powellc
 */
interface DMI_Backend {
	/**
	 * Create a new connection to the backend server.
	 * 
	 * @param type $host
	 * @param type $user
	 * @param type $pass
	 * @param type $database
	 * @return mixed 
	 */
	public function connect($host, $user, $pass, $database);
	
	public function execute(Dataset $dataset);
	
	/**
	 * Check to see if a given table exists without causing an error.
	 * 
	 * @param string $tablename
	 * @return boolean 
	 */
	public function tableExists($tablename);
	
	public function createTable($tablename, $schema);
	
	public function readCount();
	
	public function writeCount();
}

?>
