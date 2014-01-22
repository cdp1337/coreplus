<?php
/**
 * Smarty {script} block
 * 
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
 * Include a script in the main template
 * 
 * Usage:
 * <pre>
 * // Include jquery on this page.
 * {script name="jquery"}{/script}
 * 
 * // Another way to call javascript libraries
 * {script library="jquery"}{/script}
 * 
 * // Traditional "src" tags work too
 * {script src="http://blah.tld/js/jquery.js"}{/script}
 * 
 * // Because it's a block-type tag... you can also do
 * {script}
 *   // This section is automatically plugged into a <script> tag.
 * {/script}
 * 
 * // Specifying the location of the target rendering area is also allowable.
 * // This is useful for scripts that expect to be at the end of the body tag.
 * {script src="js/mylib/foo.js" location="head"}{/script}
 * {script src="js/mylib/foo.js" location="foot"}{/script}
 * </pre>
 * 
 * 
 * @param type $params
 * @param type $template 
 */
function smarty_block_script($params, $innercontent, $template, &$repeat){
	// This only needs to be called once.
	if($repeat) return;

	// A script library name is provided.
	if(isset($params['name'])){
		if(!Core::LoadScriptLibrary($params['name'])){
			throw new SmartyException('Unable to load script library ' . $params['name']);
		}
	}
	// I guess using "library" to indicate the desired library would make sense too....
	elseif(isset($params['library'])){
		if(!Core::LoadScriptLibrary($params['library'])){
			throw new SmartyException('Unable to load script library ' . $params['library']);
		}
	}
	// Allow {script} tags to be called with the traditional src attribute.
	// These are most common for external resources, like facebook connect or google tools
	// but also useful for any standard asset.
	elseif(isset($params['src'])){
		$loc = (isset($params['location']))? $params['location'] : 'head';
		\Core\view()->addScript($params['src'], $loc);
	}
	// a script tag can be called with no parameters, it is after all a script tag....
	elseif($innercontent){
		// Does this content have a <script> tag already around it?
		if(strpos($innercontent, '<script') === false){
			$innercontent = '<script type="text/javascript">' . $innercontent . '</script>';
		}
		$loc = (isset($params['location']))? $params['location'] : 'head';
		\Core\view()->addScript($innercontent, $loc);
	}
}
