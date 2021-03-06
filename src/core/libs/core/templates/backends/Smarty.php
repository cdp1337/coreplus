<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

	/** @var \View|null View that is responsible for this template, optional */
	private $_view = null;

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
			// $template = null, $cache_id = null, $compile_id = null, $parent = null
			return $this->getSmarty()->fetch($file, $cache_id, $compile_id, $parent);
		}
		catch(\SmartyException $e){
			throw $e;
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

		// Resolve this template.
		if($template === null){
			$template = $this->_filename;
		}
		else{
			$template = Templates\Template::ResolveFile($template);
		}

		try{
			return $this->getSmarty()->display($template, $cache_id, $compile_id, $parent);
		}
		catch(\SmartyException $e){
			throw new Templates\Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}


	public function getSmarty(){
		if($this->_smarty === null){
			$this->_smarty = new \Smarty();

			/**
			 * @var $caching int
			 *
			 * Leave this off!  Smarty doesn't seem to cache the output of our {css} blocks when this is enabled as of 3.1.27
			 */
			$this->_smarty->caching = \Smarty::CACHING_OFF;
			//$this->_smarty->caching = DEVELOPMENT_MODE ? \Smarty::CACHING_OFF : \Smarty::CACHING_LIFETIME_SAVED;

			$this->_smarty->setCompileDir(TMP_DIR . 'smarty_templates_c');
			$this->_smarty->setCacheDir(TMP_DIR . 'smarty_cache');

			/**
			 * @var $force_compile boolean
			 *
			 * This forces Smarty to (re)compile templates on every invocation.
			 * This setting overrides $compile_check. By default this is FALSE.
			 * This is handy for development and debugging.
			 * It should never be used in a production environment.
			 * If $caching is enabled, the cache file(s) will be regenerated every time.
			 */
			$this->_smarty->force_compile = DEVELOPMENT_MODE ? true : false;

			/**
			 * @var $compile_check boolean
			 *
			 * Upon each invocation of the PHP application,
			 * Smarty tests to see if the current template has changed (different timestamp) since the last time it was compiled.
			 * If it has changed, it recompiles that template.
			 * If the template has yet not been compiled at all, it will compile regardless of this setting.
			 * By default this variable is set to TRUE.
			 */
			$this->_smarty->compile_check = DEVELOPMENT_MODE ? true : false;

			$this->_smarty->assign('__core_template', $this);
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
	 * Get the basename of this template.
	 *
	 * @return string
	 */
	public function getBasename(){
		return basename($this->_filename);
	}

	/**
	 * Get the full filename of this template
	 *
	 * @return string|null
	 */
	public function getFilename(){
		return $this->_filename;
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
	 * Get an array of widget areas defined on this template.
	 *
	 * The returning array is associative with the widgetarea name as the key,
	 * and each value is an array of name and installable.
	 *
	 * @return array
	 */
	public function getWidgetAreas(){
		// First, check if this file exists and is readable!
		if(!is_readable($this->_filename)){
			return [];
		}
		// Easiest way.... convert this to XML/HTML!
		$fullsearch = file_get_contents($this->_filename);
		$fullsearch = preg_replace('#\{widgetarea(.*)\}#isU', '<widgetarea$1/>', $fullsearch);

		//echo '<pre>' . str_replace('<', '&lt;', $fullsearch) . '</pre>';

		$areas = [];
		$dom = new \DOMDocument();

		// I don't care about parsing errors here... I just want the damn nodes!
		libxml_use_internal_errors(true);

		try{
			$dom->loadHTML('<html>' . $fullsearch . '</html>');
			$nodes = $dom->getElementsByTagName('widgetarea');
			$validattributes = ['name', 'installable'];

			foreach($nodes as $n){
				/** @var $n \DOMElement */
				// Pull out all the valid attributes for each node.
				$nodedata = [];
				foreach($validattributes as $k){
					$nodedata[$k] = $n->getAttribute($k);
				}

				if(!isset($nodedata['installable'])){
					$nodedata['installable'] = '';
				}

				// Add the actual elements now!
				$areas[ $nodedata['name'] ] = $nodedata;

			}
		}
		catch(\Exception $e){
			// I honestly don't care if it failed.
		}

		return $areas;
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
		libxml_use_internal_errors(true);

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
	
	/**
	 * Get an array of any meta field present in this template.
	 * 
	 * These are useful for the template defining some different behaviour
	 * or additional piece of information required by the controllers.
	 * 
	 * @return array
	 */
	public function getMetas() {
		// Will get populated with all the metas in this template.
		$metas = [];

		// Scan through $tpl and find any {*#META ... #*} section.
		$contents = file_get_contents($this->_filename);

		if(strpos($contents, '{*#META') !== false){
			$segment = trim(preg_replace('/{\*#META(.*)#\*}.*/s', '$1', $contents));
			$lines = array_map('trim', explode("\n", $segment));
			foreach($lines as $l){
				if($l == ''){
					// Skip blank lines
					continue;
				}
				$pos = strpos($l, ':'); 
				if($pos === false){
					// Skip lines that do not contain a colon
					continue;
				}
				$key = substr($l, 0, $pos);
				$val = trim(substr($l, $pos+1));
				
				if(isset($metas[$key]) && is_array($metas[$key])){
					$metas[$key][] = $val;
				}
				elseif(isset($metas[$key])){
					$metas[$key] = [ $metas[$key] ];
					$metas[$key][] = $val;
				}
				else{
					$metas[$key] = $val;
				}
			}
		}
		
		return $metas;
	}

	/**
	 * Get the registered view for this template, useful for setting CSS and Scripts in correct locations in the markup.
	 *
	 * If no view has been set on this template, then \Core\view() should be returned.
	 *
	 * @return \View
	 */
	public function getView() {
		return $this->_view === null ? \Core\view() : $this->_view;
	}

	/**
	 * Set the registered view for this template, usually set from the View.
	 *
	 * @param \View $view
	 *
	 * @return void
	 */
	public function setView(\View $view) {
		$this->_view = $view;
	}
	
	public static function FlushCache(){
		
		$dir = \Core\Filestore\Factory::Directory(TMP_DIR . 'smarty_templates_c');
		foreach($dir->ls('php') as $file){
			/** @var \Core\Filestore\File $file */
			$file->delete();
		}

		$dir = \Core\Filestore\Factory::Directory(TMP_DIR . 'smarty_cache');
		foreach($dir->ls('php') as $file){
			/** @var \Core\Filestore\File $file */
			$file->delete();
		}
	}
}
