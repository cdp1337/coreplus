<?php
/**
 * File for class TemplateCssModel definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130411.1425
 */


/**
 * Simple model for storing which templates have which optional stylesheet enabled.
 */
class TemplateCssModel extends Model{
	public static $Schema = array(
		'template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 100,
			'required' => true,
		),
		'css_asset' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 100,
			'required' => true,
		),
		'enabled' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0
		)
	);

	public static $Indexes = array(
		'primary' => array('template', 'css_asset'),
	);
}
