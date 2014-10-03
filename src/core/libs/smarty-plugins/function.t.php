<?php
/**
 * @package Core\Templates\Smarty
 * @since 2.1.3
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
 * @experimental
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @throws SmartyException
 *
 * @return string
 */
function smarty_function_t($params, $smarty){

	$key = array_shift($params);

	$ikey = \Core\i18n\Loader::Get($key);

	if(!$ikey){
		trigger_error('i18n key [' . $key . '] not located.', E_USER_NOTICE);
		return $key;
	}

	if(sizeof($params)){
		return call_user_func_array('sprintf', array_merge([$ikey], $params));
	}
	else{
		return $ikey;
	}
}
