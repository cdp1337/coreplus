<?php
/**
 *
 * @package User
 * @since 2.1.0
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
 *
 */

function smarty_block_permission($params, $innercontent, $template, &$repeat){

	if($repeat) return;
	
	// Evaluate this block only if the permission checks through.
	$accessstring = false;
	foreach($params as $k => $v){
		switch($k){
			case 'permission':
			case 'p':
			case 'perm':
			case 'string':
			case 'accessstring':
				$accessstring = $v;
				break 2;
		}
	}

	if($accessstring === false){
		if(DEVELOPMENT_MODE) echo "Alert, no access string requested, using '*' as default";
		$accessstring = '*';
	}

	if($accessstring == '*'){
		// allow all.... easy!
		return $innercontent;
	}
	elseif(Core::User()->checkAccess($accessstring)){
		return $innercontent;
	}
}