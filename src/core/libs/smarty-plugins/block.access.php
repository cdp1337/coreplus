<?php
/**
 * @package Core
 * @since 1.9
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
 * Only render the inside content if the access string passes.
 *
 * @param $params array
 * @param $innercontent string
 * @param $template Smarty
 * @param $repeat boolean
 *
 * @return string
 */
function smarty_block_access($params, $innercontent, $template, &$repeat){
	// This only needs to be called once.
	if($repeat) return '';

	if(sizeof($params) == 2){

		$inv = false;
		$str = null;

		foreach($params as $v){
			if($v == '!' || $v == '0'){
				$inv = true;
			}
			else{
				$str = $v;
			}
		}
	}
	elseif(sizeof($params) == 1){
		$inv = false;
		$str = $params[0];
	}
	else{
		return 'Unsupported number of arguments for "{access}", please set only one access string or one access string and the "!" sign to inverse the result.';
	}

	$result = \Core\user()->checkAccess($str);

	return ($result xor $inv) ? $innercontent : null;
}