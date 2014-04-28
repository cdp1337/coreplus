<?php
/**
 * Class file for the model FormBuilderFieldModel
 *
 * @package FormBuilder
 * @author Nicholas Hinsch <nicholas@eval.bz>
 */
class FormBuilderFieldModel extends Model {
	/**
	 * Schema definition for FormBuilderFieldModel
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
			'type'     => Model::ATT_TYPE_STRING,
			'required' => true,
			'null'     => false,
			'formtype' => 'system',
		),
		'type' => array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'maxlength' => 64,
			'form' => array(
				'title' => 'Field Type',
				'description' => 'The type of field to display on the custom form',
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			)
		),
		'name' => array(
			'type' => Model::ATT_TYPE_STRING,
			'null' => false,
			'required' => true,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),
		),
		'minlength' => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),
		),
		'maxlength' => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),
		),
		'readonly' => array(
			'type'     => Model::ATT_TYPE_BOOL,
			'required' => true,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),
		),
		'validation_pattern' => array(
			'type'     => Model::ATT_TYPE_STRING,
			'required' => false,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),
		),
		'placeholder' =>array(
			'type' => Model::ATT_TYPE_STRING,
			'required' => false,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),
		),
		'conditional' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'required' => true,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),

		),
		'conditional_element' => array(
			'type' => Model::ATT_TYPE_STRING,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),

		),
		'conditional_value' => array(
			'type' => Model::ATT_TYPE_STRING,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),

		),
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
	 * Index definition for FormBuilderFieldModel
	 * @todo Fill this in with your model indexes
	 *
	 * @static
	 * @var array
	 */
	public static $Indexes = array(
		'primary' => array('id'),
	);
}