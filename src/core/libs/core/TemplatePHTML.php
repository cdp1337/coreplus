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
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
 * @since 2.2.0
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
class TemplatePHTML implements TemplateInterface {
	private $_scope = array();

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

	public function fetch($template){
		// Since the only way to capture php output is to use buffers...
		ob_start();
		$this->render($template);
		$contents = ob_get_clean();
		return $contents;
	}

	public function render($template){
		// Templates don't need a beginning '/'.  They'll be resolved automatically
		// UNLESS they're already resolved fully.....
		if (strpos($template, ROOT_PDIR) !== 0 && $template{0} == '/') $template = substr($template, 1);

		if(!file_exists($template)){
			throw new TemplateException('Unable to read template ' . $template . ', file does not exist');
		}
		if(!is_readable($template)){
			throw new TemplateException('Unable to read template ' . $template . ', file is not readable');
		}

		// I need to make the assigned variables visible in the scope.
		foreach($this->_scope as $key => $var){
			${$key} = $var;
		}

		try{
			require($template);
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
}
