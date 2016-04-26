<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 12:14 PM
 */
class PackageRepositoryFeatureModel extends Model {
	public static $Schema = [
		'id' => [
			'type' => Model::ATT_TYPE_UUID,
		],
		'feature' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'type' => [
			'type' => Model::ATT_TYPE_STRING,
			'required' => true,
			'maxlength' => 64,
			'form' => array(
				'title' => 'Field Type',
				'description' => 'The type of field to display on the custom form',
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			)
		],
		'title' => [
			'type' => Model::ATT_TYPE_STRING,
			'null' => false,
			'required' => true,
			'form' => array(
				'group' => 'Form Elements',
				'grouptype' => 'tabs',
			),
		],
		'options' => [
			'type' => Model::ATT_TYPE_DATA,
			'encoding' => Model::ATT_ENCODING_JSON,
			'comment' => 'JSON encoded set of data',
		],
	];
	
	public static $Indexes = [
		'primary' => 'id',
		'unique:feature' => 'feature',
	];
}