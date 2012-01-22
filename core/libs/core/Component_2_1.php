<?php
/**
 * Core component system, responsible for reading and parsing the component.xml,
 * saving it, and installing all components on the system.
 * 
 * @package Core
 * @since 2011.06
 * @version 2.1
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class Component_2_1{
	
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
	protected $_enabled;
	
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
	 * User can disable a component in the admin, if not enabled don't load the component at all.
	 * 
	 * @var boolean
	 */
	protected $enabled = true;
	
	/**
	 * Version of the component, as per the database (installed version).
	 * 
	 * @var string
	 */
	private $_versionDB = false;
	
	
	/**
	 * 
	 * @var array
	 */
	private $_requires = array();
	
	/**
	 * Each component can have an execution mode, by default it's "web".
	 * This is used because some components will bomb out in CLI mode, and vice versa.
	 * @var string
	 */
	private $_execMode = 'WEB';
	
	// A set of error codes components may encounter.
	const ERROR_NOERROR = 0;           // 0000
	const ERROR_INVALID = 1;           // 0001
	const ERROR_WRONGEXECMODE = 2;     // 0010
	const ERROR_MISSINGDEPENDENCY = 4; // 0100
	const ERROR_CONFLICT = 8;          // 1000
	
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
	 * This object only needs to be loaded once
	 * @var boolean
	 */
	private $_loaded = false;
	
	
	public function __construct($filename = null){
		$this->_file = \Core\file($filename);
		
		$this->_xmlloader = new XMLLoader();
		$this->_xmlloader->setRootName('component');
		
		if(!$this->_xmlloader->loadFromFile($filename)){
			throw new Exception('Parsing of XML Metafile [' . $filename . '] failed, not valid XML.');
		}
	}
	
	
	public function load(){
		if($this->_loaded) return;
		
		if(($mode = $this->_xmlloader->getRootDOM()->getAttribute('execmode'))){
			$this->_execMode = strtoupper($mode);
		}
		
		$this->_name = $this->_xmlloader->getRootDOM()->getAttribute('name');
		$this->_version = $this->_xmlloader->getRootDOM()->getAttribute("version");
		
		// Load the database information, if there is any.
		$dat = ComponentFactory::_LookupComponentData($this->_name);
		if(!$dat) return;
		
		$this->_versionDB = $dat['version'];
		$this->_enabled = ($dat['enabled']) ? true : false;
		$this->_loaded = true;
	}
	
	
	/**
	 * Save this component metadata back to its XML file.
	 * Useful in packager scripts.
	 */
	public function save($minified = false){
		// Ensure there's a required namespace on the root node.
		$this->_xmlloader->getRootDOM()->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");


		/////////////   Handle the file sections and their hashes \\\\\\\\\\\\\\

		// Purge the 'otherfiles' section.
		$this->removeElements('//otherfiles');
		// Also purge any module/library/view files, as these are built automatically.
		$this->removeElements('//library/file');
		$this->removeElements('//module/file');
		$this->removeElements('//view/file');
		//if($this->_xmlloader->getElement('/otherfiles', false)){
		//	$this->_xmlloader->getRootDOM()->removeChild($this->_xmlloader->getElement('/otherfiles'));
		//}
		$otherfilesnode = $this->_xmlloader->getElement('//otherfiles');
		
		$it = $this->getDirectoryIterator();
		$hasview = $this->hasView();
		$viewd = ($hasview)? $this->getViewSearchDir() : null;
		$assetd = $this->getAssetDir();
		$strlen = strlen($this->getBaseDir());
		foreach($it as $file){
			$el = false;
			$fname = substr($file->getFilename(), $strlen);
			
			if($hasview && $file->inDirectory($viewd)){
				$el = $this->_xmlloader->getElement('/view/file[@filename="' . $fname . '"]');
			}
			elseif($assetd && $file->inDirectory($assetd)){
				// It's an asset!
				$el = $this->_xmlloader->getElement('/assets/file[@filename="' . $fname . '"]');
			}
			else{
				// Only add it if the file doesn't exist already.
				$el = $this->_xmlloader->getElement('//library/file[@filename="' . $fname . '"]|//module/file[@filename="' . $fname . '"]|//view/file[@filename="' . $fname . '"]', false);
				// Scan through this file and file any classes that are provided.
				if(preg_match('/\.php$/i', $fname)){
					$fconts = file_get_contents($file->getFilename());
					
					// Trim out the comments to prevent false readings.
					
					// Will remove /* ... */ multi-line comments.
					$fconts = preg_replace(':/\*.*\*/:Us', '', $fconts);
					// Will remove // single-line comments.
					$fconts = preg_replace('://.*$:', '', $fconts);
					
					if($el){
						$getnames = ($el->parentNode->nodeName == 'library' || $el->parentNode->nodeName == 'module');
					}
					else{
						// Does this file contain something that extends Controller?
						if(preg_match('/^(abstract ){0,1}class[ ]*[a-z0-9_\-]*[ ]*extends controller/im', $fconts)){
							$el = $this->_xmlloader->getElement('/module/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						// Widgets also go in the module section.
						elseif(preg_match('/^class[ ]*[a-z0-9_\-]*[ ]*extends widget/im', $fconts)){
							$el = $this->_xmlloader->getElement('/module/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						elseif(preg_match('/^(abstract |final ){0,1}class[ ]*[a-z0-9_\-]*/im', $fconts)){
							$el = $this->_xmlloader->getElement('/library/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						elseif(preg_match('/^interface[ ]*[a-z0-9_\-]*/im', $fconts)){
							$el = $this->_xmlloader->getElement('/library/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						else{
							$el = $this->_xmlloader->getElement('/otherfiles/file[@filename="' . $fname . '"]');
							$getnames = false;
						}
					}
					
					// $el will now be set in the correct location!
					
					if($getnames){
						// Well... get the classes!
						$viewclasses = array();
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*controller/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							$this->_xmlloader->getElementFrom('provides[@type="controller"][@name="' . $foundclass . '"]', $el);
							// This is needed to tell the rest of the save logic to ignore the save for classes.
							$viewclasses[] = $foundclass;
						}
						
						preg_match_all('/^class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*widget/im', $fconts, $ret);
						foreach($ret[1] as $foundclass){
							$this->_xmlloader->getElementFrom('provides[@type="widget"][@name="' . $foundclass . '"]', $el);
							// This is needed to tell the rest of the save logic to ignore the save for classes.
							$viewclasses[] = $foundclass;
						}
						
						// Add any class found in this file.
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							if(in_array($foundclass, $viewclasses)) continue;
							$this->_xmlloader->getElementFrom('provides[@type="class"][@name="' . $foundclass . '"]', $el);
						}
						
						// Allow interfaces to be associated as a provided element too.
						preg_match_all('/^(interface)[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							if(in_array($foundclass, $viewclasses)) continue;
							$this->_xmlloader->getElementFrom('provides[@type="interface"][@name="' . $foundclass . '"]', $el);
						}
					}
				}
								
				//$el = $this->_xmlloader->getElement('file[@filename="' . $fname . '"]', false);
				if(!$el){
					$el = $this->_xmlloader->getElement('/otherfiles/file[@filename="' . $fname . '"]');
				}
			}

			// This really shouldn't NOT hit since the file is created under /otherfiles if it didn't exist before.... but who knows.
			if($el){
				// Tack on the hash of the file.
				$el->setAttribute('md5', $file->getHash());
			}
		}
		
		
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
		

		// This needs to be the final step... write the XML doc back to the file.
		$XMLFilename = $this->getXMLFilename();
		//echo $this->asPrettyXML(); // DEBUG //
		if($minified){
			file_put_contents($XMLFilename, $this->asMinifiedXML());
		}
		else{
			file_put_contents($XMLFilename, $this->asPrettyXML());
		}
	}
	
	/**
	 * Save or get the package XML for this component.  This is useful for the 
	 * packager
	 * 
	 * @param boolean $minified
	 * @param string $filename 
	 */
	public function savePackageXML($minified = true, $filename = false){
		
		// Instantiate a new XML Loader object and get it ready to use.
		$dom = new XMLLoader();
		$dom->setRootName('package');
		$dom->load();
		
		// Populate the root attributes for this component package.
		$dom->getRootDOM()->setAttribute('type', 'component');
		$dom->getRootDOM()->setAttribute('name', $this->getName());
		$dom->getRootDOM()->setAttribute('version', $this->getVersion());
		
		// Declare the packager
		$dom->createElement('packager[version="' . Core::GetComponent()->getVersion() . '"]');
		
		// Copy over any provide directives.
		foreach($this->_xmlloader->getRootDOM()->getElementsByTagName('provides') as $u){
			$newu = $dom->getDOM()->importNode($u);
			$dom->getRootDOM()->appendChild($newu);
		}
		$dom->getElement('/provides[type="component"][name="' . strtolower($this->getName()) . '"][version="' . $this->getVersion() . '"]');
		
		// Copy over any requires directives.
		foreach($this->_xmlloader->getRootDOM()->getElementsByTagName('requires') as $u){
			$newu = $dom->getDOM()->importNode($u);
			$dom->getRootDOM()->appendChild($newu);
		}
		
		// Copy over any upgrade directives.
		// This one can be useful for an existing installation to see if this 
		// package can provide a valid upgrade path.
		foreach($this->_xmlloader->getRootDOM()->getElementsByTagName('upgrade') as $u){
			$newu = $dom->getDOM()->importNode($u);
			$dom->getRootDOM()->appendChild($newu);
		}
		
		// Tack on description
		$desc = $this->_xmlloader->getElement('/description', false);
		if($desc){
			$newd = $dom->getDOM()->importNode($desc);
			$newd->nodeValue = $desc->nodeValue;
			$dom->getRootDOM()->appendChild($newd);
		}

		
		$out = ($minified) ? $dom->asMinifiedXML() : $dom->asPrettyXML();
		
		if($filename){
			file_put_contents($filename, $out);
		}
		else{
			return $out;
		}
	}
	
	public function getRequires(){
		$ret = array();
		foreach($this->_xmlloader->getRootDOM()->getElementsByTagName('requires') as $r){
			$t = $r->getAttribute('type');
			$n = $r->getAttribute('name');
			$v = @$r->getAttribute('version');
			$op = @$r->getAttribute('operation');
			//$value = @$r->getAttribute('value');

			// Defaults.
			if($v == '') $v = false;
			if($op == '') $op = 'ge';

			$ret[] = array(
				'type' => strtolower($t),
				'name' => $n,
				'version' => strtolower($v),
				'operation' => strtolower($op),
				//'value' => $value,
			);
		}
		return $ret;
	}
	
	public function getDescription(){
		if(is_null($this->_description)) $this->_description = $this->_xmlloader->getElement('//description')->nodeValue;
		
		return $this->_description;
	}
	
	public function setDescription($desc){
		// Set the cache first.
		$this->_description = $desc;
		// And set the data in the original DOM.
		$this->_xmlloader->getElement('//description')->nodeValue = $desc;
	}
	
	
	
	public function loadFiles(){
		// Include any includes requested.
		// This adds support for namespaced functions.
		// <includes>
		//     <include filename="core/functions/Core.functions.php"/>
		// </includes>
		foreach($this->_xmlloader->getElements('/includes/include') as $f){
			require_once($this->getBaseDir() . $f->getAttribute('filename'));
		}
		
		
		// Register any hooks that may be present.
		foreach($this->_xmlloader->getElementsByTagName('hookregister') as $h){
			$hook = new Hook($h->getAttribute('name'));
			$hook->description = $h->getAttribute('description');
			HookHandler::RegisterHook($hook);
		}
		
		// Register any events that may be present.
		foreach($this->_xmlloader->getElementsByTagName('hook') as $h){
			$event = $h->getAttribute('name');
			$call = $h->getAttribute('call');
			$type = @$h->getAttribute('type');
			HookHandler::AttachToHook($event, $call, $type);
		}
		
		
		// This component may have special form elements registered.  Check!
		foreach($this->_xmlloader->getElements('/forms/formelement') as $node){
			Form::$Mappings[$node->getAttribute('name')] = $node->getAttribute('class');
		}
		
		
		return true;
	}
	
	public function getLibraryList(){
		// Get an array of library -> version
		$libs = array();
		
		// Every component is a library
		$libs[strtolower($this->_name)] = $this->_versionDB;
		
		foreach($this->_xmlloader->getElements('provides/provide') as $p){
			if(strtolower($p->getAttribute('type')) == 'library'){
				$v = @$p->getAttribute('version');
				if(!$v) $v = $this->_versionDB;
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
	public function getClassList(){
		// Get an array of class -> file (fully resolved)
		$classes = array();
		
		//foreach($this->_xmlloader->getElementByTagName('files')->getElementsByTagName('file') as $f){
		foreach($this->_xmlloader->getElements('/files/file') as $f){
			$filename = $this->getBaseDir() . $f->getAttribute('filename');
			//foreach($f->getElementsByTagName('provides') as $p){
			foreach($f->getElementsByTagName('class') as $p){
				$n = strtolower($p->getAttribute('name'));
				$classes[$n] = $filename;
			}
			
			foreach($f->getElementsByTagName('interface') as $p){
				$n = strtolower($p->getAttribute('name'));
				$classes[$n] = $filename;
			}
			
			foreach($f->getElementsByTagName('controller') as $p){
				$n = strtolower($p->getAttribute('name'));
				$classes[$n] = $filename;
			}
			
			foreach($f->getElementsByTagName('widget') as $p){
				$n = strtolower($p->getAttribute('name'));
				$classes[$n] = $filename;
			}
		}
		
		return $classes;
	}
	
	/**
	 * Get an array of widget names provided in this component.
	 * 
	 * @return array
	 */
	public function getWidgetList(){
		$widgets = array();
		
		foreach($this->_xmlloader->getElements('/files/file') as $f){
			$filename = $this->getBaseDir() . $f->getAttribute('filename');
			foreach($f->getElementsByTagName('widget') as $p){
				$widgets[] = $p->getAttribute('name');
			}
		}
		/*
		if($this->hasModule()){
			foreach($this->_xmlloader->getElementByTagName('module')->getElementsByTagName('file') as $f){
				foreach($f->getElementsByTagName('provides') as $p){
					if(strtolower($p->getAttribute('type')) == 'widget'){
						$widgets[] = $p->getAttribute('name');
					}
				}
			}
		}
		*/
		return $widgets;
	}
	
	public function getViewClassList(){
		$classes = array();
		if($this->hasModule()){
			foreach($this->_xmlloader->getElementByTagName('module')->getElementsByTagName('file') as $f){
				$filename = $this->getBaseDir() . $f->getAttribute('filename');
				foreach($f->getElementsByTagName('provides') as $p){
					switch(strtolower($p->getAttribute('type'))){
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
	public function getViewList(){
		$views = array();
		if($this->hasView()){
			foreach($this->_xmlloader->getElementByTagName('view')->getElementsByTagName('tpl') as $t){
				$filename = $this->getBaseDir() . $t->getAttribute('filename');
				$name = $t->getAttribute('name');
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
	public function getControllerList(){
		// Get an array of class -> file (fully resolved)
		$classes = array();
		
		//foreach($this->_xmlloader->getElementByTagName('files')->getElementsByTagName('file') as $f){
		foreach($this->_xmlloader->getElements('/files/file') as $f){
			$filename = $this->getBaseDir() . $f->getAttribute('filename');
			//foreach($f->getElementsByTagName('provides') as $p){
			
			foreach($f->getElementsByTagName('controller') as $p){
				$n = strtolower($p->getAttribute('name'));
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
	public function getSmartyPluginDirectory(){
		$d = $this->_xmlloader->getElement('/smartyplugins')->getAttribute('directory');
		if($d) return $this->getBaseDir() . $d;
		else return false;
	}
	
	public function getScriptLibraryList(){
		$libs = array();
		foreach($this->_xmlloader->getElements('/provides/scriptlibrary') as $s){
			$libs[strtolower($s->getAttribute('name'))] = $s->getAttribute('call');
		}
		return $libs;
	}


	public function getViewSearchDir(){
		if($this->hasView()){
			// Using the searchdir attribute is the preferred method.
			$att = @$this->_xmlloader->getElement('/view')->getAttribute('searchdir');
			if($att){
				return $this->getBaseDir() . $att . '/';
			}
			elseif(($att = $this->_xmlloader->getElements('/view/searchdir')->item(0))){
				// Try the 'searchdir' element instead.
				return $this->getBaseDir() .$att->getAttribute('dir') . '/';
			}
			elseif(is_dir($this->getBaseDir() . 'templates')){
				// Still no?!?  Try just a filesystem check instead...
				return $this->getBaseDir() . 'templates';
			}
			else return false;
		}
	}
	
	public function getAssetDir(){
		// Core has a special exception...
		if($this->getName() == 'core') $d = $this->getBaseDir() . 'core/assets';
		else $d = $this->getBaseDir() . 'assets';
		
		if(is_dir($d)) return $d;
		else return null;
	}
	
	/**
	 * @deprecated 2012.01
	 * @return array
	 */
	public function getIncludePaths(){
		return array();
	}
	
	
	
	/**
	 * Get an array of the table names in the DB schema.
	 * @return array
	 */
	public function getDBSchemaTableNames(){
		$ret = array();
		foreach($this->_xmlloader->getElement('dbschema')->getElementsByTagName('table') as $table){
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
	public function setDBSchemaTableNames($arr){
		// Easiest way... just drop the current set.
		$this->_xmlloader->getRootDOM()->removeChild($this->_xmlloader->getElement('/dbschema'));
		// And recreate it.
		$node = $this->_xmlloader->getElement('/dbschema[@prefix="' . DB_PREFIX . '"]');
		foreach($arr as $k){
			if(!trim($k)) continue;
			$tablenode = $this->getDOM()->createElement('table');
			$tablenode->setAttribute('name', $k);
			$node->appendChild($tablenode);
			unset($tablenode);
		}
	}
	
	
	
	public function getVersionInstalled(){
		return $this->_versionDB;
	}
	
	/**
	 * Components are components, (unless it's the core)
	 * 
	 * @return string 
	 */
	public function getType(){
		if($this->_name == 'core') return 'core';
		else return 'component';
	}
	
	/**
	 * Get this component's name
	 * 
	 * @return string
	 */
	public function getName(){
		return $this->_name;
	}
	
	/**
	 * Get this component's version
	 *
	 * @return string
	 */
	public function getVersion(){
		return $this->_version;
	}
	
	
	
	
	
	public function isValid(){
		return (!$this->error & Component::ERROR_INVALID);
	}
	
	public function isInstalled(){
		return ($this->_versionDB === false)? false : true;
	}
	
	public function needsUpdated(){
		return ($this->_versionDB != $this->_version);
	}
	
	public function getErrors($glue = '<br/>'){
		if($glue){
			return implode($glue, $this->errstrs);
		}
		else{
			return $this->errors;
		}
	}
	
	public function isEnabled(){
		return $this->_enabled;
	}
	
	/**
	 * Check if this component is loadable in the environment's current state.
	 * 
	 * This cannot be cached because it's called multiple times in the loader.
	 * ie: com1 needs com2, but com1 is checked first in the loop.
	 */
	public function isLoadable(){
		// Invalid ones are not loadable... don't even try ;)
		if($this->error & Component::ERROR_INVALID){
			return false;
		}
		
		// Reset the error info.
		$this->error = 0;
		$this->errstrs = array();
		
		// Check the mode of it also, quick check.
		if($this->_execMode != 'BOTH'){
			if($this->_execMode != EXEC_MODE){
				$this->error = $this->error | Component::ERROR_WRONGEXECMODE;
				$this->errstrs[] = 'Wrong execution mode, can only be ran in ' . $this->_execMode . ' mode';
			}
		}
		
		// Can this component be loaded as-is?
		foreach($this->getRequires() as $r){
			switch($r['type']){
				case 'library':
					if(!Core::IsLibraryAvailable($r['name'], $r['version'], $r['operation'])){
						$this->error = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing library ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'jslibrary':
					if(!Core::IsJSLibraryAvailable($r['name'], $r['version'], $r['operation'])){
						$this->error = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing JSlibrary ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'component':
					if(!Core::IsComponentAvailable($r['name'], $r['version'], $r['operation'])){
						$this->error = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing component ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'define':
					// Ensure that whatever define the script is expecting is there... this is useful for the EXEC_MODE define.
					if(!defined($r['name'])){
						$this->error = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing define ' . $r['name'];
					}
					// Also if they opted to include a value... check that too.
					if($r['value'] != null && constant($r['name']) != $r['value']){
						$this->error = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires wrong define ' . $r['name'] . '(' . $r['value'] . ')';
					}
					break;
			}
		}
		
		if($this->error) return false;
		
		// Check classes.  If a class is provided in another package, DON'T LOAD!
		$cs = $this->getClassList();
		foreach($cs as $c => $file){
			if(Core::IsClassAvailable($c)){
				$this->error = $this->error | Component::ERROR_CONFLICT;
				$this->errstrs[] = $c . ' already defined in another component';
				break;
			}
		}
		
		// I should have a good idea of any errors by now...
		return (!$this->error)? true : false;
	}
	
	
	
	/**
	 * Get every JSLibrary in this component as an object.
	 */
	public function getJSLibraries(){
		$ret = array();
		foreach($this->_xmlloader->getRootDOM()->getElementsByTagName('jslibrary') as $node){
			$lib = new JSLibrary();
			$lib->name = $node->getAttribute('name');
			// The version doesn't have to be set... it can be derived from the component version.
			$lib->version = (($v = @$node->getAttribute('version'))? $v : $this->_xmlloader->getRootDOM()->getAttribute('version'));
			$lib->baseDirectory = ROOT_PDIR . 'components/' . $this->getName() . '/';
			$lib->DOMNode = $node;
			$ret[strtolower($lib->name)] = $lib;
		}
		return $ret;
	}
	
	
	
	public function hasLibrary(){
		// Every component is a library now.
		return true;
	}
	
	public function hasJSLibrary(){
		return ($this->_xmlloader->getRootDOM()->getElementsByTagName('jslibrary')->length)? true : false;
	}
	
	public function hasModule(){
		return ($this->_xmlloader->getRootDOM()->getElementsByTagName('module')->length)? true : false;
	}
	
	public function hasView(){
		return ($this->_xmlloader->getRootDOM()->getElementsByTagName('view')->length)? true : false;
	}
	
	
	public function install(){
		
		// @todo I need actual error checking here.
		if($this->isInstalled()) return false;
		
		if(!$this->isLoadable()) return false;
		
		$this->_parseDBSchema();
		
		$this->_parseConfigs();
		
		$this->_parsePages();
		
		$this->_installAssets();
		
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
		//DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array($this->_name, $this->_version));
		$this->_versionDB = $this->_version;
		
		// And load this component into the system so anything else can access it immediately.
		$this->loadFiles();
		if(class_exists('Core')){
			$ch = Core::Singleton();
			$ch->_registerComponent($this);
		}
		
		return true;
	}
	
	public function reinstall(){
		// @todo I need actual error checking here.
		if(!$this->isInstalled()) return false;
		
		$changed = false;
		
		if($this->_parseDBSchema()) $changed = true;
		
		if($this->_parseConfigs()) $changed = true;
		
		if($this->_parsePages()) $changed = true;
		
		if($this->_installAssets()) $changed = true;
		
		// @todo What else should be done?
		
		return $changed;
	}
	
	public function upgrade(){
		if(!$this->isInstalled()) return false;
		
		$canBeUpgraded = true;
		while($canBeUpgraded){
			// Set as false to begin with, (will be set back to true if an upgrade is ran).
			$canBeUpgraded = false;
			foreach($this->_xmlloader->getRootDOM()->getElementsByTagName('upgrade') as $u){
				// look for a valid upgrade path.
				if($this->_versionDB == @$u->getAttribute('from')){
					// w00t, found one...
					$canBeUpgraded = true;
					
					$result = InstallTask::ParseNode($u,  $this->getBaseDir());
					if(!$result){
						if(DEVELOPMENT_MODE){
							trigger_error('Upgrade of Component ' . $this->_name . ' failed.', E_USER_NOTICE);
						}
						return;
					}
					
					$this->_versionDB = @$u->getAttribute('to');
					$c = new ComponentModel($this->_name);
					$c->set('version', $this->_versionDB);
					$c->save();
					//DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array($this->_name, $this->_versionDB));
				}
			}
		}
		
		$this->_parseDBSchema();
		
		$this->_parseConfigs();
		
		$this->_parsePages();
		
		$this->_installAssets();
	}
	
	public function getProvides(){
		$ret = array();
		// This element itself.
		$ret[] = array(
			'name' => strtolower($this->getName()),
			'type' => 'component',
			'version' => $this->getVersion()
		);
		foreach($this->_xmlloader->getElements('provides/provide') as $el){
			// <provide name="JQuery" type="library" version="1.4"/>
			$ret[] = array(
				'name' => strtolower($el->getAttribute('name')),
				'type' => $el->getAttribute('type'),
				'version' => $el->getAttribute('version'),
			);
		}
		return $ret;
	}
	
	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 * 
	 * @return bool True if something changed, false if nothing changed.
	 */
	private function _parseConfigs(){
		// Keep track of if this changed anything.
		$changed = false;
		
		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('configs');
		//$prefix = $node->getAttribute('prefix');
		
		// Now, get every table under this node.
		foreach($node->getElementsByTagName('config') as $confignode){
			$key = $confignode->getAttribute('key');
			$m = ConfigHandler::GetConfig($key);
			$m->set('options', $confignode->getAttribute('options'));
			$m->set('type', $confignode->getAttribute('type'));
			$m->set('default_value', $confignode->getAttribute('default'));
			$m->set('description', $confignode->getAttribute('description'));
			$m->set('mapto', $confignode->getAttribute('mapto'));
			if(!$m->get('value')) $m->set('value', $confignode->getAttribute('default'));
			if($m->save()) $changed = true;
		}
		
		return $changed;
		
	} // private function _parseConfigs
	
	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 */
	private function _parsePages(){
		$changed = false;
		
		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('pages');
		//$prefix = $node->getAttribute('prefix');
		
		// Now, get every table under this node.
		foreach($node->getElementsByTagName('page') as $subnode){
			// <config key="/core/theme" type="string" default="default" description="The theme of the site"/>
			// Insert/Update the defaults for an entry in the database.
			$m = new PageModel($subnode->getAttribute('baseurl'));
			// Do not "update" value, keep whatever the user set previously.
			if(!$m->get('rewriteurl')){
				if($subnode->getAttribute('rewriteurl')) $m->set('rewriteurl', $subnode->getAttribute('rewriteurl'));
				else $m->set('rewriteurl', $subnode->getAttribute('baseurl'));
			}
			// Do not "update" value, keep whatever the user set previously.
			if(!$m->get('title')) $m->set('title', $subnode->getAttribute('title'));
			// Do not "update" value, keep whatever the user set previously.
			if($m->get('access') == '*') $m->set('access', $subnode->getAttribute('access'));
			$m->set('widget', $subnode->getAttribute('widget'));
			$m->set('admin', $subnode->getAttribute('admin'));
			if($m->save()) $changed = true;
		}
		
		return $changed;
	}
	
	
	/**
	 * Internal function to parse and handle the DBSchema in the component.xml file.
	 * This is used for installations and upgrades.
	 */
	private function _parseDBSchema(){
		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('dbschema');
		$prefix = $node->getAttribute('prefix');
		
		$changed = false;
		
		
		// Get the table structure as it exists in the database first, this will be the comparison point.
		$classes = $this->getClassList();
		foreach($classes as $k => $v){
			if($k == 'model' || strpos($k, 'model') !== strlen($k) - 5) unset($classes[$k]);
		}
		
		// Do the actual processing of every Model.
		foreach($classes as $m => $file){
			require_once($file);
			
			$s = $m::GetSchema();
			$i = $m::GetIndexes();
			$tablename = $m::GetTableName();

			$schema = array('schema' => $s, 'indexes' => $i);
			
			if(Core::DB()->tableExists($tablename)){
				// Exists, ensure that it's up to date instead.
				Core::DB()->modifyTable($tablename, $schema);
			}
			else{
				// Pass this schema into the DMI processor for create table.
				Core::DB()->createTable($tablename, $schema);
			}
		}
		
		return $changed;
	} // private function _parseDBSchema()
	
	/**
	 * Copy in all the assets for this component into the assets location.
	 * 
	 * @return boolean True if something changed, false if nothing changed.
	 */
	private function _installAssets(){
		$assetbase = ConfigHandler::Get('/core/filestore/assetdir');
		$theme = ConfigHandler::Get('/theme/selected');
		$changed = false;
		
		foreach($this->_xmlloader->getElements('/assets/file') as $node){
			$b = $this->getBaseDir();
			// Local file is guaranteed to be a local file.
			$f = new File_local_backend($b . $node->getAttribute('filename'));
			
			// The new file should have a filename identical to the original, with the exception of
			// everything before the filename.. ie: the ROOT_PDIR and the asset directory.
			$newfilename = 'assets' . substr($b . $node->getAttribute('filename'), strlen($this->getAssetDir()));
			$nf = Core::File($newfilename);
			
			// If it's null, don't change the path any.
			if($theme === null){
				// Don't do anything.
			}
			// The new destination must be in the default directory, this is a 
			// bit of a hack from the usual behaviour of the filestore system.
			elseif($theme != 'default' && strpos($nf->getFilename(), $assetbase . $theme) !== false){
				$nf->setFilename(str_replace($assetbase . $theme, $assetbase . 'default', $nf->getFilename()));
			}
			
			// Check if this file even needs updated. (this is primarily used for reporting reasons)
			if($nf->exists() && $nf->identicalTo($f)) continue;
			
			$f->copyTo($nf, true);
			// Something changed.
			$changed = true;
		}
		
		if(!$changed) return false;
		
		// Make sure the asset cache is purged!
		Core::Cache()->delete('asset-resolveurl');
		
		return true;
	}
	
	/**
	 * Get the base directory of this component
	 * 
	 * Generally /home/foo/public_html/components/componentname/
	 * 
	 * @return string
	 */
	public function getBaseDir($prefix = ROOT_PDIR){
		if($this->_name == 'core'){
			return $prefix;
		}
		else{
			return $prefix . 'components/' . strtolower($this->_name) . '/';
		}
	}
	
	
}
