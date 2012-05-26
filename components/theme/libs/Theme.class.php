<?php
/**
 * 
 * 
 * @package Theme
 * @since 2011.06
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

/**
 * Theme object.
 * 
 * Themes consist just of the template files and corresponding assets.
 */
class Theme{

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
	 * Version of the component, as per the database (installed version).
	 * 
	 * @var string
	 */
	private $_versionDB = false;
	
	/**
	 * This object only needs to be loaded once
	 * @var boolean
	 */
	private $_loaded = false;
	
	
	public function __construct($name = null){
		$this->_xmlloader = new XMLLoader();
		$this->_xmlloader->setRootName('theme');
		
		$filename = ROOT_PDIR . 'themes/' . $name . '/theme.xml';
		
		if(!$this->_xmlloader->loadFromFile($filename)){
			throw new Exception('Parsing of XML Metafile [' . $filename . '] failed, not valid XML.');
		}
	}
	
	public function load(){
		if($this->_loaded) return;
		
		$this->_name = $this->_xmlloader->getRootDOM()->getAttribute('name');
		$this->_version = $this->_xmlloader->getRootDOM()->getAttribute("version");
		
		// Load the database information, if there is any.
		$dat = ComponentFactory::_LookupComponentData('theme/' . $this->_name);
		if(!$dat) return;
		
		$this->_versionDB = $dat['version'];
		$this->_enabled = ($dat['enabled']) ? true : false;
		$this->_loaded = true;
	}
	
	/**
	 * Get all the templates registered for this theme.
	 * Each template can be a different site skin, ie: 2-column, 3-column, etc.
	 * 
	 * @return array 
	 */
	public function getTemplates(){
		$out = array();
		$default = null;
		// If this theme is currently selected, check the default template too.
		if($this->getName() == ConfigHandler::Get('/theme/selected')) $default = ConfigHandler::Get('/theme/default_template');
		
		foreach($this->_xmlloader->getElements('//templates/file') as $f){
			$out[] = array(
				'filename' => $this->getBaseDir() . $f->getAttribute('filename'),
				'file' => $f->getAttribute('filename'),
				'title' => $f->getAttribute('title'),
				'default' => ($default == $f->getAttribute('filename'))
			);
		}
		
		return $out;
	}
	
	/**
	 * Get this theme's name
	 * 
	 * @return string
	 */
	public function getName(){
		return $this->_name;
	}
	
