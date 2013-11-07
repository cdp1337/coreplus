<?php
/**
 * @package Core Plus\Core
 * @since 1.9
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
 * @param $params
 * @param $template
 *
 * @return string
 */
function smarty_function_link($params, $template){
	
	$assign= (isset($params['assign']))? $params['assign'] : false;
	
	// I don't really care what the parameter's called to be honest...
	if(isset($params['href'])) $href = $params['href'];
	elseif(isset($params['link'])) $href = $params['link'];
	elseif(isset($params['to'])) $href = $params['to'];
	
	$href = Core::ResolveLink($href);
	
    return $assign ? $template->assign($assign, $href) : $href;
}
