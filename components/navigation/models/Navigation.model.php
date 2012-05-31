<?php
/**
 * Model for NavigationModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 2011-06-09 01:14:48
 */
class NavigationModel extends Model {
	public static $Schema = array(
		'id'      => array(
			'type'     => Model::ATT_TYPE_ID,
			'required' => true,
			'null'     => false,
		),
		'name'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null'      => false,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary'     => array('id'),
		'unique:name' => array('name'),
	);


	public function __construct($key = null) {
		$this->_linked = array(
			'Widget'          => array(
				'link' => Model::LINK_HASONE,
				'on'   => 'baseurl',
			),
			'NavigationEntry' => array(
				'link' => Model::LINK_HASMANY,
				'on'   => array('navigationid' => 'id'),
			),
		);

		parent::__construct($key);
	}


	public function get($k) {
		$k = strtolower($k);
		switch ($k) {
			case 'baseurl':
				return '/Navigation/View/' . $this->_data['id'];
				break;
			default:
				return parent::get($k);
		}
	}

	/*
	public function save(){
		// Make sure the linked widget is kept in sync.
		$this->getLink('Widget')->set('title', $this->get('name'));
		return parent::save();
	}
	*/

} // END class NavigationModel extends Model