	/**
	 * Get the base directory of this component
	 * 
	 * Generally /home/foo/public_html/themes/componentname/
	 * 
	 * @return string
	 */
	public function getBaseDir($prefix = ROOT_PDIR){
		return $prefix . 'themes/' . $this->_name . '/';
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
		//if($this->_xmlloader->getElement('/otherfiles', false)){
		//	$this->getRootDOM()->removeChild($this->_xmlloader->getElement('/otherfiles'));
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
			
			if($file->inDirectory($assetd)){
				// It's an asset!
				$el = $this->_xmlloader->getElement('/assets/file[@filename="' . $fname . '"]');
			}
			elseif($hasview && $file->inDirectory($viewd)){
				$el = $this->_xmlloader->getElement('/view/file[@filename="' . $fname . '"]');
			}
			else{
				// Only add it if the file doesn't exist already.
				$el = $this->_xmlloader->getElement('//library/file[@filename="' . $fname . '"]|//module/file[@filename="' . $fname . '"]|//view/file[@filename="' . $fname . '"]', false);
				// Scan through this file and file any classes that are provided.
				if(preg_match('/\.php$/i', $fname)){
					$fconts = file_get_contents($file->getFilename());
					
					if($el){
						$getnames = ($el->parentNode->nodeName == 'library' || $el->parentNode->nodeName == 'module');
					}
					else{
						// Does this file contain something that extends Controller?
						if(preg_match('/^(abstract ){0,1}class[ ]*[a-z0-9_\-]*[ ]*extends controller/im', $fconts)){
							$el = $this->_xmlloader->getElement('/module/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						// WidgetControllers also go in the module section.
						elseif(preg_match('/^(abstract ){0,1}class[ ]*[a-z0-9_\-]*[ ]*extends widgetcontroller/im', $fconts)){
							$el = $this->_xmlloader->getElement('/module/file[@filename="' . $fname . '"]');
							$getnames = true;
						}
						elseif(preg_match('/^(abstract |final ){0,1}class[ ]*[a-z0-9_\-]*/im', $fconts)){
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
						
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*widgetcontroller/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							$this->_xmlloader->getElementFrom('provides[@type="widgetcontroller"][@name="' . $foundclass . '"]', $el);
							// This is needed to tell the rest of the save logic to ignore the save for classes.
							$viewclasses[] = $foundclass;
						}
						
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							if(in_array($foundclass, $viewclasses)) continue;
							$this->_xmlloader->getElementFrom('provides[@type="class"][@name="' . $foundclass . '"]', $el);
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
		
		
		// This needs to be the final step... write the XML doc back to the file.
		$XMLFilename = $this->getXMLFilename();
		//echo $this->asPrettyXML(); // DEBUG //
		file_put_contents($XMLFilename, $this->asPrettyXML());
		// and this would be a minimized version
		//file_put_contents(substr($XMLFilename, 0, -4) . '-min.xml', $this->asMinifiedXML());
	}
	
	
	public function getViewSearchDir(){
		return $this->getBaseDir();
	}
	
	public function getAssetDir(){
		$d = $this->getBaseDir() . 'assets';
		
		// If there is no "asset" directory, just return the base directory.
		if(is_dir($d)) return $d;
		else return rtrim($this->getBaseDir(), '/');
	}
	
	public function isLoadable(){
		return true; // Themes really can't quite be *not* loadable.
	}
	
	
	public function isInstalled(){
		return ($this->_versionDB === false)? false : true;
	}
	
	public function needsUpdated(){
		return ($this->_versionDB != $this->_version);
	}
	
		
	public function hasLibrary(){
		return false; // Themes don't have libraries.
	}
	
	public function hasJSLibrary(){
		return false; // Themes don't have JS libraries, (am I even supporting this anymore???)
	}
	
	public function hasModule(){
		return false; // Themes don't have modules.
	}
	
	public function hasView(){
		return true; // This is the only thing a theme is in fact...
	}
	
	
	/**
	 * Install this theme
	 * 
	 * Returns false if nothing changed, else will return an array containing all changes.
	 * 
	 * @return false | array
	 * @throws InstallerException
	 */
	public function install(){
		// @todo I need actual error checking here.
		if($this->isInstalled()) return false;
		
		return $this->_performInstall();
	}
	
	/**
	 * Reinstall this theme.
	 * 
	 * Returns false if nothing changed, else will return an array containing all changes.
	 * 
	 * @return false | array
	 * @throws InstallerException
	 */
	public function reinstall(){
		// @todo I need actual error checking here.
		if(!$this->isInstalled()) return false;
		
		return $this->_performInstall();
	}
	
	/**
	 * Upgrade the theme and reinstall all components.
	 * 
	 * This is actually an alias for reinstall, as there is no difference!
	 * Returns false if nothing changed, else will return an array containing all changes.
	 * 
	 * @return false | array
	 * @throws InstallerException
	 */
	public function upgrade(){
		if(!$this->isInstalled()) return false;
		
		return $this->_performInstall();
	}
	
	/**
	 * Because install, upgrade and remove all are actually the exact same logic for themes.
	 * 
	 * Returns false if nothing changed, else will return an array containing all changes.
	 * 
	 * @return false | array
	 * @throws InstallerException
	 */
	private function _performInstall(){
		$changed = array();
		
		$change = $this->_installAssets();
		if($change !== false) $changed = array_merge($changed, $change);
		
		$change = $this->_parseConfigs();
		if($change !== false) $changed = array_merge($changed, $change);
		
		// Make sure the version is correct in the database.
		$c = new ComponentModel('theme/' . $this->_name);
		$c->set('version', $this->_version);
		$c->save();
		
		return (sizeof($changed)) ? $changed : false;
	}
	
	/**
	 * Internal function to parse and handle the configs in the component.xml file.
	 * This is used for installations and upgrades.
	 * 
	 * Returns false if nothing changed, else will return the configuration options changed.
	 * 
	 * @return false | array
	 * @throws InstallerException
	 */
	private function _parseConfigs(){
		$changes = array();
		
		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('configs');
		//$prefix = $node->getAttribute('prefix');
		
		// Now, get every table under this node.
		foreach($node->getElementsByTagName('config') as $confignode){
			$m = new ConfigModel($confignode->getAttribute('key'));
			$m->set('type', $confignode->getAttribute('type'));
			$m->set('default_value', $confignode->getAttribute('default'));
			// Themes overwrite the settings regardless.
			$m->set('value', $confignode->getAttribute('default'));
			$m->set('description', $confignode->getAttribute('description'));
			if($m->save()) $changes[] = 'Set configuration [' . $m->get('key') . '] to [' . $m->get('value') . ']';
		}
		
		// Are there changes?
		return (sizeof($changes)) ? $changes : false;
	} // private function _parseConfigs
	
	/**
	 * Copy in all the assets for this component into the assets location.
	 * 
	 * Returns false if nothing changed, else will return an array of all the changes that occured.
	 * 
	 * @return false | array
	 * @throws InstallerException
	 */
	private function _installAssets(){
		$assetbase = ConfigHandler::Get('/core/filestore/assetdir');
		$coretheme = ConfigHandler::Get('/theme/selected');
		$theme = $this->getName();
		$changes = array();
		
		foreach($this->_xmlloader->getElements('/assets/file') as $node){
			$b = $this->getBaseDir();
			// The base filename with the directory.
			$filename = $node->getAttribute('filename');
			// Local file is guaranteed to be a local file.
			$f = new File_local_backend($b . $filename);
			// The new theme asset will be installed into the same directory as its theme.
			// This differs from usual components because they just follow whatever theme is currently running.
			//$nf = Core::File($assetbase . $theme . '/' . $filename);
			$newfilename = 'assets' . substr($b . $node->getAttribute('filename'), strlen($this->getAssetDir()));
			
			$nf = Core::File($newfilename);

			// The new destination must be in the theme-specific directory, this is a
			// bit of a hack from the usual behaviour of the filestore system.
			// Since that's designed to return the default if the theme-specific doesn't exist.
			$nf->setFilename(str_replace($assetbase . $coretheme, $assetbase . $theme, $nf->getFilename()));

			/*
			if($theme != 'default' && strpos($nf->getFilename(), $assetbase . $theme) === false){
				// The only possible filename bases to be returned are the $coretheme and default.
				// so...
				if($theme == 'default'){
					$nf->setFilename(str_replace($assetbase . $coretheme, $assetbase . $theme, $nf->getFilename()));
				}
				else{
					$nf->setFilename(str_replace($assetbase . 'default', $assetbase . $theme, $nf->getFilename()));
				}
			}*/
			
			// Check if this file even needs updated. (this is primarily used for reporting reasons)
			if($nf->exists() && $nf->identicalTo($f)){
				//echo "Skipping file, it's identical.<br/>";
				continue;
			}
			// Otherwise if it exists, I want to be able to inform the user that it was replaced and not just installed.
			elseif($nf->exists()){
				$action = 'Replaced';
			}
			// Otherwise otherwise, it's a new file.
			else{
				$action = 'Installed';
			}
			
			try{
				$f->copyTo($nf, true);
			}
			catch(Exception $e){
				throw new InstallerException('Unable to copy [' . $f->getFilename() . '] to [' . $nf->getFilename() . ']');
			}
			
			$changes[] = $action . ' ' . $nf->getFilename();
		}
		
		if(!sizeof($changes)) return false;
		
		// Make sure the asset cache is purged!
		Core::Cache()->delete('asset-resolveurl');
		
		return $changes;
	}
}
