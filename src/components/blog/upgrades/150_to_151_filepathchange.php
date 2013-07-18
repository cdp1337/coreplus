<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 1/17/13
 * Time: 10:20 PM
 * This is the upgrade file from 1.5.0 to 1.5.1
 *
 * Required because with the file input change in 2.6.0 returning core resolved paths,
 * the data in the database will contain that path now.
 */

$articles = BlogArticleModel::Find(['image != ']);

foreach($articles as $a){
	/** @var $a BlogArticleModel */
	$a->set('image', 'public/blog/' . $a->get('image'));
	$a->save();
}
// return