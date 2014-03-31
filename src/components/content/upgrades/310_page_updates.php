<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140228.1049
 * @package PackageName
 * 
 * Created with PhpStorm.
 */

$fac = new ModelFactory('PageModel');
$fac->where('baseurl LIKE /content/view/%');
foreach($fac->get() as $model){
	/** @var PageModel $model */
	$base = '/content/view/';
	$id   = substr($model->get('baseurl'), strlen($base));

	$model->set('component', 'content');
	$model->set('baseurl', '/content/view/' . $id);
	$model->set('editurl', '/content/edit/' . $id);
	$model->set('deleteurl', '/content/delete/' . $id);
	$model->save();
}
