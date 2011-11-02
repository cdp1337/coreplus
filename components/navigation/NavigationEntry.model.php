<?php
/**
 * Model for NavigationEntryModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class NavigationEntryModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'navigationid' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'parentid' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 6,
			'null' => false,
		),
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'null' => false,
		),
		'target' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 16,
			'null' => false,
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'null' => false,
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
		'primary' => array('id'),
	);

	/**
	 * Based on the type of this entry, ie: int or ext, resolve the URL fully.
	 * 
	 * @return string
	 */
    public function getResolvedURL(){
		switch($this->get('type')){
			case 'int':
				return Core::ResolveLink($this->get('baseurl'));
				break;
			case 'ext':
				if(strpos(substr($this->get('baseurl'), 0, 6), '://') !== false) return $this->get('baseurl');
				else return 'http://' . $this->get('baseurl');
				break;
		}
	}

} // END class NavigationEntryModel extends Model
