<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
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
			/** @var \Component_2_1 $c */
			$plugindir = $c->getSmartyPluginDirectory();
			if ($plugindir) $this->getSmarty()->addPluginsDir($plugindir);

			foreach($c->getSmartyPlugins() as $name => $call){
				if(strpos($call, '::') !== false){
					$parts = explode('::', $call);
					$this->getSmarty()->registerPlugin('function', $name, $parts);
				}
				else{
					$this->getSmarty()->registerPlugin('function', $name, $call);
				}
			}
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
		catch(\SmartyException $e){
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

	/**
	 * Get an array of the insertables in this template.
	 *
	 * Should have "name", "type", "title", "value", and "description" in each array.
	 * Should also have any formelement-specific key necessary for operation, ie: "basedir", "accept", etc.
	 *
	 * @return array
	 */
	public function getInsertables() {
		// Will get populated with all the insertables in this template.
		$insertables = [];

		// Scan through $tpl and find any {insertable} tag.
		$contents = file_get_contents($this->_filename);


		// I need to make sure that I'm accommodating for nested insertables!
		// Easiest way.... convert this to XML/HTML!
		$fullsearch = $contents;
		$fullsearch = preg_replace('#\{insertable(.*)\}#isU', '<insertable$1>', $fullsearch);
		$fullsearch = preg_replace('#\{\/insertable[ ]*\}#', '</insertable>', $fullsearch);

		//echo '<pre>' . str_replace('<', '&lt;', $fullsearch) . '</pre>';

		$dom = new \DOMDocument();
		// I don't care about parsing errors here... I just want the damn nodes!
		// @TODO Find a better way to ignore errors!
		try{
			@$dom->loadHTML('<html>' . $fullsearch . '</html>');
			$nodes = $dom->getElementsByTagName('insertable');
			$validattributes = ['accept', 'basedir', 'cols', 'default', 'description', 'name', 'options', 'rows', 'size', 'type', 'title', 'value', 'width'];

			foreach($nodes as $n){
				/** @var $n \DOMElement */
				// Pull out all the valid attributes for each node.
				$nodedata = [];
				foreach($validattributes as $k){
					$nodedata[$k] = $n->getAttribute($k);
				}

				// Because saveXML will include the node itself,
				// I need to render the first child only, (which includes any child node).
				$inner = $dom->saveXML($n->firstChild);

				if(!$nodedata['type']){
					// Try to determine the form type based on the content since this is optional.
					if (preg_match('/<img(.*?)>/i', $inner)) {
						// It's an image!
						$nodedata['type'] = 'image';
					}
					elseif (preg_match('/{img(.*?)}/i', $inner)) {
						// Also an image, but using the smarty version instead.... I don't care.
						$nodedata['type'] = 'image';
					}
					elseif (strpos($inner, "\n") === false && strpos($inner, "<") === false) {
						// If there are no newlines or &lt;'s, it's just simple text.
						$nodedata['type'] = 'text';
					}
					else {
						$nodedata['type'] = 'wysiwyg';
					}
				}

				if(!$nodedata['title']){
					// Use the name as a default title if none provided.
					$nodedata['title'] = $nodedata['name'];
				}

				if(!$nodedata['default'] && $nodedata['value']){
					// Default but no value...
					$nodedata['default'] = $nodedata['value'];
				}
				elseif(!$nodedata['default'] && $inner){
					// No default value nor value, but it has inner contents.
					// This will suffice as the default value.
					$nodedata['default'] = $inner;
				}

				if(!$nodedata['value'] && $nodedata['default']){
					// Value but no default...
					$nodedata['value'] = $nodedata['default'];
				}

				// Some elements have specific options that need to be set.
				switch($nodedata['type']){
					case 'image':
						$nodedata['type'] = 'file';
						if(!$nodedata['accept'])  $nodedata['accept']  = 'image/*';
						if(!$nodedata['basedir']) $nodedata['basedir'] = 'public/insertable';
						break;
					case 'file':
						if(!$nodedata['basedir']) $nodedata['basedir'] = 'public/insertable';
						break;
					case 'select':
						$nodedata['options'] = array_map('trim', explode('|', $nodedata['options']));
						break;
				}

				// Add the actual elements now!
				$insertables[ $nodedata['name'] ] = $nodedata;

			}
		}
		catch(\Exception $e){
			// I honestly don't care if it failed.
		}

		return $insertables;
	}
}
