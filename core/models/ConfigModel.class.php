<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


/**
 * Model for ConfigModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-06-09 01:14:48
 */
class ConfigModel extends Model {
	public static $Schema = array(
		'key' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 256,
			'required' => true,
			'null' => false,
		),
		'type' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('string','int','boolean','enum','set'),
			'default' => 'string',
			'null' => false,
		),
		'default_value' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
		'value' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
		'options' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
			'default' => null,
			'null' => true,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null' => true,
		),
		'mapto' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'default' => null,
			'comment' => 'The define constant to map the value to on system load.',
			'null' => true,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED
		)
	);
	
	public static $Indexes = array(
		'primary' => array('key'),
	);
	
	/**
	 * Get either the set value or the default value if that is null.
	 * 
	 * This value will also be typecasted to the correct type.
	 * 
	 * @return mixed 
	 */
	public function getValue(){
		$v = $this->get('value');
		if($v === null) $v = $this->get('default');
		
		switch ($this->get('type')) {
			case 'int':
				return (int) $v;
			case 'boolean':
				return ($v == '1' || $v == 'true') ? true : false;
			case 'set':
				return array_map('trim', explode('|', $v));
			default:
				return $v;
		}
	}

} // END class ConfigModel extends Model
