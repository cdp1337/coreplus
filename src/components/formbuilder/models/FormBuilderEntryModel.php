<?php
/**
 * Class file for the model FormBuilderEntryModel
 *
 * @package FormBuilder
 * @author Nicholas Hinsch <nicholas@eval.bz>
 */
class FormBuilderEntryModel extends Model {
	/**
	 * Schema definition for FormBuilderEntryModel
	 * @todo Fill this in with your model structure
	 *
	 * @static
	 * @var array
	 */
	public static $Schema = array(
		'id' => [
			'type' => Model::ATT_TYPE_UUID,
		],
		'formid' => array(
			'type'     => Model::ATT_TYPE_INT,
			'required' => true,
			'null'     => false,
			'formtype' => 'system',
		),

		'custom_fields' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'formtype' => 'disabled',
		),
		'ip' => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'formtype' => 'hidden',
		),
		'user_id'    => array(
			'type'    => Model::ATT_TYPE_INT,
			'default' => 0,
			'formtype' => 'disabled',
		),
		'useragent' => array(
			'type'     => Model::ATT_TYPE_TEXT,
			'required' => false,
			'formtype' => 'hidden',
		),
		'referrer'  => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'formtype' => 'hidden',
		),
		'junk' => [
			'type' => Model::ATT_TYPE_BOOL,
			'default' => false,
			'formtype' => 'disabled',
			'comment' => 'Set to true when flagged as junk.',
		],
		'created'   => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated'   => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	/**
	 * Index definition for FormEntryModel
	 * @todo Fill this in with your model indexes
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);
}