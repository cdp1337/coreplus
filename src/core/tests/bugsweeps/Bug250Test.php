<?php

/**
 * Class Bug250Test
 * Test for pages that are not defined as "selectable" will not appear in the dropdown.
 */
class Bug250Test extends PHPUnit_Framework_TestCase {

	public function testBug(){
		$selectablepage = PageModel::Find(['selectable' => 1], null);
		$notselectablepage = PageModel::Find(['selectable' => 0], null);

		// Both of these should return an array!
		$this->assertNotEmpty($selectablepage);
		$this->assertNotEmpty($notselectablepage);

		// And this needs to be an array too.
		$pagearray = PageModel::GetPagesAsOptions();
		$this->assertNotEmpty($pagearray);

		// Run through each selectable page and make sure that it displays in the list.
		foreach($selectablepage as $page){
			$this->assertInstanceOf('PageModel', $page);
			$this->assertArrayHasKey($page->get('baseurl'), $pagearray);
		}

		foreach($notselectablepage as $page){
			$this->assertInstanceOf('PageModel', $page);
			$this->assertArrayNotHasKey($page->get('baseurl'), $pagearray);
		}
	}
}