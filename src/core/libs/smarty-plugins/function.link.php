<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
 * Resolve a dynamic or static link with smarty.
 *
 * @todo Finish documentation of smarty_function_link
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string
 */
function smarty_function_link($params, $smarty){
	
	$assign= (isset($params['assign']))? $params['assign'] : false;
	
	// I don't really care what the parameter's called to be honest...
	if(isset($params['href'])) $href = $params['href'];
	elseif(isset($params['link'])) $href = $params['link'];
	elseif(isset($params['to'])) $href = $params['to'];
	elseif(isset($params[0])) $href = $params[0];
	else $href = '/';

	$originalHref = $href;
	
	$href = \Core\resolve_link($href);

	if(!$href && strpos($originalHref, 'public/') === 0){
		$file = \Core\Filestore\Factory::File($originalHref);
		if($file->exists()){
			$href = $file->getURL();
		}
	}

	if(isset($params['ssl'])){
		// Perform SSL translation of some sort.
		$ssl = (isset($_SERVER['HTTPS']));

		if(
			($ssl && $params['ssl'] == 'auto') ||
			$params['ssl'] == '1' ||
			$params['ssl'] == 'true'
		){
			$href = str_replace('http://', 'https://', $href);
		}
		elseif(!$params['ssl']){
			$href = str_replace('https://', 'http://', $href);
		}
	}

	if($assign){
		$smarty->assign($assign, $href);
	}
	else{
		return $href;
	}
}
