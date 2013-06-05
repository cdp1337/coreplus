<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130603.1443
 * @package PackageName
 * 
 * Created with JetBrains PhpStorm.
 */

class GeocodeUpdaterTest extends PHPUnit_Framework_TestCase {
	public function testUpdateDatabases() {
		$f = \Core\Filestore\Factory::File('tmp/geographic-codes/country_name_iso_2.txt');
		if($f->exists()){
			// Well... delete it first!
			$this->assertTrue($f->delete());
		}

		geocode\Updater::UpdateDatabases(true);

		$f = \Core\Filestore\Factory::File('tmp/geographic-codes/country_name_iso_2.txt');
		$this->assertTrue($f->exists());
	}
}
