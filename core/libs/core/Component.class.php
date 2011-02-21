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
	const ERROR_NOERROR = 0;
	const ERROR_INVALID = 1;
	const ERROR_WRONGEXECMODE = 2;
	const ERROR_MISSINGDEPENDENCY = 4;
	
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
			$q = DB::Execute("SELECT `version` FROM `" . DB_PREFIX . "component` WHERE `name` = ?", $this->_name);
			if(!$q) return false;
			
			if($q->numRows() > 0){
				$this->_versionDB = $q->fields['version'];
			}
			else{
				$this->_versionDB = false;
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
			
			foreach($this->getElementByTagName('library')->getElementsByTagName('file') as $f){
				foreach($f->getElementsByTagName('provides') as $p){
					if(strtolower($p->getAttribute('type')) == 'library'){
						$v = @$p->getAttribute('version');
						if(!$v) $v = $this->_versionDB;
						$libs[$p->getAttribute('name')] = $v;
					}
				}
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
			if(!$att){
				// Try the 'searchdir' element instead.
				$att = $this->getElements('/view/searchdir');
				if($att) return $this->getBaseDir() .$att->item(0)->getAttribute('dir') . '/';
			}
			else{
				return $this->getBaseDir() . $att . '/';
			}
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
		if($this->getRootDOM()->getElementsByTagName('install')->item(0)){
			InstallTask::ParseNode(
				$this->getRootDOM()->getElementsByTagName('install')->item(0), 
				$this->getBaseDir()
			);
		}
		
		// Yay, it should be installed now.	Update the version in the database.
		DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array($this->_name, $this->_version));
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
		$c = Cache::Singleton('asset-resolveurl');
		$c->delete();
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
			// <config key="/core/theme" type="string" default="default" description="The theme of the site"/>
			// Insert/Update the defaults for an entry in the database.
			$s = new SQLBuilderInsertUpdate();
			$s->table(DB_PREFIX . 'config');
			$s->set('key', $confignode->getAttribute('key'));
			$s->set('type', $confignode->getAttribute('type'), true);
			$s->set('default_value', $confignode->getAttribute('default'), true);
			$s->set('value', $confignode->getAttribute('default')); // Do not "update" value, keep whatever the user set previously.
			$s->set('description', $confignode->getAttribute('description'), true);
			
			//echo $s->query() . '<br/>';
			
			$s->execute();
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
			$s = new SQLBuilderInsertUpdate();
			$s->table(DB_PREFIX . 'page');
			$s->set('baseurl', $subnode->getAttribute('baseurl'));
			$s->set('rewriteurl', $subnode->getAttribute('baseurl')); // Do not "update" value, keep whatever the user set previously.
			$s->set('title', $subnode->getAttribute('title')); // Do not "update" value, keep whatever the user set previously.
			$s->set('access', $subnode->getAttribute('access')); // Do not "update" value, keep whatever the user set previously.
			$s->set('widget', $subnode->getAttribute('widget'), true);
			$s->set('admin', $subnode->getAttribute('admin'), true);
			$s->set('created', 'UNIX_TIMESTAMP()');
			$s->set('updated', 'UNIX_TIMESTAMP()', true);
			
			//echo $s->query() . '<br/>';
			
			$s->execute();
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
		
		
		// Now, get every table under this node.
		foreach($node->getElementsByTagName('table') as $tblnode){
			$table = DB_PREFIX . preg_replace('/^' . $prefix . '/', '', $tblnode->getAttribute('name'));
			// Check if the table exists to begin with.
			if(!DB::TableExists($table)){
				// Table doesn't exist, just do a simple create
				$q = 'CREATE TABLE `' . $table . '` ';
				$directives = array();
				foreach($this->getElementsFrom('column', $tblnode) as $colnode){
					$coldef = $this->elementToArray($colnode);
					$d = '`' . $coldef['field'] . '` ';
					$d .= $coldef['type'] . ' ';
					if($coldef['collation']) $d .= 'COLLATE ' . $coldef['collation'] . ' ';
					$d .= (($coldef['null'] == 'NO')? 'NOT NULL' : 'NULL') . ' ';
					if($coldef['default']) $d .= 'DEFAULT ' . "'" . $coldef['default'] . "'";
					if($coldef['extra'] == 'auto_increment') $d .= 'AUTO_INCREMENT ';
					if($coldef['comment']) $d .= 'COMMENT \'' . $coldef['comment'] . '\' ';
					$directives[] = $d;
				}
				
				foreach($this->getElementsFrom('index', $tblnode) as $idxnode){
					$idxdef = $this->elementToArray($idxnode);
					$d = '';
					if($idxdef['name'] == 'PRIMARY') $d .= 'PRIMARY KEY ';
					elseif($idxdef['nonunique'] == 0) $d .= 'UNIQUE KEY `' . $idxdef['name'] . '` ';
					else $d .= 'KEY `' . $idxdef['name'] . '` ';
					
					if(is_array($idxdef['column'])){
						$d .= '(`' . implode('`, `', $idxdef['column']) . '`) ';
					}
					else{
						$d .= '(`' . $idxdef['column'] . '`) ';
					}
					if($idxdef['comment']) $d .= 'COMMENT \'' . $idxdef['comment'] . '\' ';
					$directives[] = $d;
				}
				
				$q .= '( ' . implode(', ', $directives) . ' ) ';
				$q .= 'ENGINE=' . $tblnode->getAttribute('engine') . ' ';
				$q .= 'DEFAULT CHARSET=' . $tblnode->getAttribute('charset') . ' ';
				if($tblnode->getAttribute('comment')) $q .= 'COMMENT=\'' . $tblnode->getAttribute('comment') . '\' ';
				// @todo should AUTO_INCREMENT be available here?
				
				DB::Execute($q);
			} // if(!DB::TableExists($table))
			else{
				// Table does exist... I need to do a merge of the data schemas.
				// Create a temp table to do the operations on.
				DB::Execute('CREATE TEMPORARY TABLE _tmptable LIKE ' . $table);
				DB::Execute('INSERT INTO _tmptable SELECT * FROM ' . $table);
				
				// My simple counter.  Helps keep track of column order.
				$x = 0;
				// This will contain the current table schema of the tmptable.
				// It will get reloaded after any change.
				$schema = $this->_describeTableSchema('_tmptable');
				
				foreach($this->getElementsFrom('column', $tblnode) as $colnode){
					$coldef = $this->elementToArray($colnode);
					
					/*
					// Get the column schema from the XML node.
					$coldef = array();
					foreach($colnode->getElementsByTagName('field') as $f){
						if($f->getAttribute('xsi:nil')) $coldef[$f->getAttribute('name')] = null;
						else $coldef[$f->getAttribute('name')] = trim($f->nodeValue);
					}
					*/
					// coldef should now contain:
					// array(
					//   'field' => 'name_of_field',
					//   'type' => 'type definition, ie: int(11), varchar(32), etc',
					//   'null' => 'NO|YES',
					//   'key' => 'PRI|MUL|UNI|[blank]'
					//   'default' => default value
					//   'extra' => 'auto_increment|[blank]',
					//   'collation' => 'some collation type',
					//   'comment' => 'some comment',
					// );
					
					// Check that the current column is in the same location as in the database.
					if($schema['ord'][$x] != $coldef['field']){
						// Is it even present?
						if(isset($schema['def'][$coldef['field']])){
							// w00t, move it to this position.
							// ALTER TABLE `test` MODIFY COLUMN `fieldfoo` mediumint AFTER `something`
							$q = 'ALERT TABLE _tmptable MODIFY COLUMN `' . $coldef['field'] . '` ' . $coldef['type'] . ' ';
							$q .= ($x == 0)? 'FIRST' : 'AFTER ' . $schema['ord'][$x-1];
							DB::Execute($q);
							
							// Moving the column will change the definition... reload that.
							$schema = $this->_describeTableSchema('_tmptable');
						}
						// No? Ok, create it.
						else{
							// ALTER TABLE `test` ADD `newfield` TEXT NOT NULL AFTER `something` 
							$q = 'ALTER TABLE _tmptable ADD `' . $coldef['field'] . '` ' . $coldef['type'] . ' ';
							$q .= (($coldef['null'] == 'NO')? 'NOT NULL' : 'NULL') . ' ';
							$q .= ($x == 0)? 'FIRST' : 'AFTER ' . $schema['ord'][$x-1];
							DB::Execute($q);
							
							// Adding the column will change the definition... reload that.
							$schema = $this->_describeTableSchema('_tmptable');
						}
					}
					
					// coldef should now contain:
					// array(
					//   'field' => 'name_of_field',
					//   'type' => 'type definition, ie: int(11), varchar(32), etc',
					//   'null' => 'NO|YES',
					//   'key' => 'PRI|MUL|UNI|[blank]'
					//   'default' => default value
					//   'extra' => 'auto_increment|[blank]',
					//   'collation' => 'some collation type',
					//   'comment' => 'some comment',
					// );
					
					// Now the column should exist and be in the correct location.  Check its structure.
					// ALTER TABLE `test` CHANGE `newfield` `newfield` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL 
					// Check its AI and primary states first.
					if(
						$coldef['extra'] == 'auto_increment' && $coldef['key'] == 'PRI' && 
						(!isset($schema['def'][$coldef['field']]) || ($schema['def'][$coldef['field']]['extra'] == '' && $schema['def'][$coldef['field']]['key'] == ''))
					){
						// An AI value was added to the table.  I need to add that column as the primary key first, then
						// tack on the AI property.
						// ALTER TABLE `test` ADD PRIMARY KEY(`id`)
						$q = 'ALTER TABLE _tmptable ADD PRIMARY KEY (`' . $coldef['field'] . '`)';
						DB::Execute($q);
						$q = 'ALTER TABLE _tmptable CHANGE `' . $coldef['field'] . '` `' . $coldef['field'] . '` ' . $coldef['type'] . ' ';
						$q .= (($coldef['null'] == 'NO')? 'NOT NULL' : 'NULL') . ' ';
						// Durka durka, AI columns don't have a default value.
						//$q .= 'DEFAULT ' . (($coldef['default'] == '')? (($coldef['null'] == 'NO')? "''" : 'NULL') : "'" . $coldef['Default'] . "'") . ' ';
						$q .= 'AUTO_INCREMENT';
						DB::Execute($q);
						
						// And reload the schema.
						$schema = $this->_describeTableSchema('_tmptable');
					}
					
					// Now, check everything else.
					if(
						$coldef['type'] != $schema['def'][$coldef['field']]['type'] ||
						$coldef['null'] != $schema['def'][$coldef['field']]['null'] ||
						$coldef['default'] != $schema['def'][$coldef['field']]['default'] ||
						$coldef['collation'] != $schema['def'][$coldef['field']]['collation'] || 
						$coldef['comment'] != $schema['def'][$coldef['field']]['comment']
					){
						$q = 'ALTER TABLE _tmptable CHANGE `' . $coldef['field'] . '` `' . $coldef['field'] . '` ';
						$q .= $coldef['type'] . ' ';
						if($coldef['collation']) $q .= 'COLLATE ' . $coldef['collation'] . ' ';
						$q .= (($coldef['null'] == 'NO')? 'NOT NULL' : 'NULL') . ' ';
						$q .= 'DEFAULT ' . (($coldef['default'] == '')? (($coldef['null'] == 'NO')? "''" : 'NULL') : "'" . $coldef['default'] . "'") . ' ';
						if($coldef['comment']) $q .= 'COMMENT \'' . $coldef['comment'] . '\' ';
						DB::Execute($q);
						
						// And reload the schema.
						$schema = $this->_describeTableSchema('_tmptable');
					}
					
					$x++;
				} // foreach($this->getElementFrom('column', $tblnode, false) as $colnode)
				
				/*
			<index>
				<name>PRIMARY</name>
				<nonunique>0</nonunique>
				<column>id</column>
				<comment></comment>
			</index>
			<index>
				<name>data</name>
				<nonunique>0</nonunique>
				<column>data</column>
				<comment></comment>
			</index>
			<index>
				<name>something</name>
				<nonunique>1</nonunique>
				<column>something</column>
				<comment></comment>
				<column>data</column>
			</index> */
			
				// The columns should be done; onto the indexes.
				$schema = $this->_describeTableIndexes('_tmptable');
				foreach($this->getElementsFrom('index', $tblnode) as $idxnode){
					$idxdef = $this->elementToArray($idxnode);
					// Ensure that idxdef['column'] is an array if it's not.
					if(!is_array($idxdef['column'])) $idxdef['column'] = array($idxdef['column']);
					// @todo do all the indexes here.
					if($idxdef['name'] == 'PRIMARY'){
						$name = 'PRIMARY KEY';
					}
					elseif($idxdef['nonunique'] == 0){
						$name = 'UNIQUE `' . $idxdef['name'] . '`';
					}
					else{
						$name = 'INDEX `' . $idxdef['name'] . '`';
					}
					
					if(!isset($schema[$idxdef['name']])){
						DB::Execute('ALTER TABLE `_tmptable` ADD ' . $name . ' (`' . implode('`, `', $idxdef['column']) . '`)');
						$schema = $this->_describeTableIndexes('_tmptable');
					}
					// There can only be 1!
					elseif(sizeof(array_diff($idxdef['column'], $schema[$idxdef['name']]['columns']))){
						DB::Execute('ALTER TABLE `_tmptable` DROP ' . $name . ', ADD ' . $name . ' (`' . implode('`, `', $idxdef['column']) . '`)');
						$schema = $this->_describeTableIndexes('_tmptable');
					}
				} // foreach($this->getElementFrom('index', $tblnode, false) as $idxnode)
				
				
				// All operations should be completed now; move the temp table back to the original one.
				DB::Execute('DROP TABLE `' . $table . '`');
				DB::Execute('CREATE TABLE `' . $table . '` LIKE _tmptable');
				DB::Execute('INSERT INTO `' . $table . '` SELECT * FROM _tmptable');
				
				// Drop the table so it's ready for the next table.
				DB::Execute('DROP TABLE _tmptable');
			} // else if(!DB::TableExists($table))
		} // foreach($node->getElementsByTagName('table') as $tblnode)
	} // private function _parseDBSchema()
	
	private function _describeTableSchema($table){
		$rs = DB::Execute('SHOW FULL COLUMNS FROM `' . $table . '`');
		$tabledef = array();
		$tableord = array();
		foreach($rs as $row){
			$tabledef[$row['Field']] = array();
			foreach($row as $k => $v){
				$tabledef[$row['Field']][strtolower($k)] = $v;
			}
			$tableord[] = $row['Field'];
		}
		return array('def' => $tabledef, 'ord' => $tableord);
	}
	
	private function _describeTableIndexes($table){
		$rs = DB::Execute('SHOW INDEXES FROM `' . $table . '`');
		$def = array();
		foreach($rs as $row){
			// Non_unique | Key_name | Column_name | Comment
			if(isset($def[$row['Key_name']])){
				// Add a column.
				$def[$row['Key_name']]['columns'][] = $row['Column_name'];
			}
			else{
				$def[$row['Key_name']] = array(
					'name' => $row['Key_name'],
					'nonunique' => $row['Non_unique'],
					'comment' => $row['Comment'],
					'columns' => array($row['Column_name']),
				);
			}
		}
		return $def;
	}
}
