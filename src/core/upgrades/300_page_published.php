<?php
/**
 * Upgrade script to update the published timestamp of all pages.
 * This is necessary because <= 3.0.0, pages did not have the published flag.
 *
 * @package Core
 */

// Get every page that currently exists
$factory = new ModelFactory('PageModel');
$pages = $factory->get();
foreach($pages as $page){
	/** @var $page PageModel */
	$page->set('published', $page->get('created'));
	$page->save();
}

