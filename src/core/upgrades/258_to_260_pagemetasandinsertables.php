<?php
/**
 * Upgrade file to update the page insertables on 2.6.0
 *
 * @package Core
 */

// Give me the page system!
if(!class_exists('PageModel')) require_once(ROOT_PDIR . 'core/models/PageModel.class.php');
if(!class_exists('PageMetaModel')) require_once(ROOT_PDIR . 'core/models/PageMetaModel.class.php');

// If this site was not in multisite mode.... the insertables and metadata may not have matched up 1-to-1 with the page's site.
// In 2.6.0, this relationship is a little more strictly enforced.
if(!(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled())){
	// Get every page that currently exists
	$pages = PageModel::FindRaw();
	foreach($pages as $page){
		// Find the insertables that belong to this page and update their site id.
		$insertables = InsertableModel::Find(['baseurl = ' . $page['baseurl']]);
		foreach($insertables as $ins){
			/** @var $ins InsertableModel */
			$ins->set('site', $page['site']);
			$ins->save();
		}
	}
}
