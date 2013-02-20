<?php
/**
 * Core component system, responsible for reading and parsing the component.xml,
 * saving it, and installing all components on the system.
 *
 * @package Core Plus\Core
 * @since 1.9
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

// @todo Implement arrayaccess system.
class Component_2_1 {

	/**
	 * Underlying XML Loader object of the component.xml file.
	 *
	 * Responsible for retrieving most information about this component.
	 *
	 * @var XMLLoader
	 */
	private $_xmlloader = null;

	/**
	 * The name of the component.
	 * Has to be unique, (because the name is a directory in /components)
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Version of the component, (propagates to libraries and modules).
	 *
	 * @var string
	 */
	protected $_version;

	/**
	 * Is this component explictly disabled?
	 *
	 * @var boolean
	 */
	protected $_enabled = false;

	/**
	 * Description of this library.
	 * As set from the XML file.
	 *
	 * @var string
	 */
	protected $_description;

	/**
	 * Any update sites provided in this library.
	 *
	 * @var array <<string>>
	 */
	protected $_updateSites = array();

	/**
	 * Array of any authors for the library.
	 * Each element is composed of an array of name, email and url.
	 *
	 * @var array <<array>>
	 */
	protected $_authors = array();

	/**
	 * The iterator for this object, kept as a cache.
	 * @var CAEDirectoryIterator
	 */
	protected $_iterator;

	/**
	 * Version of the component, as per the database (installed version).
	 *
	 * @var string
	 */
	private $_versionDB = false;

	/**
	 * Each component can have an execution mode, by default it's "web".
	 * This is used because some components will bomb out in CLI mode, and vice versa.
	 * @var string
	 */
	private $_execMode = 'WEB';

	/**
	 * @var File_Backend
	 */
	private $_file;

	/**
	 * The permissions along with their description that are registered for this component.
	 *
	 * @var array
	 */
	private $_permissions = array();

	// A set of error codes components may encounter.
	const ERROR_NOERROR = 0;           // 00000
	const ERROR_INVALID = 1;           // 00001
	const ERROR_WRONGEXECMODE = 2;     // 00010
	const ERROR_MISSINGDEPENDENCY = 4; // 00100
	const ERROR_CONFLICT = 8;          // 01000

	/**
	 * This is the error code of any errors encountered.
	 * @var int
	 */
	public $error = 0;

	/**
	 * Any error messages encountered in this component, mainly while loading.
	 * @var array <<string>>
	 */
	public $errstrs = array();

	/**
	 * Only try to load a component only once!
	 *
	 * @var bool
	 */
	private $_loaded = false;

	/**
	 * Only try to load the files for this component once!
	 *
	 * @var bool
	 */
	private $_filesloaded = false;

	/**
	 * The smarty plugin directory cache.  This is to reduce the number of lookups required.
	 *
	 * @var null|false|string
	 */
	private $_smartyPluginDirectory = null;

	/**
	 * View search directory cache.  This is to reduce the number of lookups required.
	 *
	 * @var null|false|string
	 */
	private $_viewSearchDirectory = null;

	/**
	 * Array of classes in this component.  This is to reduce the number of lookups required.
	 *
	 * @var null|array
	 */
	private $_classlist = null;

	/**
	 * Array of widgets in this component.  This is to reduce the number of lookups required.
	 * @var null|array
	 */
	private $_widgetlist = null;

	/**
	 * Array of require defintions in this component.  This is to reduce the number of lookups required.
	 * @var null|array
	 */
	private $_requires = null;


	public function __construct($filename = null) {
		$this->_file = \Core\file($filename);

		$this->_xmlloader = new XMLLoader();
		$this->_xmlloader->setRootName('component');

		if (!$this->_xmlloader->loadFromFile($filename)) {
			throw new Exception('Parsing of XML Metafile [' . $filename . '] failed, not valid XML.');
		}
	}

	/**
	 * Load this component's metadata from the XML file.
	 *
	 * Will setup the name, version, installed version (if available), and enabled flag (if available).
	 *
	 * @return void
	 */
	public function load() {
		if ($this->_loaded) return;

		if (($mode = $this->_xmlloader->getRootDOM()->getAttribute('execmode'))) {
			$this->_execMode = strtoupper($mode);
		}

		$this->_name    = $this->_xmlloader->getRootDOM()->getAttribute('name');
		$this->_version = $this->_xmlloader->getRootDOM()->getAttribute("version");

		Debug::Write('Loading metadata for component [' . $this->_name . ']');

		// Load the database information, if there is any.
		$dat = ComponentFactory::_LookupComponentData($this->_name);
		if (!$dat) return;

		$this->_versionDB = $dat['version'];
		$this->_enabled   = ($dat['enabled']) ? true : false;
		$this->_loaded    = true;

		// Set the permissions
		$this->_permissions = array();
		foreach($this->_xmlloader->getElements('/permissions/permission') as $el){
			$this->_permissions[$el->getAttribute('key')] = $el->getAttribute('description');
		}
	}


	/**
	 * Save this component metadata back to its XML file.
	 * Useful in packager scripts.
	 */
	public function save($minified = false) {
		// Set the schema version to the newest API version.
		$this->_xmlloader->setSchema('http://corepl.us/api/2_4/component.dtd');
		// Ensure there's a required namespace on the root node.
		$this->_xmlloader->getRootDOM()->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");

		/*
		///////////////  Handle the hard-set pages, ie: admin ones \\\\\\\\\\\\\
		if(!isset($viewclasses)) $viewclasses = array();
		foreach($viewclasses as $c){
			// Should end in Controller.
			if(strlen($c) - strpos($c, 'Controller') == 10) $c = substr($c, 0, -10);
			$data = Dataset::Init()->table('page')->select('*')->where("baseurl = /$c", 'admin=1', 'fuzzy=0')->execute();
			
			//$rs = DB::Execute("SELECT * FROM " . DB_PREFIX . "page WHERE ( `baseurl` = '/$c' OR `baseurl` LIKE '/$c/%' ) AND `fuzzy` = '0' AND `admin` = '1'");
			foreach($data as $row){
				$node = $this->_xmlloader->getElement('/pages/page[@baseurl="' . $row['baseurl'] . '"]');
				$node->setAttribute('admin', $row['admin']);
				$node->setAttribute('widget', $row['widget']);
				$node->setAttribute('access', $row['access']);
				$node->setAttribute('title', $row['title']);
			}
			
			$data = Dataset::Init()->table('page')->select('*')->where("baseurl LIKE /$c/%", 'admin=1', 'fuzzy=0')->execute();
			
			//$rs = DB::Execute("SELECT * FROM " . DB_PREFIX . "page WHERE ( `baseurl` = '/$c' OR `baseurl` LIKE '/$c/%' ) AND `fuzzy` = '0' AND `admin` = '1'");
			foreach($data as $row){
				$node = $this->_xmlloader->getElement('/pages/page[@baseurl="' . $row['baseurl'] . '"]');
				$node->setAttribute('admin', $row['admin']);
				$node->setAttribute('widget', $row['widget']);
				$node->setAttribute('access', $row['access']);
				$node->setAttribute('title', $row['title']);
			}
		}
		*/

		/*
		///////////////////////  Handle the config options \\\\\\\\\\\\\\\\\\\\\
		$data = Dataset::Init()->table('config')->select('*')->where('key LIKE /' . $this->getName() . '/%')->execute();
		//$rs = DB::Execute("SELECT * FROM " . DB_PREFIX . "config WHERE `key` LIKE '/" . $this->getName() . "/%'");
		foreach($data as $row){
			$node = $this->_xmlloader->getElement('/configs/config[@key="' . $row['key'] . '"]');
			$node->setAttribute('type', $row['type']);
			$node->setAttribute('default', $row['default_value']);
			$node->setAttribute('description', $row['description']);
			
			if($row['options']) $node->setAttribute('options', $row['options']);
			else $node->removeAttribute('options');
		}
		*/

		// This needs to be the final step... write the XML doc back to the file.
		$XMLFilename = $this->_file->getFilename();
		//echo $this->asPrettyXML(); // DEBUG //
		if ($minified) {
			file_put_contents($XMLFilename, $this->_xmlloader->asMinifiedXML());
		}
		else {
			file_put_contents($XMLFilename, $this->_xmlloader->asPrettyXML());
		}
	}

	/**
	 * Save or get the package XML for this component.  This is useful for the
	 * packager
	 *
	 * @param boolean     $minified
	 * @param bool|string $filename
	 *
	 * @return string|null
	 */
	public function savePackageXML($minified = true, $filename = false) {

		$packagexml = new PackageXML();
		$packagexml->setFromComponent($this);

		$out = ($minified) ? $packagexml->asMinifiedXML() : $packagexml->asPrettyXML();

		if ($filename) {
			file_put_contents($filename, $out);
		}
		else {
			return $out;
		}
	}

	public function getRequires() {
		if($this->_requires === null){
			$this->_requires = array();
			foreach ($this->_xmlloader->getElements('//component/requires/require') as $r) {
				$t  = $r->getAttribute('type');
				$n  = $r->getAttribute('name');
				$v  = @$r->getAttribute('version');
				$op = @$r->getAttribute('operation');

				// Defaults.
				if ($v == '') $v = false;
				if ($op == '') $op = 'ge';

				$this->_requires[] = array(
					'type'      => strtolower($t),
					'name'      => $n,
					'version'   => strtolower($v),
					'operation' => strtolower($op),
					//'value' => $value,
				);
			}
		}

		return $this->_requires;
	}

	/**
	 * Get the description for this component
	 * @return string
	 */
	public function getDescription() {
		if ($this->_description === null) {
			$this->_description = trim($this->_xmlloader->getElement('//description')->nodeValue);
		}

		return $this->_description;
	}

	/**
	 * Set the description for this component
	 * @param $desc string
	 */
	public function setDescription($desc) {
		// Set the cache first.
		$this->_description = $desc;
		// And set the data in the original DOM.
		$this->_xmlloader->getElement('//description')->nodeValue = $desc;
	}

	/**
	 * Get the registered permissions for this component.
	 *
	 * @return array
	 */
	public function getPermissions(){
		return $this->_permissions;
	}

	/**
	 * Set and override the list of authors for this component.
	 *
	 * @param $authors array Array of authors to set
	 */
	public function setAuthors($authors) {
		// First, remove any authors currently in the XML.
		$this->_xmlloader->removeElements('/authors');

		// Now I can add the ones in the authors array.
		foreach ($authors as $a) {
			if (isset($a['email']) && $a['email']) {
				$this->_xmlloader->getElement('//component/authors/author[@name="' . $a['name'] . '"][@email="' . $a['email'] . '"]');
			}
			else {
				$this->_xmlloader->getElement('//component/authors/author[@name="' . $a['name'] . '"]');
			}
		}
	}

	/**
	 * Set and override the list of licenses for this component.
	 *
	 * @param $licenses array Array of licenses to set
	 */
	public function setLicenses($licenses) {
		// First, remove any licenses currently in the XML.
		$this->_xmlloader->removeElements('//component/licenses');

		// Now I can add the ones in the licenses array.
		$path = '//component/licenses/';
		foreach ($licenses as $lic) {
			$el = 'license' . ((isset($lic['url']) && $lic['url']) ? '[@url="' . $lic['url'] . '"]' : '');
			$l  = $this->_xmlloader->createElement($path . $el, false, 1);
			if ($lic['title']) $l->nodeValue = $lic['title'];
		}
	}


	public function loadFiles() {

		// First of all, this cannot be called on disabled or uninstalled components.
		if(!$this->isInstalled()) return false;
		if(!$this->isEnabled()) return false;
		if($this->_filesloaded) return true;

		Debug::Write('Loading files for component [' . $this->getName() . ']');

		$dir = $this->getBaseDir();

		// Include any includes requested.
		// This adds support for namespaced functions.
		// <includes>
		//     <include filename="core/functions/Core.functions.php"/>
		// </includes>
		foreach ($this->_xmlloader->getElements('/includes/include') as $f) {
			require_once($dir . $f->getAttribute('filename'));
		}


		// Register any hooks that may be present.
		foreach ($this->_xmlloader->getElementsByTagName('hookregister') as $h) {
			$hook              = new Hook($h->getAttribute('name'));
			$hook->description = $h->getAttribute('description');
			if($h->getAttribute('return')){
				$hook->returnType = $h->getAttribute('return');
			}
		}

		// Register any events that may be present.
		foreach ($this->_xmlloader->getElementsByTagName('hook') as $h) {
			$event = $h->getAttribute('name');
			$call  = $h->getAttribute('call');
			$type  = @$h->getAttribute('type');
			HookHandler::AttachToHook($event, $call, $type);
		}


		// This component may have special form elements registered.  Check!
		foreach ($this->_xmlloader->getElements('/forms/formelement') as $node) {
			Form::$Mappings[$node->getAttribute('name')] = $node->getAttribute('class');
		}

		$this->_filesloaded = true;

		return true;
	}

	public function getLibraryList() {
		// Get an array of library -> version
		$libs = array();

		// Every component is a library
		$libs[strtolower($this->_name)] = $this->_versionDB;

		foreach ($this->_xmlloader->getElements('provides/provide') as $p) {
			if (strtolower($p->getAttribute('type')) == 'library') {
				$v = @$p->getAttribute('version');
				if (!$v) $v = $this->_versionDB;
				$libs[strtolower($p->getAttribute('name'))] = $v;
			}
		}

		return $libs;
	}

	/**
	 * Get the list of classes provided in this component, (and their filenames)
	 *
	 * @return array
	 */
	public function getClassList() {
		$dir = $this->getBaseDir();

		if($this->_classlist === null){
			// Get an array of class -> file (fully resolved)
			$this->_classlist = array();

			foreach ($this->_xmlloader->getElements('/files/file') as $f) {
				$filename = $dir . $f->getAttribute('filename');
				//foreach($f->getElementsByTagName('provides') as $p){
				foreach ($f->getElementsByTagName('class') as $p) {
					$n           = strtolower($p->getAttribute('name'));
					$this->_classlist[$n] = $filename;
				}

				foreach ($f->getElementsByTagName('interface') as $p) {
					$n           = strtolower($p->getAttribute('name'));
					$this->_classlist[$n] = $filename;
				}

				foreach ($f->getElementsByTagName('controller') as $p) {
					$n           = strtolower($p->getAttribute('name'));
					$this->_classlist[$n] = $filename;
				}

				foreach ($f->getElementsByTagName('widget') as $p) {
					$n           = strtolower($p->getAttribute('name'));
					$this->_classlist[$n] = $filename;
				}
			}
		}

		return $this->_classlist;
	}

	/**
	 * Get an array of widget names provided in this component.
	 *
	 * @return array
	 */
	public function getWidgetList() {
		$dir = $this->getBaseDir();

		if($this->_widgetlist === null){
			$this->_widgetlist = array();

			foreach ($this->_xmlloader->getElements('/files/file') as $f) {
				$filename = $dir . $f->getAttribute('filename');
				foreach ($f->getElementsByTagName('widget') as $p) {
					$this->_widgetlist[] = $p->getAttribute('name');
				}
			}
		}

		return $this->_widgetlist;
	}

	public function getViewClassList() {
		$classes = array();
		if ($this->hasModule()) {
			foreach ($this->_xmlloader->getElementByTagName('module')->getElementsByTagName('file') as $f) {
				$filename = $this->getBaseDir() . $f->getAttribute('filename');
				foreach ($f->getElementsByTagName('provides') as $p) {
					switch (strtolower($p->getAttribute('type'))) {
						case 'viewclass':
						case 'view_class':
							$classes[$p->getAttribute('name')] = $filename;
							break;
					}
				}
			}
		}
		return $classes;
	}

	/**
	 * Get a list of view templates provided by this component.
	 */
	public function getViewList() {
		$views = array();
		$dir = $this->getBaseDir();

		if ($this->hasView()) {
			foreach ($this->_xmlloader->getElementByTagName('view')->getElementsByTagName('tpl') as $t) {
				$filename     = $dir . $t->getAttribute('filename');
				$name         = $t->getAttribute('name');
				$views[$name] = $filename;
			}
		}
		return $views;
	}

	/**
	 * Get the list of controllers in this component.
	 *
	 * @return array
	 */
	public function getControllerList() {
		// Get an array of class -> file (fully resolved)
		$classes = array();
		$dir = $this->getBaseDir();

		//foreach($this->_xmlloader->getElementByTagName('files')->getElementsByTagName('file') as $f){
		foreach ($this->_xmlloader->getElements('/files/file') as $f) {
			$filename = $dir . $f->getAttribute('filename');
			//foreach($f->getElementsByTagName('provides') as $p){

			foreach ($f->getElementsByTagName('controller') as $p) {
				$n           = strtolower($p->getAttribute('name'));
				$classes[$n] = $filename;
			}
		}

		return $classes;
	}

	/**
	 * Return the fully resolved name of the smarty plugin directory for
	 * this component (if there is one).
	 *
	 * Not many templates will use this function, but it is there for when needed.
	 */
	public function getSmartyPluginDirectory() {
		if($this->_smartyPluginDirectory === null){
			$d = $this->_xmlloader->getElement('/smartyplugins')->getAttribute('directory');
			if ($d) $this->_smartyPluginDirectory = $this->getBaseDir() . $d;
			else $this->_smartyPluginDirectory = false;
		}

		return $this->_smartyPluginDirectory;
	}

	public function getScriptLibraryList() {
		$libs = array();
		foreach ($this->_xmlloader->getElements('/provides/scriptlibrary') as $s) {
			$libs[strtolower($s->getAttribute('name'))] = $s->getAttribute('call');
		}
		return $libs;
	}


	public function getViewSearchDir() {
		if ($this->hasView()) {
			if($this->_viewSearchDirectory === null){
				// Using the searchdir attribute is the preferred method.
				$att = @$this->_xmlloader->getElement('/view')->getAttribute('searchdir');
				if ($att) {
					$this->_viewSearchDirectory = $this->getBaseDir() . $att . '/';
				}
				elseif (($att = $this->_xmlloader->getElements('/view/searchdir')->item(0))) {
					// Try the 'searchdir' element instead.
					$this->_viewSearchDirectory = $this->getBaseDir() . $att->getAttribute('dir') . '/';
				}
				elseif (is_dir($this->getBaseDir() . 'templates')) {
					// Still no?!?  Try just a filesystem check instead...
					$this->_viewSearchDirectory = $this->getBaseDir() . 'templates';
				}
				else{
					$this->_viewSearchDirectory = false;
				}
			}

			return $this->_viewSearchDirectory;
		}
	}

	public function getAssetDir() {
		// Core has a special exception...
		if ($this->getName() == 'core') $d = $this->getBaseDir() . 'core/assets';
		else $d = $this->getBaseDir() . 'assets';

		if (is_dir($d)) return $d;
		else return null;
	}

	/**
	 * @deprecated 2012.01
	 * @return array
	 */
	public function getIncludePaths() {
		return array();
	}


	/**
	 * Get an array of the table names in the DB schema.
	 * @return array
	 */
	public function getDBSchemaTableNames() {
		$ret = array();
		foreach ($this->_xmlloader->getElement('dbschema')->getElementsByTagName('table') as $table) {
			$ret[] = $table->getAttribute('name');
		}
		return $ret;
	}

	/**
	 * Set the DB Schema table names.
	 * Will override any setting of the current dbschema.
	 *
	 * @param array $arr
	 */
	public function setDBSchemaTableNames($arr) {
		// Easiest way... just drop the current set.
		$this->_xmlloader->getRootDOM()->removeChild($this->_xmlloader->getElement('/dbschema'));
		// And recreate it.
		$node = $this->_xmlloader->getElement('/dbschema[@prefix="' . DB_PREFIX . '"]');
		foreach ($arr as $k) {
			if (!trim($k)) continue;
			$tablenode = $this->getDOM()->createElement('table');
			$tablenode->setAttribute('name', $k);
			$node->appendChild($tablenode);
			unset($tablenode);
		}
	}


	public function getVersionInstalled() {
		return $this->_versionDB;
	}

	/**
	 * Components are components, (unless it's the core)
	 *
	 * @return string
	 */
	public function getType() {
		if ($this->_name == 'core') return 'core';
		else return 'component';
	}

	/**
	 * Get this component's name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Get this component's version
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->_version;
	}

	/**
	 * Set the version of this component
	 *
	 * This affects the component.xml metafile of the package.
	 *
	 * @param $vers string
	 *
	 * @return void
	 */
	public function setVersion($vers) {
		if ($vers == $this->_version) return;

		// Switch over any unversioned upgrade directives to this version.
		// First, check just a plain <upgrade> directive.
		if (($upg = $this->_xmlloader->getElement('/upgrades/upgrade[@from=""][@to=""]', false))) {
			// Add the current and dest. attribute to it.
			$upg->setAttribute('from', $this->_version);
			$upg->setAttribute('to', $vers);
		}
		elseif (($upg = $this->_xmlloader->getElement('/upgrades/upgrade[@from="' . $this->_version . '"][@to=""]', false))) {
			$upg->setAttribute('to', $vers);
		}
		else {
			// No node found... just create a new one.
			$this->_xmlloader->getElement('/upgrades/upgrade[@from="' . $this->_version . '"][@to="' . $vers . '"]');
		}

		$this->_version = $vers;
		$this->_xmlloader->getRootDOM()->setAttribute('version', $vers);
	}

	/**
	 * Set all files in this component.  Only really usable in the installer.
	 *
	 * @param $files array Array of files to set.
	 */
	public function setFiles($files) {
		// Clear out the array first.
		$this->_xmlloader->removeElements('//component/files/file');

		// It would be nice to have them alphabetical.
		$newarray = array();
		foreach ($files as $f) {
			$newarray[$f['file']] = $f;
		}
		ksort($newarray);

		// And recreate them all.
		foreach ($newarray as $f) {
			$el = $this->_xmlloader->createElement('//component/files/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');

			if (isset($f['controllers'])) {
				foreach ($f['controllers'] as $c) {
					$this->_xmlloader->createElement('controller[@name="' . $c . '"]', $el);
				}
			}
			if (isset($f['classes'])) {
				foreach ($f['classes'] as $c) {
					$this->_xmlloader->createElement('class[@name="' . $c . '"]', $el);
				}
			}
			if (isset($f['interfaces'])) {
				foreach ($f['interfaces'] as $i) {
					$this->_xmlloader->createElement('interface[@name="' . $i . '"]', $el);
				}
			}
		}
	}

	/**
	 * Set all asset files in this component.  Only really usable in the installer.
	 *
	 * @param $files array Array of files to set.
	 */
	public function setAssetFiles($files) {
		// Clear out the array first.
		$this->_xmlloader->removeElements('//component/assets/file');

		// It would be nice to have them alphabetical.
		$newarray = array();
		foreach ($files as $f) {
			$newarray[$f['file']] = $f;
		}
		ksort($newarray);

		// And recreate them all.
		foreach ($newarray as $f) {
			$el = $this->_xmlloader->createElement('//component/assets/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');
		}
	}

	/**
	 * Set all asset files in this component.  Only really usable in the installer.
	 *
	 * @param $files array Array of files to set.
	 */
	public function setViewFiles($files) {
		// Clear out the array first.
		$this->_xmlloader->removeElements('//component/view/file');

		// It would be nice to have them alphabetical.
		$newarray = array();
		foreach ($files as $f) {
			$newarray[$f['file']] = $f;
		}
		ksort($newarray);

		// And recreate them all.
		foreach ($newarray as $f) {
			$el = $this->_xmlloader->createElement('//component/view/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');
		}
	}


	/**
	 * Get the raw XML of this component, useful for debugging.
	 *
	 * @return string (XML)
	 */
	public function getRawXML() {
		return $this->_xmlloader->asPrettyXML();
	}


	public function isValid() {
		return (!$this->error & Component::ERROR_INVALID);
	}

	public function isInstalled() {
		return ($this->_versionDB === false) ? false : true;
	}

	public function needsUpdated() {
		return ($this->_versionDB != $this->_version);
	}

	public function getErrors($glue = '<br/>') {
		if ($glue) {
			return implode($glue, $this->errstrs);
		}
		else {
			return $this->errors;
		}
	}

	public function isEnabled() {
		return ($this->_enabled === true);
	}

	/**
	 * Check if this component is loadable in the environment's current state.
	 *
	 * This cannot be cached because it's called multiple times in the loader.
	 * ie: com1 needs com2, but com1 is checked first in the loop.
	 */
	public function isLoadable() {
		// Invalid ones are not loadable... don't even try ;)
		if ($this->error & Component::ERROR_INVALID) {
			return false;
		}

		// It's already loaded!
		if($this->_loaded) return true;

		// Reset the error info.
		$this->error   = 0;
		$this->errstrs = array();

		// Check the mode of it also, quick check.
		if ($this->_execMode != 'BOTH') {
			if ($this->_execMode != EXEC_MODE) {
				$this->error     = $this->error | Component::ERROR_WRONGEXECMODE;
				$this->errstrs[] = 'Wrong execution mode, can only be ran in ' . $this->_execMode . ' mode';
			}
		}

		// Can this component be loaded as-is?
		foreach ($this->getRequires() as $r) {
			switch ($r['type']) {
				case 'library':
					if (!Core::IsLibraryAvailable($r['name'], $r['version'], $r['operation'])) {
						$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing library ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'jslibrary':
					if (!Core::IsJSLibraryAvailable($r['name'], $r['version'], $r['operation'])) {
						$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing JSlibrary ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'component':
					if (!Core::IsComponentAvailable($r['name'], $r['version'], $r['operation'])) {
						$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing component ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'define':
					// Ensure that whatever define the script is expecting is there... this is useful for the EXEC_MODE define.
					if (!defined($r['name'])) {
						$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing define ' . $r['name'];
					}
					// Also if they opted to include a value... check that too.
					if ($r['value'] != null && constant($r['name']) != $r['value']) {
						$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires wrong define ' . $r['name'] . '(' . $r['value'] . ')';
					}
					break;
			}
		}

		if ($this->error) return false;

		// Check classes.  If a class is provided in another package, DON'T LOAD!
		$cs = $this->getClassList();
		foreach ($cs as $c => $file) {
			if (Core::IsClassAvailable($c)) {
				$this->error     = $this->error | Component::ERROR_CONFLICT;
				$this->errstrs[] = $c . ' already defined in another component';
				break;
			}
		}

		// Check that if the version installed is not what's in the component file, that there is a valid upgrade path.
		if(!$this->_checkUpgradePath()){
			$this->error = $this->error | Component::ERROR_UPGRADEPATH;
			$this->errstrs[] = 'No upgrade path found';
		}

		// I should have a good idea of any errors by now...
		return (!$this->error) ? true : false;
	}


	/**
	 * Get every JSLibrary in this component as an object.
	 */
	public function getJSLibraries() {
		$ret = array();
		foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('jslibrary') as $node) {
			$lib       = new JSLibrary();
			$lib->name = $node->getAttribute('name');
			// The version doesn't have to be set... it can be derived from the component version.
			$lib->version                = (($v = @$node->getAttribute('version')) ? $v : $this->_xmlloader->getRootDOM()->getAttribute('version'));
			$lib->baseDirectory          = ROOT_PDIR . 'components/' . $this->getName() . '/';
			$lib->DOMNode                = $node;
			$ret[strtolower($lib->name)] = $lib;
		}
		return $ret;
	}


	public function hasLibrary() {
		// Every component is a library now.
		return true;
	}

	public function hasJSLibrary() {
		return ($this->_xmlloader->getRootDOM()->getElementsByTagName('jslibrary')->length) ? true : false;
	}

	public function hasModule() {
		return ($this->_xmlloader->getRootDOM()->getElementsByTagName('module')->length) ? true : false;
	}

	public function hasView() {
		return ($this->_xmlloader->getRootDOM()->getElementsByTagName('view')->length) ? true : false;
	}

	/**
	 * Install this component.
	 *
	 * Returns false if nothing changed, else will return an array containing all changes.
	 *
	 * @return false | array
	 * @throws InstallerException
	 */
	public function install() {

		if ($this->isInstalled()) return false;

		if (!$this->isLoadable()) return false;

		$changes = $this->_performInstall();

		// Allow datasets to be in here too.
		foreach($this->_xmlloader->getElements('install/dataset') as $datasetel){
			$datachanges = $this->_parseDatasetNode($datasetel);
			if($datachanges !== false) $changes = array_merge($changes, $datachanges);
		}

		// Run through each task under <install> and execute it.
		/*
		if($this->_xmlloader->getRootDOM()->getElementsByTagName('install')->item(0)){
			InstallTask::ParseNode(
				$this->_xmlloader->getRootDOM()->getElementsByTagName('install')->item(0), 
				$this->getBaseDir()
			);
		}
		*/

		// Yay, it should be installed now.	Update the version in the database.
		$c = new ComponentModel($this->_name);
		$c->set('version', $this->_version);
		$c->save();
		$this->_versionDB = $this->_version;
		$this->_enabled = ($c->get('enabled') == '1');

		// And load this component into the system so anything else can access it immediately.
		$this->loadFiles();
		if (class_exists('Core')) {
			$ch = Core::Singleton();
			$ch->_registerComponent($this);
		}

		return $changes;
	}

	/**
	 * Reinstall a component with its same version.
	 * Useful for replacing corrupt assets or what not.
	 *
	 * Returns false if nothing changed, else will return an array containing all changes.
	 *
	 * @return false | array
	 * @throws InstallerException
	 */
	public function reinstall() {
		// @todo I need actual error checking here.
		if (!$this->isInstalled()) return false;

		return $this->_performInstall();
	}

	/**
	 * Upgrade this component to the newer version, if possible.
	 *
	 * Returns false if nothing changed, else will return an array containing all changes.
	 *
	 * @return false | array
	 * @throws InstallerException
	 */
	public function upgrade() {
		if (!$this->isInstalled()) return false;

		$changes = array();

		// I can now do everything else.
		$otherchanges = $this->_performInstall();
		if ($otherchanges !== false) $changes = array_merge($changes, $otherchanges);

		$canBeUpgraded = true;
		while ($canBeUpgraded) {
			// Set as false to begin with, (will be set back to true if an upgrade is ran).
			$canBeUpgraded = false;
			foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('upgrade') as $u) {
				/** @var $u DOMNode */

				// look for a valid upgrade path.
				if ($this->_versionDB == @$u->getAttribute('from')) {
					// w00t, found one...
					$canBeUpgraded = true;

					// This gets a bit tricky, I need to get all the valid upgrade elements in the order that they
					// are defined in the component.xml.
					$children = $u->childNodes;

					// The various upgrade tasks that can happen
					foreach($children as $child){
						/** @var $child DOMNode */
						switch($child->nodeName){
							case 'dataset':
								$datachanges = $this->_parseDatasetNode($child);
								if($datachanges !== false) $changes = array_merge($changes, $datachanges);
								break;
							case 'phpfileinclude':
								// Easy one :p
								include(ROOT_PDIR . trim($child->nodeValue));
								$changes[] = 'Included custom php file ' . basename($child->nodeValue);
								break;
							default:
								$changes[] = 'Ignoring unsupported upgrade directive: [' . $child->nodeName . ']';
						}
					}

					// Record this change.
					$changes[] = 'Upgraded from [' . $this->_versionDB . '] to [' . $u->getAttribute('to') . ']';

					$this->_versionDB = @$u->getAttribute('to');
					$c                = new ComponentModel($this->_name);
					$c->set('version', $this->_versionDB);
					$c->save();
				}
			}
		}

		return (sizeof($changes)) ? $changes : false;
	}

	/**
	 * Set this component as disabled in the database.
	 *
	 * Hopefully it won't break anything else :p
	 */
	public function disable(){
		// If it's not installed already, it can't be disabled!
		if(!$this->isInstalled()) return false;

		$c = new ComponentModel($this->_name);
		$c->set('enabled', false);
		$c->save();
		$this->_versionDB = null;

		// Ensure that the core component cache is purged too!
		Core::Cache()->delete('core-components');

		return true;
	}

	/**
	 * Set this component as enabled in the database.
	 */
	public function enable(){
		// If it's not installed already, it can't be disabled!
		if($this->isEnabled()) return false;

		$c = new ComponentModel($this->_name);
		$c->set('enabled', true);
		$c->save();
		$this->_enabled = true;
		//echo 'ENABLING!<br/>';
		//var_dump($c);

		// Ensure that the core component cache is purged too!
		Core::Cache()->delete('core-components');

		return true;
	}

	/**
	 * Helper function for external classes and scripts to get this component's xml DOM.
	 *
	 * @return DOMNode
	 */
	public function getRootDOM(){
		return $this->_xmlloader->getRootDOM();
	}

	/**
	 * Component installation operations all share common actions, (mostly).
	 *
	 * Returns false if nothing changed, else will return an array containing all changes.
	 *
	 * @return false | array
	 * @throws InstallerException
	 */
	private function _performInstall() {
		$changed = array();

		$change = $this->_parseDBSchema();
		if ($change !== false) $changed = array_merge($changed, $change);

		$change = $this->_parseConfigs();
		if ($change !== false) $changed = array_merge($changed, $change);

		$change = $this->_parseUserConfigs();
		if ($change !== false) $changed = array_merge($changed, $change);

		$change = $this->_parsePages();
		if ($change !== false) $changed = array_merge($changed, $change);

		$change = $this->_parseWidgets();
		if ($change !== false) $changed = array_merge($changed, $change);

		$change = $this->_installAssets();
		if ($change !== false) $changed = array_merge($changed, $change);

		// Ensure that the core component cache is purged too!
		Core::Cache()->delete('core-components');

		return (sizeof($changed)) ? $changed : false;
	}

	public function getProvides() {
		$ret = array();
		// This element itself.
		$ret[] = array(
			'name'    => strtolower($this->getName()),
			'type'    => 'component',
			'version' => $this->getVersion()
		);
		foreach ($this->_xmlloader->getElements('provides/provide') as $el) {
			// <provide name="JQuery" type="library" version="1.4"/>
			$ret[] = array(
				'name'    => strtolower($el->getAttribute('name')),
				'type'    => $el->getAttribute('type'),
				'version' => $el->getAttribute('version'),
			);
		}
		return $ret;
	}

	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 *
	 * Returns false if nothing changed, else will return an int of the number of configuration options changed.
	 *
	 * @return false | int
	 * @throws InstallerException
	 */
	private function _parseConfigs() {
		// Keep track of if this changed anything.
		$changes = array();

		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('configs');
		//$prefix = $node->getAttribute('prefix');

		// Now, get every table under this node.
		foreach ($node->getElementsByTagName('config') as $confignode) {
			$key = $confignode->getAttribute('key');
			$m   = ConfigHandler::GetConfig($key);
			$m->set('options', $confignode->getAttribute('options'));
			$m->set('type', $confignode->getAttribute('type'));
			$m->set('default_value', $confignode->getAttribute('default'));
			$m->set('description', $confignode->getAttribute('description'));
			$m->set('mapto', $confignode->getAttribute('mapto'));
			// Default from the xml, only if it's not already set.
			if (!$m->get('value')) $m->set('value', $confignode->getAttribute('default'));
			// Allow configurations to overwrite any value.  This is useful on the initial installation.
			if (isset($_SESSION['configs']) && isset($_SESSION['configs'][$key])) $m->set('value', $_SESSION['configs'][$key]);

			if ($m->save()) $changes[] = 'Set configuration [' . $m->get('key') . '] to [' . $m->get('value') . ']';

			// Make it available immediately
			ConfigHandler::CacheConfig($m);
		}

		return (sizeof($changes)) ? $changes : false;

	} // private function _parseConfigs

	/**
	 * Internal function to parse and handle the user configs in the component.xml file.
	 * This is used for installations and upgrades.
	 *
	 * Returns false if nothing changed, else will return an int of the number of configuration options changed.
	 *
	 * @return false | int
	 * @throws InstallerException
	 */
	private function _parseUserConfigs() {
		// Keep track of if this changed anything.
		$changes = array();

		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('userconfigs');

		// Now, get every table under this node.
		foreach ($node->getElementsByTagName('userconfig') as $confignode) {

			//<userconfig key="first_name" name="First Name"/>
			//<userconfig key="last_name" name="Last Name" default="" formtype="" onregistration="" options=""/>

			$key        = $confignode->getAttribute('key');
			$name       = $confignode->getAttribute('name');
			$default    = $confignode->getAttribute('default');
			$formtype   = $confignode->getAttribute('formtype');
			$onreg      = $confignode->getAttribute('onregistration');
			$onedit     = $confignode->getAttribute('onedit');
			$options    = $confignode->getAttribute('options');
			$searchable = $confignode->getAttribute('searchable');
			$validation = $confignode->getAttribute('validation');

			// Defaults
			if($onreg === null)      $onreg = 1;
			if($onedit === null)     $onedit = 1;
			if($searchable === null) $searchable = 0;

			$model = UserConfigModel::Construct($key);
			$model->set('name', $name);
			if($default)  $model->set('default_value', $default);
			if($formtype) $model->set('formtype', $formtype);
			$model->set('onregistration', $onreg);
			$model->set('onedit', $onedit);
			$model->set('searchable', $searchable);
			if($options)  $model->set('options', $options);
			$model->set('validation', $validation);


			if($model->save()) $changes[] = 'Set user config [' . $model->get('key') . '] as a [' . $model->get('formtype') . ' input]';
		}

		return (sizeof($changes)) ? $changes : false;

	} // private function _parseUserConfigs

	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 *
	 * @throws InstallerException
	 */
	private function _parsePages() {
		$changes = array();

		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('pages');
		//$prefix = $node->getAttribute('prefix');

		// Now, get every table under this node.
		foreach ($node->getElementsByTagName('page') as $subnode) {
			// Insert/Update the defaults for an entry in the database.
			// These are always global pages.
			$m = new PageModel(-1, $subnode->getAttribute('baseurl'));

			// Just something to help the log.
			$action = ($m->exists()) ? 'Updated' : 'Added';
			$admin = $subnode->getAttribute('admin');
			$selectable = ($admin ? 0 : 1); // Defaults
			if($subnode->getAttribute('selectable') !== '') $selectable = $subnode->getAttribute('selectable');

			// Do not "update" value, keep whatever the user set previously.
			if (!$m->get('rewriteurl')) {
				if ($subnode->getAttribute('rewriteurl')) $m->set('rewriteurl', $subnode->getAttribute('rewriteurl'));
				else $m->set('rewriteurl', $subnode->getAttribute('baseurl'));
			}
			// Do not "update" value, keep whatever the user set previously.
			if (!$m->get('title')) $m->set('title', $subnode->getAttribute('title'));
			// Do not "update" value, keep whatever the user set previously.
			if ($m->get('access') == '*') $m->set('access', $subnode->getAttribute('access'));
			// Do not update parent urls if the page already exists.
			if(!$m->exists()) $m->set('parenturl', $subnode->getAttribute('parenturl'));
			//$m->set('widget', $subnode->getAttribute('widget'));
			$m->set('admin', $admin);
			$m->set('selectable', $selectable);
			if ($m->save()) $changes[] = $action . ' page [' . $m->get('baseurl') . ']';
		}

		return ($changes > 0) ? $changes : false;
	}

	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 *
	 * @throws InstallerException
	 */
	private function _parseWidgets() {
		$changes = array();

		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('widgets');
		//$prefix = $node->getAttribute('prefix');

		// Now, get every table under this node.
		foreach ($node->getElementsByTagName('widget') as $subnode) {
			// Insert/Update the defaults for an entry in the database.
			$m = new WidgetModel($subnode->getAttribute('baseurl'));

			// Just something to help the log.
			$action = ($m->exists()) ? 'Updated' : 'Added';
			$installable = $subnode->getAttribute('installable');

			// Do not "update" value, keep whatever the user set previously.
			if (!$m->get('title')) $m->set('title', $subnode->getAttribute('title'));

			$m->set('installable', $installable);

			if ($m->save()){
				$changes[] = $action . ' widget [' . $m->get('baseurl') . ']';

				// Is this a new widget and it's an admin installable one?
				// If so install it to the admin widgetarea!
				if($action == 'Added' && $installable == '/admin'){
					$weight = WidgetInstanceModel::Count([
						'widgetarea' => 'Admin Dashboard',
						'page' => 'pages/admin/index.tpl',
					]) + 1;

					$wi = new WidgetInstanceModel();
					$wi->setFromArray([
						'baseurl' => $m->get('baseurl'),
						'page' => 'pages/admin/index.tpl',
						'widgetarea' => 'Admin Dashboard',
						'weight' => $weight
					]);
					$wi->save();

					$changes[] = 'Installed  widget ' . $m->get('baseurl') . ' into the admin dashboard!';
				}
			}
		}

		return ($changes > 0) ? $changes : false;
	}


	/**
	 * Internal function to parse and handle the DBSchema in the component.xml file.
	 * This is used for installations and upgrades.
	 *
	 * @throws InstallerException
	 * @returns array|false
	 */
	private function _parseDBSchema() {
		// I need to get the schema definitions first.
		$node   = $this->_xmlloader->getElement('dbschema');
		$prefix = $node->getAttribute('prefix');

		$changes = array();


		// Get the table structure as it exists in the database first, this will be the comparison point.
		$classes = $this->getClassList();
		foreach ($classes as $k => $v) {
			if ($k == 'model' || strpos($k, 'model') !== strlen($k) - 5) unset($classes[$k]);
		}

		// Do the actual processing of every Model.
		foreach ($classes as $m => $file) {
			if(!class_exists($m)) require_once($file);

			$schema = ModelFactory::GetSchema($m);
			$tablename = $m::GetTableName();

			try{
				if (Core::DB()->tableExists($tablename)) {
					// Exists, ensure that it's up to date instead.
					if(Core::DB()->modifyTable($tablename, $schema)){
						$changes[] = 'Modified table ' . $tablename;
					}
				}
				else {
					// Pass this schema into the DMI processor for create table.
					Core::DB()->createTable($tablename, $schema);
					$changes[] = 'Created table ' . $tablename;
				}
			}
			catch(DMI_Query_Exception $e){
				// Append the table name since otherwise it may be "_tmptable"... which does not provide any useful information!
				$e->query = $e->query . "\n<br/>(original table " . $tablename . ")";
				//echo '<pre>' . $e->getTraceAsString() . '</pre>'; // DEBUG //
				throw $e;
			}
		}

		return sizeof($changes) ? $changes : false;
	} // private function _parseDBSchema()


	/**
	 * Internal function to parse and handle the dataset in the <upgrade> and <install> tasks.
	 * This is used for installations and upgrades.
	 *
	 * Unlike the other parse functions, this handles a single node at a time.
	 *
	 * @param $node DOMElement
	 * @throws InstallerException
	 */
	private function _parseDatasetNode(DOMElement $node){
		$action   = $node->getAttribute('action');
		$table    = $node->getAttribute('table');
		$haswhere = false;
		$sets     = array();
		$renames  = array();
		$ds       = new Dataset();


		$ds->table($table);

		foreach($node->getElementsByTagName('datasetset') as $el){
			$sets[$el->getAttribute('key')] = $el->nodeValue;
		}

		foreach($node->getElementsByTagName('datasetrenamecolumn') as $el){
			// <datasetrenamecolumn oldname="ID" newname="id"/>
			$renames[$el->getAttribute('oldname')] = $el->getAttribute('newname');
		}

		foreach($node->getElementsByTagName('datasetwhere') as $el){
			$haswhere = true;
			$ds->where(trim($el->nodeValue));
		}

		switch($action){
			case 'alter':
				if(sizeof($sets)) throw new InstallerException('Invalid mix of arguments on ' . $action . ' dataset request, datasetset is not supported!');
				if($haswhere) throw new InstallerException('Invalid mix of arguments on ' . $action . ' dataset request, datasetwhere is not supported!');

				foreach($renames as $k => $v){
					// ALTER TABLE `controllers` CHANGE `ID` `id` INT( 11 ) NOT NULL AUTO_INCREMENT
					$ds->renameColumn($k, $v);
				}
				break;
			case 'update':
				foreach($sets as $k => $v){
					$ds->update($k, $v);
				}
				break;
			case 'insert':
				foreach($sets as $k => $v){
					$ds->insert($k, $v);
				}
				break;
			case 'delete':
				if(sizeof($sets)) throw new InstallerException('Invalid mix of arguments on ' . $action . ' dataset request');
				if(!$haswhere) throw new InstallerException('Cowardly refusing to delete with no where statement');
				$ds->delete();
				break;
			default:
				throw new InstallerException('Invalid action type, '. $action);
		}

		// and GO!
		$ds->execute();
		if($ds->num_rows){
			return array($action . ' on table ' . $table . ' affected ' . $ds->num_rows . ' records.');
		}
		else{
			return false;
		}
	}


	/**
	 * Copy in all the assets for this component into the assets location.
	 *
	 * Returns false if nothing changed, else will return an array of all the changes that occured.
	 *
	 * @return false | array
	 * @throws InstallerException
	 */
	private function _installAssets() {
		$assetbase = ConfigHandler::Get('/core/filestore/assetdir');
		$theme     = ConfigHandler::Get('/theme/selected');
		$changes   = array();

		foreach ($this->_xmlloader->getElements('/assets/file') as $node) {
			$b = $this->getBaseDir();
			// Local file is guaranteed to be a local file.
			$f = new File_local_backend($b . $node->getAttribute('filename'));

			// The new file should have a filename identical to the original, with the exception of
			// everything before the filename.. ie: the ROOT_PDIR and the asset directory.
			$newfilename = 'assets' . substr($b . $node->getAttribute('filename'), strlen($this->getAssetDir()));

			$nf = Core::File($newfilename);

			// If it's null, don't change the path any.
			if ($theme === null) {
				// Don't do anything.
			}
			// The new destination must be in the default directory, this is a 
			// bit of a hack from the usual behaviour of the filestore system.
			elseif ($theme != 'default' && strpos($nf->getFilename(), $assetbase . $theme) !== false) {
				$nf->setFilename(str_replace($assetbase . $theme, $assetbase . 'default', $nf->getFilename()));
			}

			// Check if this file even needs updated. (this is primarily used for reporting reasons)
			if ($nf->exists() && $nf->identicalTo($f)) {
				continue;
			}
			// Otherwise if it exists, I want to be able to inform the user that it was replaced and not just installed.
			elseif ($nf->exists()) {
				$action = 'Replaced';
			}
			// Otherwise otherwise, it's a new file.
			else {
				$action = 'Installed';
			}

			try {
				$f->copyTo($nf, true);
			}
			catch (Exception $e) {
				throw new InstallerException('Unable to copy [' . $f->getFilename() . '] to [' . $nf->getFilename() . ']');
			}

			$changes[] = $action . ' ' . $nf->getFilename();
		}

		if (!sizeof($changes)) return false;

		// Make sure the asset cache is purged!
		Core::Cache()->delete('asset-resolveurl');

		return $changes;
	}

	/**
	 * Helper function to see if there is a valid upgrade path from the current version installed
	 * to the version of the code available.
	 *
	 * @return bool
	 */
	private function _checkUpgradePath(){
		// Check that if the version installed is not what's in the component file, that there is a valid upgrade path.
		if($this->_versionDB && $this->_version != $this->_versionDB){

			// Assemble an array of upgrade paths, with the key/pairs being from//to versions.
			$paths = array();

			foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('upgrade') as $u) {
				$from = $u->getAttribute('from');
				$to   = $u->getAttribute('to');
				if(!isset($paths[$from])) $paths[$from] = array();

				$paths[$from][] = $to;
			}

			if(!sizeof($paths)){
				// No upgrade paths even defined!
				return false;
			}

			// Sort them version descending, makes finding the highest version number easier
			foreach($paths as $k => $vs){
				rsort($paths[$k], SORT_NATURAL);
			}
			$current = $this->_versionDB;
			$x = 0; // My anti-infinite-loop counter.
			while($current != $this->_version && $x < 20){
				++$x;
				if(isset($paths[$current])){
					$current = $paths[$current][0];
				}
				else{
					return false;
				}
			}

			// Yay, if it's gotten here, that means that there was a valid upgrade path!
			return true;
		}
		else{
			// Easy enough :)
			// The else is that it's installed and up to date.
			return true;
		}
	}

	/**
	 * Get the base directory of this component
	 *
	 * Generally /home/foo/public_html/components/componentname/
	 *
	 * @return string
	 */
	public function getBaseDir($prefix = ROOT_PDIR) {
		if ($this->_name == 'core') {
			return $prefix;
		}
		else {
			return $prefix . 'components/' . strtolower($this->_name) . '/';
		}
	}


}
