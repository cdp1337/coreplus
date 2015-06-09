<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * Resolve an asset to a fully-resolved URL from within Smarty.
 *
 * This is the recommended way to handle asset url resolving from within templates.
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string
 */
function smarty_function_asset($params, $smarty){

	// I don't really care what it's called!
	if(isset($params['file']))     $file = $params['file'];
	elseif(isset($params['src']))  $file = $params['src'];
	elseif(isset($params['href'])) $file = $params['href'];
	elseif(isset($params[0]))      $file = $params[0];
	else                           $file = 'images/404.jpg';

	$f = \Core\resolve_asset($file);

	if(isset($params['assign'])) $smarty->assign($params['assign'], $f);
	else return $f;
}
