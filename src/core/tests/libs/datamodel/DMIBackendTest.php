<?php
/**
 * Enter a meaningful file description here!
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130312.0529
 * @package PackageName
 *
 * Created with JetBrains PhpStorm.
 */
/**
 * This will test the currently selected DMI_Backend in the system.
 */
class DMIBackendTest extends PHPUnit_Framework_TestCase{
	/**
	 * @var DMI_Backend
	 */
	public $dmi;

	public $dminame;

	/**
	 * Ensure that a valid DMI object can be created in the system.
	 */
	protected function setUp(){
		$this->dmi = \Core\db();
		$this->dminame = get_class($this->dmi);

		// This should be a DMI_Backend object!
		$this->assertInstanceOf('DMI_Backend', $this->dmi, '\Core\db() did not return a valid DMI_Backend object!');
	}

	/**
	 * Ensure that tableExists returns true for a table that exists.
	 */
	public function testTableExists(){
		$tablethatexists = 'component';

		$this->assertTrue($this->dmi->tableExists($tablethatexists));
	}

	/**
	 * The inverse of tableExists true.  If a table does not exist, it needs to indicate such!
	 */
	public function testTableExistsNot(){
		$tablethatdoesnot = 'something_completely_random_and_hopefully_does_not_exist';
		$this->assertFalse($this->dmi->tableExists($tablethatdoesnot));
	}

	public function testCreateTable1(){
		$schema = array(
			'id'                         => array(
				'type' => Model::ATT_TYPE_ID,
			),
			'site'                       => array(
				'type'     => Model::ATT_TYPE_SITE,
				'formtype' => 'system',
			),
			'manage_articles_permission' => array(
				'type'    => Model::ATT_TYPE_STRING,
				'default' => '!*',
				'form'    => array('type' => 'access', 'title' => 'Article Management Permission', 'description' => 'Which groups can add, edit, and remove blog articles in this blog.'),
			),
		);
	}
}
