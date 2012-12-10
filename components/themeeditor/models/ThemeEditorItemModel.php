<?php
/**
 * Class file for the model ThemeEditorItemModel
 *
 * @package theme-editor
 * @author Nick Hinsch <nicholas@eval.bz
 */
class ThemeEditorItemModel extends Model {
	/**
	 * Schema definition for ThemeEditorItemModel
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID
		),
		'filename' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
		),
		'content' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	/**
	 * Index definition for ThemeEditorItemModel
	 * @todo Fill this in with your model indexes
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);
}