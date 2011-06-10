<?php
/**
 * Interface that all backends should share.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @package Core
 * @subpackage Datamodel
 * @since 20110610
 */

/**
 *
 * @author powellc
 */
interface DMI_Backend {
	public function connect($host, $user, $pass, $database);
	
	public function execute(Dataset $dataset);
	
	public function tableExists($tablename);
	
	public function createTable($tablename, $schema);
}

?>
