<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
 * @author Charlie Powell <charlie@eval.bz>
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

namespace Core\Templates\Backends;

use Core\Templates;

class Smarty implements Templates\TemplateInterface {

	private $_baseurl;

	protected $_filename;

	/**
	 * Smarty internal object that handles the rendering.
	 * @var Smarty
	 */
	private $_smarty;

	public function  __construct() {

		// Smarty can resolve the template automatically too, providing I send in the directories.
		$this->getSmarty()->addTemplateDir(Templates\Template::GetPaths());

		// Tack on the search directories from the loaded components.
		// Also handle the plugins directory search.
		foreach (\Core::GetComponents() as $c) {
			$plugindir = $c->getSmartyPluginDirectory();
			if ($plugindir) $this->getSmarty()->addPluginsDir($plugindir);
		}
	}

	public function setBaseURL($url) {
		$this->_baseurl = $url;
	}

	public function getBaseURL() {
		return $this->_baseurl;
	}

	/**
	 * Fetch the HTML from this template
	 *
	 * @param string $template the filename to render this template with
	 * @return string          rendered template output
	 *
	 * @throws Templates\Exception
	 */
	public function fetch($template = null) {
		// Defaults for smarty.
		$cache_id = null;
		$compile_id = null;
		$parent = null;
		$display = false;
		$merge_tpl_vars = true;
		$no_output_filter = false;

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

		// Templates don't need a beginning '/'.  They'll be resolved automatically
		// UNLESS they're already resolved fully.....
		//if (strpos($template, ROOT_PDIR) !== 0 && $template{0} == '/') $template = substr($template, 1);

		try{
			return $this->getSmarty()->fetch($file, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
		}
		catch(\SmartyException $e){
			throw new Templates\Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * Display the HTML from this template
	 *
	 * @param string $template
	 * @return string|void
	 *
	 * @throws Templates\Exception
	 */
	public function render($template = null){
		// Defaults for smarty.
		$cache_id = null;
		$compile_id = null;
		$parent = null;
		$display = true;
		$merge_tpl_vars = true;
		$no_output_filter = false;

		// Resolve this template.
		if($template === null){
			$template = $this->_filename;
		}
		else{
			$template = Templates\Template::ResolveFile($template);
		}

		try{
			return $this->getSmarty()->fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
		}
		catch(SmartyException $e){
			throw new Templates\Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}


	public function getSmarty(){
		if($this->_smarty === null){
			$this->_smarty = new \Smarty();
			$this->_smarty->compile_dir = TMP_DIR . 'smarty_templates_c';
			$this->_smarty->cache_dir   = TMP_DIR . 'smarty_cache';
		}
		return $this->_smarty;
	}

	/**
	 * Returns a single or all template variables
	 *
	 * @param string|null  $varname        variable name or null
	 *
	 * @return string|array|null variable value or or array of variables
	 */
	public function getTemplateVars($varname = null) {
		return $this->getSmarty()->getTemplateVars($varname);
	}

	/**
	 * Assign a variable into the template
	 *
	 * This is required because templates are sandboxed from the rest of the application.
	 *
	 * @param array|string $tpl_var the template variable name(s)
	 * @param mixed        $value   the value to assign
	 */
	public function assign($tpl_var, $value = null) {
		$this->getSmarty()->assign($tpl_var, $value);
	}

	public function getTemplateDir(){
		return $this->getSmarty()->getTemplateDir();
	}

	/**
	 * Get a single variable from the template variables.
	 *
	 * @param string $varname The name of the variable
	 *
	 * @return mixed
	 */
	public function getVariable($varname) {
		return $this->getSmarty()->getVariable($varname);
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
	 * Scan through this template file and see if it has optional stylesheets that the admin can select to enable.
	 *
	 * @return boolean
	 */
	public function hasOptionalStylesheets() {
		$contents = file_get_contents($this->_filename);
		// Smarty uses {css optional="1" ... declarations.
		return (preg_match('/{css[^}]*optional=["\']1["\'].*}/', $contents) == 1);
	}

	/**
	 * Get the list of optional stylesheets in this template.
	 *
	 * The returned array will be an array of the attributes on the declaration, with at minimum 'src' and 'title'.
	 *
	 * @return array
	 */
	public function getOptionalStylesheets(){
		$contents = file_get_contents($this->_filename);

		preg_match_all('/{(css[^}]*optional=["\']1["\'][^}]*)}/', $contents, $matches);

		$results = array();
		foreach($matches[1] as $match){
			// Convert this to a DOM elemnt so I can parse the attributes therein.
			$simple = new \SimpleXMLElement('<' . $match . '/>');
			$attributes = array();
			foreach($simple->attributes() as $k => $v){
				$attributes[$k] = (string)$v;
			}

			// css files can be src, link, or href...
			if(!isset($attributes['src']) && isset($attributes['link'])) $attributes['src'] = $attributes['link'];
			if(!isset($attributes['src']) && isset($attributes['href'])) $attributes['src'] = $attributes['href'];

			if(!isset($attributes['title'])) $attributes['title'] = basename($attributes['src']);

			$results[] = $attributes;
		}

		return $results;
	}

	/**
	 * Scan through this template file and see if it has widgetareas contained within.
	 *
	 * @return boolean
	 */
	public function hasWidgetAreas() {
		$contents = file_get_contents($this->_filename);

		if(strpos($contents, '{widgetarea') !== false){
			return true;
		}
		else{
			return false;
		}
	}
}
