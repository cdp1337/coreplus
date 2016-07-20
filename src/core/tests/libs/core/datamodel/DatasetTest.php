<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 7/6/16
 * Time: 9:30 PM
 */

namespace Core\Datamodel;

/**
 * This will test the currently selected BackendInterface in the system.
 *
 * @package Core\Datamodel
 */
class DatasetTest extends \PHPUnit_Framework_TestCase {

	/**
	 * 
	 */
	public function testSelect() {

		$ds = new Dataset();
		$ds->select('*');
		
		// After issuing a select * request, the mode should be GET
		$this->assertEquals(Dataset::MODE_GET, $ds->getMode());
		// The selects should be populated
		$this->assertTrue(is_array($ds->_selects));
		// and it should contain 1 field.
		$this->assertEquals(1, sizeof($ds->_selects));
	}

	/**
	 * 
	 */
	public function testInsert() {
		
		$ds = new Dataset();
		$ds->insert('foo', 'key');
		
		// Issuing an insert request sets the mode to INSERT.
		$this->assertEquals(Dataset::MODE_INSERT, $ds->getMode());

		$inserts = $ds->getInserts();
		
		$this->assertTrue(is_array($inserts));
		$this->assertEquals('key', $inserts['foo']);
	}

	/**
	 * 
	 */
	public function testUpdate() {
		
		$ds = new Dataset();
		$ds->update('foo', 'key');

		// Issuing an insert request sets the mode to INSERT.
		$this->assertEquals(Dataset::MODE_UPDATE, $ds->getMode());

		$updates = $ds->getUpdates();

		$this->assertTrue(is_array($updates));
		$this->assertEquals('key', $updates['foo']);
	}
	
	public function testInsertUpdate(){
		$ds = new Dataset();
		$ds->insert('newkey', 'val1');
		$ds->update('oldkey', 'val2');
		$ds->delete('oldkey', 'badval');

		$this->assertEquals(Dataset::MODE_INSERTUPDATE, $ds->getMode());

		$inserts = $ds->getInserts();
		$updates = $ds->getUpdates();
		$deletes = $ds->getDeletes();

		$this->assertTrue(is_array($inserts));
		$this->assertTrue(is_array($updates));
		$this->assertTrue(is_array($deletes));
		
		$this->assertEquals('val1', $inserts['newkey']);
		$this->assertEquals('val2', $updates['oldkey']);
		$this->assertEquals('badval', $deletes['oldkey']);
	}

	/**
	 * 
	 */
	public function testRenameColumn() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 * 
	 */
	public function testDelete() {
		$ds = new Dataset();
		$ds->delete('oldkey', 'badval');

		// Deleting a specific value triggers the INSERT/UPDATE/DELETE mode.
		// This is different from the entire record delete because one entire record is NOT being deleted.
		$this->assertEquals(Dataset::MODE_INSERTUPDATE, $ds->getMode());

		$deletes = $ds->getDeletes();
		$this->assertTrue(is_array($deletes));
		$this->assertEquals('badval', $deletes['oldkey']);
		
		// You can also issue a simple delete command.
		$ds = new Dataset();
		$ds->delete();

		$this->assertEquals(Dataset::MODE_DELETE, $ds->getMode());

		$deletes = $ds->getDeletes();
		$this->assertTrue(is_array($deletes));
		$this->assertEquals('*', $deletes['*']);
	}

	/**
	 * Set this dataset to only return the count of records.
	 *
	 * @return Dataset
	 */
	public function testCount() {
		$ds = new Dataset();
		$ds->count();

		$this->assertEquals(Dataset::MODE_COUNT, $ds->getMode());
	}

	public function testSetID() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	public function testGetID() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 *
	 *
	 */
	public function testTable() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 *
	 */
	public function testUnique() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 * 
	 */
	public function testGetWhereClause() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 * 
	 */
	public function testWhere() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 * 
	 */
	public function testWhereGroup() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 * 
	 */
	public function testLimit() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 * 
	 */
	public function testOrder() {
		// @todo Finish this
		$this->markTestIncomplete();
	}


	/**
	 *
	 */
	public function testExecute() {
		// @todo Finish this
		$this->markTestIncomplete();
	}

	/**
	 * 
	 */
	public function testExecuteAndGet() {
		// If limit == 1 and only one select was issued, that singular value or null is returned.
		$r = Dataset::Init()
			->select('name')
			->table('component')
			->limit(1)
			->executeAndGet();
		
		$this->assertTrue(is_scalar($r));
		$this->assertGreaterThan(0, strlen($r));


		// If limit == 1 and more than one select was issued, an associative array is returned.
		$r = Dataset::Init()
			->select('*')
			->table('component')
			->limit(1)
			->executeAndGet();
		
		$this->assertArrayHasKey('name', $r);
		$this->assertArrayHasKey('version', $r);
		$this->assertArrayHasKey('enabled', $r);

		$this->assertGreaterThanOrEqual(3, sizeof($r));


		// If select contains 1 key and it's not "*", an indexed array is returned containing all results.
		$r = Dataset::Init()
			->select('*')
			->table('component')
			->executeAndGet();

		$this->assertGreaterThanOrEqual(1, sizeof($r));
		$this->assertArrayHasKey(0, $r);
		
		
		// Counts also get their special version; it's just a number!
		$r = Dataset::Init()
			->count()
			->table('component')
			->executeAndGet();
		
		$this->assertGreaterThan(1, $r);
	}

}
