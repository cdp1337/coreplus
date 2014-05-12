<?php
/**
 * Upgrade file to migrate the existing navigation widgets to the new schema, (edit and delete URLs)
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140228.1049
 * @package Blog
 */

$fac = new ModelFactory('WidgetModel');
$fac->where('baseurl LIKE /navigation/view/%');
foreach($fac->get() as $model){
	/** @var WidgetModel $model */
	$id = substr($model->get('baseurl'), 17);
	$model->set('baseurl', '/navigation/view/' . $id);
	$model->set('editurl', '/navigation/edit/' . $id);
	$model->set('deleteurl', '/navigation/delete/' . $id);
	$model->save();
}