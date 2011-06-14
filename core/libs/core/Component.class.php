<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009	Charlie Powell <powellc@powelltechs.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.	If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

/**
 * Component object.
 * 
 * Components encompass libraries, modules, templates, and any upgrade/install/remove sql.
 */
class Component extends InstallArchiveAPI{
	/**
	 * User can disable a component in the admin, if not enabled don't load the component at all.
	 * 
	 * @var boolean
	 */
	public $enabled = true;
	
	/**
	 * Version of the component, as per the database (installed version).
	 * 
	 * @var string
	 */
	private $_versionDB = false;
	

	// @todo What was this going to be used for again?
	//private $files = array();
	
	
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
	
	
	public function __construct($name = null){
		//if($name){
			$this->_name = $name;
			$this->_type = InstallArchiveAPI::TYPE_COMPONENT;
		//	$this->load();
		//}
	}
	
	public function load(){
		try{
			parent::load();
		}
		catch(Exception $e){
			// Damn... couldn't load the component...
			die("Could not load " . $this->getName());
		}
		
		if(($mode = @$this->getRootDOM()->getAttribute('execmode'))){
			$this->_execMode = strtoupper($mode);
		}
		
		// Now look up the component in the database (for installed version).
		//if(class_exists('DB') && Core::IsInstalled()){
		if(class_exists('DB')){
			$q = DB::Execute("SELECT * FROM `" . DB_PREFIX . "component` WHERE `name` = ?", $this->_name);
			if(!$q) return false;
			
			if($q->numRows() > 0){
				$this->_versionDB = $q->fields['version'];
				$this->enabled = ($q->fields['enabled']);
			}
			else{
				// Indicate it's not installed yet
				$this->_versionDB = false;
				// But it is not disabled...
				$this->enabled = true;
			}
		}
		
		return true;
	}
	
