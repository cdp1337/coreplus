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
	public static $DMI;

	public static $DMIName;
	
	public static $Schema;

	public function testCreateTable(){
		// This should be a DMI_Backend object!
		$this->assertInstanceOf('Core\\Datamodel\\BackendInterface', self::$DMI, '\Core\db() did not return a valid Core\\Datamodel\\BackendInterface object!');

		if(self::$DMI->tableExists('test_table_create')){
			// Delete it first!
			$this->assertTrue(self::$DMI->dropTable('test_table_create'));
		}

		$this->assertTrue(self::$DMI->createTable('test_table_create', self::$Schema));

		$this->assertTrue(self::$DMI->tableExists('test_table_create'));
	}

	/**
	 * @depends testCreateTable
	 */
	public function testModifyTable(){
		// Modify the table now.
		$newcol = new \Core\Datamodel\Columns\SchemaColumn();
		$newcol->field = 'foo';
		$newcol->type = Model::ATT_TYPE_TEXT;
		$newcol->maxlength = 45;
		self::$Schema->definitions['foo'] = $newcol;
		self::$Schema->order[] = 'foo';

		$ret = self::$DMI->modifyTable('test_table_create', self::$Schema);
		$this->assertTrue(is_array($ret));

		// Ensure that the new column exists.
		$describe = self::$DMI->describeTable('test_table_create');
		$this->assertInstanceOf('Core\\Datamodel\\Schema', $describe);
		$this->assertEquals(8, sizeof($describe->definitions));
	}

	/**
	 * Test that the Execute method properly handles a generic Dataset object.
	 */
	public function testExecute(){
		$ds = \Core\Datamodel\Dataset::Init()
			->select('*')
			->table('component');

		self::$DMI->execute($ds);

		$this->assertGreaterThan(0, sizeof($ds->num_rows));
	}

	/**
	 * Ensure that tableExists returns true for a table that exists.
	 */
	public function testTableExists(){
		$tablethatexists = 'component';

		$this->assertTrue(self::$DMI->tableExists($tablethatexists));
	}

	/**
	 * The inverse of tableExists true.  If a table does not exist, it needs to indicate such!
	 */
	public function testTableExistsNot(){
		$tablethatdoesnot = 'something_completely_random_and_hopefully_does_not_exist';
		$this->assertFalse(self::$DMI->tableExists($tablethatdoesnot));
	}

	/**
	 * Test the showTables method of the interface
	 */
	public function testShowTables(){
		$tables = self::$DMI->showTables();
		$this->assertGreaterThan(1, sizeof($tables));
	}

	/**
	 * @depends testModifyTable
	 */
	public function testDropTable(){
		if(self::$DMI->tableExists('test_table_create')){
			// Delete it last!
			$this->assertTrue(self::$DMI->dropTable('test_table_create'));
		}
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

	/**
	 * Ensure that a valid DMI object can be created in the system.
	 */
	public static function setUpBeforeClass(){
		self::$DMI = \Core\db();
		self::$DMIName = get_class(self::$DMI);

		self::$Schema = new \Core\Datamodel\Schema();

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
			$col = new \Core\Datamodel\Columns\SchemaColumn();
			$col->field = $key;
			$col->type = $dat['type'];
			if(isset($dat['default']))   $col->default = $dat['default'];
			if(isset($dat['options']))   $col->options = $dat['options'];
			if(isset($dat['null']))      $col->null = $dat['null'];
			if(isset($dat['maxlength'])) $col->maxlength = $dat['maxlength'];
			if(isset($dat['autoinc']))   $col->autoinc = $dat['autoinc'];
			self::$Schema->definitions[$key] = $col;
			self::$Schema->order[] = $key;
		}

		self::$Schema->indexes['primary'] = ['id'];
	}
}
