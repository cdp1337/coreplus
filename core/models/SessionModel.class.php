<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


/**
 * Model for SessionModel
 * 
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 * 
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date 2011-07-24
 */
class SessionModel extends Model {
	public static $Schema = array(
		'session_id' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'required' => true,
			'null' => false,
		),
		'user_id' => array(
			'type' => Model::ATT_TYPE_INT,
			'default' => 0,
		),
		'ip_addr' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 39,
		),
		'data' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'default' => null,
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
		'primary' => array('session_id'),
	);

	// @todo Put your code here.

} // END class ConfigModel extends Model
