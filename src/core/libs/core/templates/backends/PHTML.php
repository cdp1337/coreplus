<?php
/**
 * PHP-Based template system.
 *
 * These tempalates are nothing more than a php file that contains HTML.  They're not recommended, but can be used when
 * performance or compatibility conflicts arise with the Smarty-based templates.
 *
 * Javascript template files are a perfect example; they tend to use { ... } as delimiters too, so trying to escape those
 * in a smarty template would be insane.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @since 2.2.0
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

namespace Core\Templates\Backends;

use Core\Templates;

class PHTML implements Templates\TemplateInterface {
	/**
	 * An array of scoped variables for this template.
	 * @var array
	 */
	private $_scope = array();

	/**
	 * The filename of this template
	 * @var String
	 */
	protected $_filename;

	/**
	 * Assign a variable to this template
	 *
	 * @param array|string $key The variable name or array to set
	 * @param mixed|null   $var The value of the single variable to set
	 */
	public function assign($key, $var = null){
		if(is_array($key)){
			foreach($key as $k => $v){
				$this->_scope[$k] = $v;
			}
		}
		else{
			$this->_scope[$key] = $var;
		}
	}

	/**
	 * Returns a single or all template variables
	 *
	 * @param string|null  $varname        variable name or null
	 * @return string|array|null variable value or or array of variables
	 */
	public function getTemplateVars($varname = null){
		if($varname === null){
			return $this->_scope;
		}
		elseif(isset($this->_scope[$varname])){
			return $this->_scope[$varname];
		}
		else{
			return null;
		}
	}

	public function fetch($template = null){
		// Since the only way to capture php output is to use buffers...
		ob_start();
		$this->render($template);
		$contents = ob_get_clean();
		return $contents;
	}

	public function render($template = null){

		// Resolve this template.
		if($template === null){
			$file = $this->_filename;
		}
		else{
			$file = Templates\Template::ResolveFile($template);
			if($file === null){
				throw new Templates\Exception('Template ' . $template . ' could not be found!');
			}
		}

		if(!file_exists($file)){
			throw new Templates\Exception('Unable to read template ' . $file . ', file does not exist');
		}
		if(!is_readable($file)){
			throw new Templates\Exception('Unable to read template ' . $file . ', file is not readable');
		}

		// I need to make the assigned variables visible in the scope.
		foreach($this->_scope as $key => $var){
			${$key} = $var;
		}

		try{
			require($file);
		}
		catch(Exception $e){
			var_dump($e->getMessage()); die();
		}

	}

	/**
	 * Get a single variable from the template variables.
	 *
	 * @param string $varname The name of the variable
	 *
	 * @return mixed
	 */
	public function getVariable($varname) {
		return $this->getTemplateVars($varname);
	}

	/**
	 * Set a template filename to be remembered if fetch or render are called with null parameters.
	 *
	 * @param string $template Filename to remember for this template.
	 *
	 * @return void
	 */
	public function setFilename($template) {
		// Make sure it's resolved first.
		$this->_filename = Templates\Template::ResolveFile($template);
	}

	/**
	 * Get the basename of this template.
	 *
	 * @return string
	 */
	public function getBasename(){
		return basename($this->_filename);
	}

	/**
	 * Scan through this template file and see if it has optional stylesheets that the admin can select to enable.
	 *
	 * @return boolean
	 */
	public function hasOptionalStylesheets() {
		return false;
	}

	/**
	 * Scan through this template file and see if it has widgetareas contained within.
	 *
	 * @return boolean
	 */
	public function hasWidgetAreas() {
		return false;
	}

	/**
	 * Get an array of widget areas defined on this template.
	 *
	 * The returning array is associative with the widgetarea name as the key,
	 * and each value is an array of name and installable.
	 *
	 * @return array
	 */
	public function getWidgetAreas(){
		// PHTML templates do not support widget areas yet.
		return [];
	}

	/**
	 * Get the list of optional stylesheets in this template.
	 *
	 * The returned array will be an array of the attributes on the declaration, with at minimum 'src' and 'title'.
	 *
	 * @return array
	 */
	public function getOptionalStylesheets() {
		// TODO: Implement getOptionalStylesheets() method.
	}

	/**
	 * Get an array of the insertables in this template.
	 *
	 * Should have "name", "type", "title", "value", and "description" in each array.
	 * Should also have any formelement-specific key necessary for operation, ie: "basedir", "accept", etc.
	 *
	 * @return array
	 */
	public function getInsertables() {
		// TODO: Implement getInsertables() method.
	}
}
