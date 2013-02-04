<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 1/17/13
 * Time: 10:20 PM
 * This is the upgrade file from 1.3.0 to 1.4.0
 *
 * Now that core supports selectable and non-selectable pages, I need to update all the existing blog articles to make them not selectable.
 */

$pages = PageModel::Find(array('baseurl LIKE /blog/article/view/%'));

foreach($pages as $page){
	$page->set('selectable', 0);
	$page->save();
}
// return