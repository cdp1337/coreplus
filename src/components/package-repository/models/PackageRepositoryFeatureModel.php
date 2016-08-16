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
			'required' => true,
			'form' => [
			],
		],
		'type' => [
			'type' => Model::ATT_TYPE_ENUM,
			'required' => true,
			'options' => [
				'text' => 'Free-Form Text Field',
				'bool' => 'Boolean Yes/No',
				'enum' => 'Select Options',
			],
			'form' => [
			],
		],
		'options' => [
			'type' => Model::ATT_TYPE_TEXT,
			'form' => [
			],
		],
	];
	
	public static $Indexes = [
		'primary' => 'id',
		'unique:feature' => 'feature',
	];
	
	public function getOptionsAsArray(){
		if($this->get('type') == 'enum'){
			return array_map('trim', explode("\n", $this->get('options')));
		}
		else{
			return [];
		}
	}
}