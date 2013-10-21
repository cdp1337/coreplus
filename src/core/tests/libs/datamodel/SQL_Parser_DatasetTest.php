<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131017.0955
 * @package PackageName
 * 
 * Created with JetBrains PhpStorm.
 */

class SQL_Parser_DatasetTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test that a simple SELECT statement parses
	 */
	public function testSelectBasic(){
		$query = 'SELECT * FROM `sometableblah` WHERE `key` = "apple foo"';

		$dss = SQL_Parser_Dataset::ConstructAndParse($query, SQL_Parser::DIALECT_MYSQL);

		$this->assertEquals(1, sizeof($dss));

		$ds = $dss[0];
		$this->assertInstanceOf('Dataset', $ds);
		/** @var Dataset $ds */

		$this->assertEquals('sometableblah', $ds->_table);
		$this->assertEquals('*', $ds->_selects[0]);
		$this->assertEquals(1, sizeof($ds->getWhereClause()->getStatements()));

		/** @var DatasetWhere $statement */
		$statement = $ds->getWhereClause()->getStatements()[0];
		$this->assertEquals('key', $statement->field);
		$this->assertEquals('=', $statement->op);
		$this->assertEquals('apple foo', $statement->value);
	}

	/**
	 * Test that a slightly more complex SELECT statement parses
	 */
	public function testSelectComplicated(){
		$query = 'SELECT row1, `key` FROM someThiNg WHERE `key` = "apple foo" AND `key2` > 23 AND (`subkey1` LIKE "%twelve" OR `subkey2` IS NOT NULL)';

		$dss = SQL_Parser_Dataset::ConstructAndParse($query, SQL_Parser::DIALECT_MYSQL);

		$this->assertEquals(1, sizeof($dss));

		$ds = $dss[0];
		$this->assertInstanceOf('Dataset', $ds);
		/** @var Dataset $ds */

		$this->assertEquals('someThiNg', $ds->_table);
		$this->assertEquals('row1', $ds->_selects[0]);
		$this->assertEquals('key', $ds->_selects[1]);
		$this->assertEquals(3, sizeof($ds->getWhereClause()->getStatements()));

		/** @var DatasetWhere $statement1 */
		$statement1 = $ds->getWhereClause()->getStatements()[0];
		/** @var DatasetWhere $statement2 */
		$statement2 = $ds->getWhereClause()->getStatements()[1];
		/** @var DatasetWhereClause $statement3 */
		$statement3 = $ds->getWhereClause()->getStatements()[2];

		$this->assertInstanceOf('DatasetWhere', $statement1);
		$this->assertInstanceOf('DatasetWhere', $statement2);
		$this->assertInstanceOf('DatasetWhereClause', $statement3);

		$this->assertEquals('key', $statement1->field);
		$this->assertEquals('=', $statement1->op);
		$this->assertEquals('apple foo', $statement1->value);

		$this->assertEquals('key2', $statement2->field);
		$this->assertEquals('>', $statement2->op);
		$this->assertEquals('23', $statement2->value);

		$this->assertEquals('OR', $statement3->getSeparator());
		/** @var DatasetWhere $sub1 */
		$sub1 = $statement3->getStatements()[0];
		/** @var DatasetWhere $sub2 */
		$sub2 = $statement3->getStatements()[1];

		$this->assertInstanceOf('DatasetWhere', $sub1);
		$this->assertInstanceOf('DatasetWhere', $sub2);

		$this->assertEquals('subkey1', $sub1->field);
		$this->assertEquals('like', $sub1->op);
		$this->assertEquals('%twelve', $sub1->value);

		$this->assertEquals('subkey2', $sub2->field);
		$this->assertEquals('is not', $sub2->op);
		$this->assertNull($sub2->value);
	}

	/**
	 * Test that a basic INSERT statement parses
	 */
	public function testInsertBasic(){
		$query = "INSERT INTO states (id, state, code) VALUES (33, 'Oregon', 'OR');";

		$dss = SQL_Parser_Dataset::ConstructAndParse($query, SQL_Parser::DIALECT_MYSQL);
		$this->assertEquals(1, sizeof($dss));

		$ds = $dss[0];
		$this->assertInstanceOf('Dataset', $ds);
		/** @var Dataset $ds */

		$this->assertEquals('states', $ds->_table);
		$this->assertEquals('insert', $ds->_mode);
		$this->assertEquals(3, sizeof($ds->_sets));
		$this->assertEquals(33, $ds->_sets['id']);
		$this->assertEquals('Oregon', $ds->_sets['state']);
		$this->assertEquals('OR', $ds->_sets['code']);
	}

	/**
	 * Test that a basic INSERT statement with multiple inserts parses
	 */
	public function testInsertMultiple(){
		$query = "INSERT INTO states (id, state, code) VALUES (33, 'Oregon', 'OR'), (34, 'Blah', 'BL'), (35, 'Foo', 'FO');";

		$dss = SQL_Parser_Dataset::ConstructAndParse($query, SQL_Parser::DIALECT_MYSQL);
		$this->assertEquals(3, sizeof($dss));

		$ds = $dss[0];
		$this->assertInstanceOf('Dataset', $ds);
		/** @var Dataset $ds */

		$this->assertEquals('states', $ds->_table);
		$this->assertEquals('insert', $ds->_mode);
		$this->assertEquals(3, sizeof($ds->_sets));
		$this->assertEquals(33, $ds->_sets['id']);
		$this->assertEquals('Oregon', $ds->_sets['state']);
		$this->assertEquals('OR', $ds->_sets['code']);

		$ds = $dss[1];
		$this->assertInstanceOf('Dataset', $ds);
		/** @var Dataset $ds */

		$this->assertEquals('states', $ds->_table);
		$this->assertEquals('insert', $ds->_mode);
		$this->assertEquals(3, sizeof($ds->_sets));
		$this->assertEquals(34, $ds->_sets['id']);
		$this->assertEquals('Blah', $ds->_sets['state']);
		$this->assertEquals('BL', $ds->_sets['code']);

		$ds = $dss[2];
		$this->assertInstanceOf('Dataset', $ds);
		/** @var Dataset $ds */

		$this->assertEquals('states', $ds->_table);
		$this->assertEquals('insert', $ds->_mode);
		$this->assertEquals(3, sizeof($ds->_sets));
		$this->assertEquals(35, $ds->_sets['id']);
		$this->assertEquals('Foo', $ds->_sets['state']);
		$this->assertEquals('FO', $ds->_sets['code']);
	}

	public function testTruncateBasic(){
		$query = "TRUNCATE geo_country;";

		$dss = SQL_Parser_Dataset::ConstructAndParse($query, SQL_Parser::DIALECT_MYSQL);
		$this->assertEquals(1, sizeof($dss));

		$ds = $dss[0];
		$this->assertInstanceOf('Dataset', $ds);
		/** @var Dataset $ds */

		$this->assertEquals('delete', $ds->_mode);
		$this->assertEquals('geo_country', $ds->_table);
		$this->assertNull($ds->_where);
	}

	// UPDATE authors SET first_name = 'Louisa May' WHERE first_name = 'Luoisa May';

}