	/**
	 * Save this component metadata back to its XML file.
	 * Useful in packager scripts.
	 */
	public function save(){
		// Ensure there's a required namespace on the root node.
		$this->getRootDOM()->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");


		/////////////   Handle the file sections and their hashes \\\\\\\\\\\\\\

		// Purge the 'otherfiles' section.
		$this->removeElements('//otherfiles');
		// Also purge any module/library/view files, as these are built automatically.
		$this->removeElements('//library/file');
		$this->removeElements('//module/file');
		$this->removeElements('//view/file');
		//if($this->getElement('/otherfiles', false)){
		//	$this->getRootDOM()->removeChild($this->getElement('/otherfiles'));
		//}
		$otherfilesnode = $this->getElement('//otherfiles');
		
		$it = $this->getDirectoryIterator();
		$hasview = $this->hasView();
		$viewd = ($hasview)? $this->getViewSearchDir() : null;
		$assetd = $this->getAssetDir();
		$strlen = strlen($this->getBaseDir());
		foreach($it as $file){
			$el = false;
			$fname = substr($file->getFilename(), $strlen);

			if($hasview && $file->inDirectory($viewd)){
				$el = $this->getElement('/view/file[@filename="' . $fname . '"]');
			}
			elseif($assetd && $file->inDirectory($assetd)){
				// It's an asset!
				$el = $this->getElement('/assets/file[@filename="' . $fname . '"]');
			}
			else{
				// Only add it if the file doesn't exist already.
				$el = $this->getElement('//library/file[@filename="' . $fname . '"]|//module/file[@filename="' . $fname . '"]|//view/file[@filename="' . $fname . '"]', false);
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
							$el = $this->getElement('/module/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						// WidgetControllers also go in the module section.
						elseif(preg_match('/^(abstract ){0,1}class[ ]*[a-z0-9_\-]*[ ]*extends widgetcontroller/im', $fconts)){
							$el = $this->getElement('/module/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						elseif(preg_match('/^(abstract |final ){0,1}class[ ]*[a-z0-9_\-]*/im', $fconts)){
							$el = $this->getElement('/library/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						elseif(preg_match('/^interface[ ]*[a-z0-9_\-]*/im', $fconts)){
							$el = $this->getElement('/library/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						else{
							$el = $this->getElement('/otherfiles/file[@filename="' . $fname . '"]');
							$getnames = false;
						}
					}
					
					// $el will now be set in the correct location!
					
					if($getnames){
						// Well... get the classes!
						$viewclasses = array();
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*controller/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							$this->getElementFrom('provides[@type="controller"][@name="' . $foundclass . '"]', $el);
							// This is needed to tell the rest of the save logic to ignore the save for classes.
							$viewclasses[] = $foundclass;
						}
						
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*widgetcontroller/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							$this->getElementFrom('provides[@type="widgetcontroller"][@name="' . $foundclass . '"]', $el);
							// This is needed to tell the rest of the save logic to ignore the save for classes.
							$viewclasses[] = $foundclass;
						}
						
						// Add any class found in this file.
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							if(in_array($foundclass, $viewclasses)) continue;
							$this->getElementFrom('provides[@type="class"][@name="' . $foundclass . '"]', $el);
						}
						
						// Allow interfaces to be associated as a provided element too.
						preg_match_all('/^(interface)[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							if(in_array($foundclass, $viewclasses)) continue;
							$this->getElementFrom('provides[@type="interface"][@name="' . $foundclass . '"]', $el);
						}
					}
				}
								
				//$el = $this->getElement('file[@filename="' . $fname . '"]', false);
				if(!$el){
					$el = $this->getElement('/otherfiles/file[@filename="' . $fname . '"]');
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
			$rs = DB::Execute("SELECT * FROM " . DB_PREFIX . "page WHERE ( `baseurl` = '/$c' OR `baseurl` LIKE '/$c/%' ) AND `fuzzy` = '0' AND `admin` = '1'");
			foreach($rs as $row){
				$node = $this->getElement('/pages/page[@baseurl="' . $row['baseurl'] . '"]');
				$node->setAttribute('admin', $row['admin']);
				$node->setAttribute('widget', $row['widget']);
				$node->setAttribute('access', $row['access']);
				$node->setAttribute('title', $row['title']);
			}
		}
		
		
		
		///////////////////////  Handle the config options \\\\\\\\\\\\\\\\\\\\\
		
		$rs = DB::Execute("SELECT * FROM " . DB_PREFIX . "config WHERE `key` LIKE '/" . $this->getName() . "/%'");
		foreach($rs as $row){
			$node = $this->getElement('/configs/config[@key="' . $row['key'] . '"]');
			$node->setAttribute('type', $row['type']);
			$node->setAttribute('default', $row['default_value']);
			$node->setAttribute('description', $row['description']);
		}
		


		//////////////  Handle the database and its information  \\\\\\\\\\\\\\\
		
		$tables = $this->getDBSchemaTableNames();
		
		// Purge the existing dbschema... it'll get rebuilt.
		$this->getRootDOM()->removeChild($this->getElement('/dbschema'));
		// And recreate it.
		$node = $this->getElement('/dbschema[@prefix="' . DB_PREFIX . '"]');
		
		// Run through each dbschema table and get the structure of that table.
		foreach($tables as $name){
			$node = $this->getElement('/dbschema/table[@name="' . $name . '"]');
			//$name = $node->getAttribute('name');
						
			// I need to get some information about this table first... such as collation and engine.
			$rs = DB::Execute('SHOW TABLE STATUS like \'' . $name . '\'');
			$node->setAttribute('engine', $rs->fields['Engine']);
			$node->setAttribute('charset', substr($rs->fields['Collation'], 0, strpos($rs->fields['Collation'], '_')));
			// @todo Should I include the Auto_increment field too?
			$nn = $this->getElementFrom('comment', $node);
			$nn->nodeValue = $rs->fields['Comment'];
			
			// Note, I don't need to use DB_PREFIX in this case because the table should already have that attached.
			
			
			// This is a slight variant of the standard one.
			$rs = DB::Execute('SHOW FULL COLUMNS FROM `' . $name . '`');
			foreach($rs as $row){
				$innernode = $this->getDOM()->createElement('column');
				$node->appendChild($innernode);
				foreach($row as $k => $v){
					if(is_numeric($k)) continue;
					// There are a few columns I don't need either.
					if($k == 'Privileges') continue;
					$nn = $this->getDOM()->createElement(strtolower($k));
					if(is_null($v)) $nn->setAttribute('xsi:nil', 'true');
					else $nn->nodeValue = $v;
					$innernode->appendChild($nn);
					unset($nn);
				}
				unset($innernode);
			}
			
			// And this will handle the indexes.
			$indexes = array(); // I need to keep track of the index by name.
			$rs = DB::Execute('SHOW INDEXES FROM `' . $name . '`');
			foreach($rs as $row){
				//  Table | Non_unique | Key_name  | Seq_in_index | Column_name | Collation | Cardinality | Sub_part | Packed | Null | Index_type | Comment
				if(isset($indexes[$row['Key_name']])){
					// Just tack on the column to the existing index record.
					$innernode =& $indexes[$row['Key_name']];
					$nn = $this->getDOM()->createElement('column');
					$nn->nodeValue = $row['Column_name'];
					$innernode->appendChild($nn);
					unset($nn);
				}
				else{
					$innernode = $this->getDOM()->createElement('index');
					$node->appendChild($innernode);
					
					$nn = $this->getDOM()->createElement('name');
					$nn->nodeValue = $row['Key_name'];
					$innernode->appendChild($nn);
					unset($nn);
					
					$nn = $this->getDOM()->createElement('nonunique');
					$nn->nodeValue = $row['Non_unique'];
					$innernode->appendChild($nn);
					unset($nn);
					
					$nn = $this->getDOM()->createElement('column');
					$nn->nodeValue = $row['Column_name'];
					$innernode->appendChild($nn);
					unset($nn);
					
					$nn = $this->getDOM()->createElement('comment');
					$nn->nodeValue = $row['Comment'];
					$innernode->appendChild($nn);
					unset($nn);
					
					$indexes[$row['Key_name']] =& $innernode;
				}
				unset($innernode);
			}
			
			// This bit of code will produce compliant XML code to that of the mysql command with --xml option.
			// It may be useful in the DB system at some point.
			/*
			$rs = DB::Execute("SHOW FULL COLUMNS FROM `$name`");
			foreach($rs as $row){
				$innernode = $this->getDOM()->createElement('row');
				$node->appendChild($innernode);
				foreach($row as $k => $v){
					if(is_numeric($k)) continue;
					$nn = $this->getDOM()->createElement('field');
					$nn->setAttribute('name', $k);
					if(is_null($v)) $nn->setAttribute('xsi:nil', 'true');
					else $nn->nodeValue = $v;
					$innernode->appendChild($nn);
					unset($nn);
				}
				unset($innernode);
			}
			*/
		}
		
		// This needs to be the final step... write the XML doc back to the file.
		$XMLFilename = $this->getXMLFilename();
		//echo $this->asPrettyXML(); // DEBUG //
		file_put_contents($XMLFilename, $this->asPrettyXML());
		// and this would be a minimized version
		//file_put_contents(substr($XMLFilename, 0, -4) . '-min.xml', $this->asMinifiedXML());
	}
	
	
	
	public function loadFiles(){
		// Load any autoload files for this component.
		if($this->hasLibrary()){
			foreach($this->getElementByTagName('library')->getElementsByTagName('file') as $f){
				$type = strtolower(@$f->getAttribute('type'));
				//var_dump($this->_name, $f->getAttribute('filename'), $type);
				if($type == 'autoload') require_once($this->getBaseDir() . $f->getAttribute('filename'));
			}
		}
		
		
		// Register any events that may be present.
		foreach($this->getElementsByTagName('hook') as $h){
			$event = $h->getAttribute('event');
			$call = $h->getAttribute('call');
			$type = @$h->getAttribute('type');
			HookHandler::AttachToHook($event, $call, $type);
		}
		
		
		// This component may have special form elements registered.  Check!
		foreach($this->getElements('/forms/formelement') as $node){
			Form::$Mappings[$node->getAttribute('name')] = $node->getAttribute('class');
		}
		
		
		return true;
	}
	
	public function getLibraryList(){
		// Get an array of library -> version
		$libs = array();
		
		if($this->hasLibrary()){
			$libs[$this->_name] = $this->_versionDB;
		}
		
		
		foreach($this->getElements('//provides') as $p){
			if(strtolower($p->getAttribute('type')) == 'library'){
				$v = @$p->getAttribute('version');
				if(!$v) $v = $this->_versionDB;
				$libs[$p->getAttribute('name')] = $v;
			}
		}
		
		return $libs;
	}
	
	public function getClassList(){
		// Get an array of class -> file (fully resolved)
		$classes = array();
		
		if($this->hasLibrary()){
			foreach($this->getElementByTagName('library')->getElementsByTagName('file') as $f){
				$filename = $this->getBaseDir() . $f->getAttribute('filename');
				foreach($f->getElementsByTagName('provides') as $p){
					if(strtolower($p->getAttribute('type')) == 'class') $classes[$p->getAttribute('name')] = $filename;
					// also allow interfaces to be returned.
					if(strtolower($p->getAttribute('type')) == 'interface') $classes[$p->getAttribute('name')] = $filename;
				}
			}
		}
		
		if($this->hasModule()){
			foreach($this->getElementByTagName('module')->getElementsByTagName('file') as $f){
				$filename = $this->getBaseDir() . $f->getAttribute('filename');
				foreach($f->getElementsByTagName('provides') as $p){
					switch(strtolower($p->getAttribute('type'))){
						case 'class':
						case 'controller':
						case 'widgetcontroller':
							$classes[$p->getAttribute('name')] = $filename;
							break;
					}
				}
			}
		}
		
		return $classes;
	}
	
	public function getViewClassList(){
		$classes = array();
		if($this->hasModule()){
			foreach($this->getElementByTagName('module')->getElementsByTagName('file') as $f){
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
			foreach($this->getElementByTagName('view')->getElementsByTagName('tpl') as $t){
				$filename = $this->getBaseDir() . $t->getAttribute('filename');
				$name = $t->getAttribute('name');
				$views[$name] = $filename;
			}
		}
		return $views;
	}
	
	public function getScriptLibraryList(){
		$libs = array();
		if($this->hasLibrary()){
			foreach($this->getElementByTagName('library')->getElementsByTagName('scriptlibrary') as $s){
				$libs[$s->getAttribute('name')] = $s->getAttribute('call');
			}
		}
		return $libs;
	}

	/* Why would there be more than 1 searchdir?
	public function getViewSearchDirs(){
		$dirs = array();
		if($this->hasView()){
			foreach($this->getElementByTagName('view')->getElementsByTagName('searchdir') as $t){
				$dirs[] = $this->getBaseDir() . $t->getAttribute('dir') . '/';
			}
		}
		return $dirs;
	}
	*/

	public function getViewSearchDir(){
		if($this->hasView()){
			// Using the searchdir attribute is the preferred method.
			$att = @$this->getElement('/view')->getAttribute('searchdir');
			if($att){
				return $this->getBaseDir() . $att . '/';
			}
			elseif(($att = $this->getElements('/view/searchdir')->item(0))){
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
		$d = $this->getBaseDir() . 'assets';
		
		if(is_dir($d)) return $d;
		else return null;
	}
	
	public function getIncludePaths(){
		$dirs = array();
		if($this->hasLibrary()){
			foreach($this->getElementByTagName('library')->getElementsByTagName('includepath') as $t){
				$dir = $t->getAttribute('dir');
				if($dir == '.') $dirs[] = $this->getBaseDir();
				else $dirs[] = $this->getBaseDir() . $t->getAttribute('dir') . '/';
			}
		}
		return $dirs;
	}
	
	
	
	/**
	 * Get an array of the table names in the DB schema.
	 * @return array
	 */
	public function getDBSchemaTableNames(){
		$ret = array();
		foreach($this->getElement('dbschema')->getElementsByTagName('table') as $table){
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
		$this->getRootDOM()->removeChild($this->getElement('/dbschema'));
		// And recreate it.
		$node = $this->getElement('/dbschema[@prefix="' . DB_PREFIX . '"]');
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
					if(!ComponentHandler::IsLibraryAvailable($r['name'], $r['version'], $r['operation'])){
						$this->error = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing library ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'jslibrary':
					if(!ComponentHandler::IsJSLibraryAvailable($r['name'], $r['version'], $r['operation'])){
						$this->error = $this->error | Component::ERROR_MISSINGDEPENDENCY;
						$this->errstrs[] = 'Requires missing JSlibrary ' . $r['name'] . ' ' . $r['version'];
					}
					break;
				case 'component':
					if(!ComponentHandler::IsComponentAvailable($r['name'], $r['version'], $r['operation'])){
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
			if(ComponentHandler::IsClassAvailable($c)){
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
		foreach($this->getRootDOM()->getElementsByTagName('jslibrary') as $node){
			$lib = new JSLibrary();
			$lib->name = $node->getAttribute('name');
			// The version doesn't have to be set... it can be derived from the component version.
			$lib->version = (($v = @$node->getAttribute('version'))? $v : $this->getRootDOM()->getAttribute('version'));
			$lib->baseDirectory = ROOT_PDIR . 'components/' . $this->getName() . '/';
			$lib->DOMNode = $node;
			$ret[$lib->name] = $lib;
		}
		return $ret;
	}
	
	
	
	public function hasLibrary(){
		return ($this->getRootDOM()->getElementsByTagName('library')->length)? true : false;
	}
	
	public function hasJSLibrary(){
		return ($this->getRootDOM()->getElementsByTagName('jslibrary')->length)? true : false;
	}
	
	public function hasModule(){
		return ($this->getRootDOM()->getElementsByTagName('module')->length)? true : false;
	}
	
	public function hasView(){
		return ($this->getRootDOM()->getElementsByTagName('view')->length)? true : false;
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
		if($this->getRootDOM()->getElementsByTagName('install')->item(0)){
			InstallTask::ParseNode(
				$this->getRootDOM()->getElementsByTagName('install')->item(0), 
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
		if(class_exists('ComponentHandler')){
			$ch = ComponentHandler::Singleton();
			$ch->_registerComponent($this);
		}
		
		return true;
	}
	
	public function reinstall(){
		// @todo I need actual error checking here.
		if(!$this->isInstalled()) return false;
		
		$this->_parseDBSchema();
		
		$this->_parseConfigs();
		
		$this->_parsePages();
		
		$this->_installAssets();
		
		// @todo What else should be done?
		
		return true;
	}
	
	public function upgrade(){
		if(!$this->isInstalled()) return false;
		
		$canBeUpgraded = true;
		while($canBeUpgraded){
			// Set as false to begin with, (will be set back to true if an upgrade is ran).
			$canBeUpgraded = false;
			foreach($this->getRootDOM()->getElementsByTagName('upgrade') as $u){
				// look for a valid upgrade path.
				if($this->_versionDB == @$u->getAttribute('from')){
					// w00t, found one...
					$canBeUpgraded = true;
					
					$result = InstallTask::ParseNode($u, $this->getBaseDir());
					if(!$result){
						if(DEVELOPMENT_MODE){
							trigger_error('Upgrade of Component ' . $this->_name . ' failed.', E_USER_NOTICE);
						}
						return;
					}
					
					$this->_versionDB = @$u->getAttribute('to');
					DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array($this->_name, $this->_versionDB));
				}
			}
		}
		
		$this->_parseDBSchema();
		
		$this->_parseConfigs();
		
		$this->_parsePages();
		
		$this->_installAssets();
	}
	
	/**
	 * Copy in all the assets for this component into the assets location.
	 */
	private function _installAssets(){
		foreach($this->getElements('/assets/file') as $node){
			$b = $this->getBaseDir();
			$f = new File($b . $node->getAttribute('filename'));
			$nf = new Asset($node->getAttribute('filename'));
			
			$f->copyTo($nf, true);
		}
		
		// Make sure the asset cache is purged!
		Core::Cache()->delete('asset-resolveurl');
	}
	
	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 */
	private function _parseConfigs(){
		// I need to get the schema definitions first.
		$node = $this->getElement('configs');
		//$prefix = $node->getAttribute('prefix');
		
		// Now, get every table under this node.
		foreach($node->getElementsByTagName('config') as $confignode){
			$m = new ConfigModel($confignode->getAttribute('key'));
			$m->set('type', $confignode->getAttribute('type'));
			$m->set('default_value', $confignode->getAttribute('default'));
			if(!$m->get('value')) $m->set('value', $confignode->getAttribute('default'));
			$m->set('description', $confignode->getAttribute('description'));
			$m->save();
		}
	}
	
	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 */
	private function _parsePages(){
		// I need to get the schema definitions first.
		$node = $this->getElement('pages');
		//$prefix = $node->getAttribute('prefix');
		
		// Now, get every table under this node.
		foreach($node->getElementsByTagName('page') as $subnode){
			// <config key="/core/theme" type="string" default="default" description="The theme of the site"/>
			// Insert/Update the defaults for an entry in the database.
			$m = new PageModel($subnode->getAttribute('baseurl'));
			// Do not "update" value, keep whatever the user set previously.
			if(!$m->get('rewriteurl')) $m->set('rewriteurl', $subnode->getAttribute('baseurl'));
			// Do not "update" value, keep whatever the user set previously.
			if(!$m->get('title')) $m->set('title', $subnode->getAttribute('title'));
			// Do not "update" value, keep whatever the user set previously.
			if(!$m->get('access')) $m->set('access', $subnode->getAttribute('access'));
			$m->set('widget', $subnode->getAttribute('widget'));
			$m->set('admin', $subnode->getAttribute('admin'));
			$m->save();
		}
	}
	
	
	/**
	 * Internal function to parse and handle the DBSchema in the component.xml file.
	 * This is used for installations and upgrades.
	 */
	private function _parseDBSchema(){
		// I need to get the schema definitions first.
		$node = $this->getElement('dbschema');
		$prefix = $node->getAttribute('prefix');
		
		
		// Get the table structure as it exists in the database first, this will be the comparison point.
		$classes = $this->getClassList();
		foreach($classes as $k => $v){
			if($k == 'Model' || strpos($k, 'Model') !== strlen($k) - 5) unset($classes[$k]);
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
	} // private function _parseDBSchema()
	
	
}
