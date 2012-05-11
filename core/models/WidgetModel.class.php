<?php
/**
 * Defines the schema for the WidgetModel table
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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
 * Description of PageModel
 *
 * @author powellc
 */
class WidgetModel extends Model{
	
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'comment' => '[Cached] Title of the page',
			'null' => true,
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
		'primary' => array('baseurl'),
	);
	
	/**
	 * Split a base url into its corresponding parts, controller method and parameters.
	 * 
	 * This ONLY supports widgets, and therefore does not support standard Controllers now rewriteurls.
	 * 
	 * @param string $url
	 * @return array
	 */
	public static function SplitBaseURL($base){
		
		if(!$base) return null;
		
		// Trim off both beginning and trailing slashes.
		$base = trim($base, '/');
		
		
		$args = null;
		// Support additional arguments
		if(($qpos = strpos($base, '?')) !== false){
			$argstring = substr($base, $qpos + 1);
			preg_match_all('/([^=&]*)={0,1}([^&]*)/', $argstring, $matches);
			$args = array();
			foreach($matches[1] as $k => $v){
				if(!$v) continue;
				$args[$v] = $matches[2][$k];
			}
			$base = substr($base, 0, $qpos);
		}
		
		// Logic for the Controller.
		$posofslash = strpos($base, '/');
		
		if($posofslash) $controller = substr($base, 0, $posofslash);
		else $controller = $base;

		// Preferred way of handling widget names.
		if(class_exists($controller . 'Widget')){
			switch(true){
				// 2.1 API
				case is_subclass_of($controller . 'Widget', 'Widget_2_1'):
				// 1.0 API
				case is_subclass_of($controller . 'Widget', 'Widget'):
					$controller = $controller . 'Widget';
					break;
				default:
					// Not a valid widget
					return null;
			}
		}
		// Not quite preferred way, but still works.
		elseif(class_exists($controller)){
			switch(true){
				// 2.1 API
				case is_subclass_of($controller, 'Widget_2_1'):
				// 1.0 API
				case is_subclass_of($controller, 'Widget'):
					$controller = $controller;
					break;
				default:
					// Not a valid widget
					return null;
			}
		}
		else{
			// Not even found!
			return null;
		}
		
		
		// Trim the base.
		if($posofslash !== false) $base = substr($base, $posofslash + 1);
		else $base = false;

		// Logic for the Method.
		//if(substr_count($base, '/') >= 1){
		if($base){
			
			$posofslash = strpos($base, '/');
			
			// The method can be extended.
			// This means that a method can be in the format of Sites/Edit, which should resolve to Sites_Edit.
			// This only taks effect if the method exists on the controller.
			if($posofslash){
				$method = str_replace('/', '_', $base);
				while(!method_exists($controller, $method) && strpos($method, '_')){
					$method = substr($method, 0, strrpos($method, '_'));
				}
			}
			else{
				$method = $base;
			}
			
			// Now trim the base again based on the length of the method.
			$base = substr($base, strlen($method) + 1);
		}
		else{
			// The controller may have an "Index" controller.  That doesn't need to be explictly called.
			$method = 'index';
		}

		// One last check that the method exists, (because there's only 1 scenerio that checks above)
		if(!method_exists($controller, $method)){
			return null;
		}
		
		
		// Provide some logic for security.
		// Keep any method starting with a '_' private by preventing
		// direct access from the browser.
		if($method{0} == '_') return null;


		// Logic for the parameters.
		$params = ($base !== false) ? explode('/', $base) : null;
		
		
		// Build these onto a base for a standardized callable URL.
		$baseurl = '/' . ((strpos($controller, 'Widget') == strlen($controller) - 6)? substr($controller, 0, -6) : $controller);
		// No need to add a method if it's the index.
		if(!($method == 'index' && !$params)) $baseurl .= '/' . str_replace('_', '/', $method);
		$baseurl .= ($params)? '/' . implode('/', $params) : '';
				
		return array('controller' => $controller, 'method' => $method, 'parameters' => $params, 'baseurl' => $baseurl);
	}
	
}
