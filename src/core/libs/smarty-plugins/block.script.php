<?php
/**
 * Smarty {script} block
 * 
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
 * Include a script in the main template in either the head or at the end of the body
 *
 * {script} is the preferred way to load javascript from a template.
 *
 * #### Smarty Parameters
 *
 *  * library
 *    * string, the name of the registered script library to include
 *    * ex: "jquery", "jqueryui", etc.
 *  * src
 *    * string, full resolved URL or Core-resolvable location of the asset.
 *  * location
 *    * string, "foot", or "head".  Foot will append the script block at the end of the body, head inside the &lt;head/&gt; tag.
 *
 * #### Example Usage
 *
 * Include jquery on this page.
 * <pre>
 * {script library="jquery"}{/script}
 * </pre>
 *
 * Another way to call javascript libraries
 * <pre>
 * {script library="jquery"}{/script}
 * </pre>
 *
 * Traditional "src" tags work too
 * <pre>
 * {script src="http://blah.tld/js/jquery.js"}{/script}
 * </pre>
 *
 * Because it's a block-type tag... you can also do
 * <pre>
 * {script}
 * // This section is automatically plugged into a &lt;script&gt; tag.
 * {/script}
 * </pre>
 *
 * Specifying the location of the target rendering area is also allowable.
 * This is useful for scripts that expect to be at the end of the body tag.
 * <pre>
 * {script src="js/mylib/foo.js" location="head"}{/script}
 * {script src="js/mylib/foo.js" location="foot"}{/script}
 * </pre>
 *
 * @param array       $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param string|null $content Null on opening pass, rendered source of the contents inside the block on closing pass
 * @param Smarty      $smarty  Parent Smarty template object
 * @param boolean     $repeat  True at the first call of the block-function (the opening tag) and
 * false on all subsequent calls to the block function (the block's closing tag).
 * Each time the function implementation returns with $repeat being TRUE,
 * the contents between {func}...{/func} are evaluated and the function implementation
 * is called again with the new block contents in the parameter $content.
 *
 * @throws SmartyException
 */
function smarty_block_script($params, $content, $smarty, &$repeat){
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
	elseif($content){
		// Does this content have a <script> tag already around it?
		if(strpos($content, '<script') === false){
			$content = '<script type="text/javascript">' . $content . '</script>';
		}
		$loc = (isset($params['location']))? $params['location'] : 'head';
		\Core\view()->addScript($content, $loc);
	}
}
