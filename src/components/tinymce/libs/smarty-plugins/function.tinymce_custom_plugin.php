<?php
/**
 * @package TinyMCE
 * @since 2.1.3
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Library or "Lesser" General Public License version 2.1
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
 * @alpha
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @throws SmartyException
 *
 * @return string
 */
function smarty_function_tinymce_custom_plugin($params, $smarty){
	
	if(!isset($params['src'])){
		throw new SmartyException('Please set the "src" attribute to your plugin source file.');
	}
	if(!isset($params['name'])){
		throw new SmartyException('Please set the "name" attribute to your plugin name.');
	}
	
	// Register this plugin with TinyMCE
	\TinyMCE\TinyMCE::$CustomPlugins[$params['name']] = $params['src'];
	
	// And queue TinyMCE to be included.
	\TinyMCE\TinyMCE::QueueInclude();
}
