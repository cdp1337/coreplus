<?php
/**
 * Upgrade file for 2.4.2 to 2.4.3 to handle setting the selectable flag appropriately.  Admin pages should
 * not be selectable.
 *
 * @package Core
 */


// Give me the page system!
require_once(ROOT_PDIR . 'core/models/PageModel.class.php');

// Get every page that currently exists
$factory = new ModelFactory('PageModel');
$factory->whereGroup(
	'OR',
	array(
		'admin = 1',
		'baseurl LIKE /user/%',
	)
);
$pages = $factory->get();
foreach($pages as $page){
	/** @var $page PageModel */
	$page->set('selectable', '0');
	$page->save();
}
