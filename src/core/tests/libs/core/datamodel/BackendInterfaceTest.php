<?php
/**
 * Run the test suites for the Data Model Backend Interface for the currently enabled data model backend.
 *
 * This suite uses \Core\db() to pull whichever the default backend is.
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130312.0529
 * @package Core\Datamodel
 */
/**
 * This will test the currently selected BackendInterface in the system.
 *
 * @package Core\Datamodel
 */
class BackendInterfaceTest extends PHPUnit_Framework_TestCase{
	/**
	 * @var Core\Datamodel\BackendInterface
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
		$this->assertInstanceOf('Core\\Datamodel\\BackendInterface', $this->dmi, '\Core\db() did not return a valid Core\\Datamodel\\BackendInterface object!');
	}

	/**
	 * Test that the Execute method properly handles a generic Dataset object.
	 */
	public function testExecute(){
		$ds = \Core\Datamodel\Dataset::Init()
			->select('*')
			->table('component');

		$this->dmi->execute($ds);

		$this->assertGreaterThan(0, sizeof($ds->num_rows));
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

	/**
	 * Test the Create, Modify, and Drop table methods.
	 *
	 * This is the simplest way to handle these, since they all depend on each other.
	 */
	public function testCreateModifyDropTable1(){
		$schema = new \Core\Datamodel\Schema();

		$cols = array(
			'id'                         => array(
				'type' => Model::ATT_TYPE_ID,
				'maxlength' => 15,
				'autoinc' => true,
			),
			'site'                       => array(
				'type'     => Model::ATT_TYPE_SITE,
				'maxlength' => 15,
				'default' => 0,
			),
			'manage_articles_permission' => array(
				'type'    => Model::ATT_TYPE_STRING,
				'default' => '!*',
			),
			'body'        => array(
				'type'     => Model::ATT_TYPE_TEXT,
			),
			'status'      => array(
				'type'    => Model::ATT_TYPE_ENUM,
				'options' => array('published', 'draft'),
				'default' => 'draft',
			),
			'created'     => array(
				'type' => Model::ATT_TYPE_CREATED,
				'null' => false,
				'maxlength' => 15,
				'default' => 0,
			),
			'updated'     => array(
				'type' => Model::ATT_TYPE_UPDATED,
				'null' => false,
				'maxlength' => 15,
				'default' => 0,
			),
		);

		foreach($cols as $key => $dat){
			$col = new \Core\Datamodel\SchemaColumn();
			$col->field = $key;
			$col->type = $dat['type'];
			if(isset($dat['default']))   $col->default = $dat['default'];
			if(isset($dat['options']))   $col->options = $dat['options'];
			if(isset($dat['null']))      $col->null = $dat['null'];
			if(isset($dat['maxlength'])) $col->maxlength = $dat['maxlength'];
			if(isset($dat['autoinc']))   $col->autoinc = $dat['autoinc'];
			$schema->definitions[$key] = $col;
			$schema->order[] = $key;
		}

		$schema->indexes['primary'] = ['id'];

		$this->assertFalse(\Core\db()->tableExists('test_table_create'));

		$this->assertTrue(\Core\db()->createTable('test_table_create', $schema));

		$this->assertTrue(\Core\db()->tableExists('test_table_create'));

		// Modify the table now.
		$newcol = new \Core\Datamodel\SchemaColumn();
		$newcol->field = 'foo';
		$newcol->type = Model::ATT_TYPE_TEXT;
		$newcol->maxlength = 45;
		$schema->definitions['foo'] = $newcol;
		$schema->order[] = 'foo';

		$this->assertTrue(\Core\db()->modifyTable('test_table_create', $schema));

		// Ensure that the new column exists.
		$describe = \Core\db()->describeTable('test_table_create');
		$this->assertInstanceOf('Core\\Datamodel\\Schema', $describe);
		$this->assertEquals(8, sizeof($describe->definitions));

		$this->assertTrue(\Core\db()->dropTable('test_table_create'));
	}

	/**
	 * Test the showTables method of the interface
	 */
	public function testShowTables(){
		$tables = \Core\db()->showTables();
		$this->assertGreaterThan(1, sizeof($tables));
	}

	/**
	 * By this stage, there should be some read count
	 */
	public function testReadCount(){
		$this->assertGreaterThan(1, \Core\Utilities\Profiler\DatamodelProfiler::GetDefaultProfiler()->readCount());
	}

	/**
	 * By this stage, there should be some write count
	 */
	public function testWriteCount(){
		$this->assertGreaterThan(1, \Core\Utilities\Profiler\DatamodelProfiler::GetDefaultProfiler()->writeCount());
	}

	public function testQueryLog(){
		$this->assertGreaterThan(1, \Core\Utilities\Profiler\DatamodelProfiler::GetDefaultProfiler()->getEvents());
	}
}
