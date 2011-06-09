<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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
