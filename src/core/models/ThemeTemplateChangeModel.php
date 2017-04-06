<?php
/**
 * Class file for the model ThemeEditorItemModel
 *
 * @package theme-editor
 * @author Nick Hinsch <nicholas@evalagency.com>
 */
class ThemeTemplateChangeModel extends Model {
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
			'formtype' => 'system',
		),
		'content' => array(
			'type' => Model::ATT_TYPE_DATA,
			'required' => true,
			'form' => array(
				'type' => 'textarea',
			),
		),
		'content_md5' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'comment' => 'An MD5 of the content, (used for external change verification checks)',
			'formtype' => 'disabled',
		),
		'comment' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'form' => array(
				'title' => 'Change Comment',
				'description' => 'If you want to comment about this change, enter something short and meaningful here.',
			),
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	/**
	 * Index definition for ThemeTemplateChangeModel
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);
}