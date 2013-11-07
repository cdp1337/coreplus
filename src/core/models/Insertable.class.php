<?php
/**
 * Defines the schema for the Insertable table
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

/**
 * Model for InsertableModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 2011-06-09 01:14:48
 */
class InsertableModel extends Model {
	public static $Schema = array(
		'site' => array(
			'type' => Model::ATT_TYPE_SITE,
			'formtype' => 'system',
		),
		'baseurl' => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required'  => true,
			'null'      => false,
		),
		'name'    => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required'  => true,
			'null'      => false,
		),
		'value'   => array(
			'type' => Model::ATT_TYPE_TEXT,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('site', 'baseurl', 'name'),
	);

	public function __construct(){
		// This system now has a combined primary key.
		// HOWEVER, construction of the model should still be allowed to be performed with simply the baseurl.
		// The first part of the key can be assumed.
		if(func_num_args() == 2){
			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				$site = MultiSiteHelper::GetCurrentSiteID();
			}
			else{
				$site = 0;
			}
			$baseurl = func_get_arg(0);
			$name = func_get_arg(1);
			parent::__construct($site, $baseurl, $name);
		}
		elseif(func_num_args() == 3){
			$site = func_get_arg(0);
			$baseurl = func_get_arg(1);
			$name  = func_get_arg(2);
			parent::__construct($site, $baseurl, $name);
		}
		else{
			parent::__construct();
		}
	}
} // END class InsertableModel extends Model
