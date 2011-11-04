<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
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
class Theme extends InstallArchiveAPI{

	/**
	 * Version of the component, as per the database (installed version).
	 * 
	 * @var string
	 */
	private $_versionDB = false;
	
	
	public function __construct($name = null){
		$this->_name = $name;
		$this->_type = InstallArchiveAPI::TYPE_THEME;
		$this->load();
	}
	
	public function load(){
		parent::load();
		
		// Lookup this theme data in the component handler cache.
		$c = ComponentHandler::Singleton()->_dbcache;
		if(!isset($c['theme/' . $this->_name])) return false;
		
		$this->_versionDB = $c['theme/' . $this->_name]['version'];
		
		return true;
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
		
		foreach($this->getElements('//templates/file') as $f){
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
	 * Save this component metadata back to its XML file.
	 * Useful in packager scripts.
	 */
	public function save(){
		// Ensure there's a required namespace on the root node.
		$this->getRootDOM()->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");


		/////////////   Handle the file sections and their hashes \\\\\\\\\\\\\\

		// Purge the 'otherfiles' section.
		$this->removeElements('//otherfiles');
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
			
			if($file->inDirectory($assetd)){
				// It's an asset!
				$el = $this->getElement('/assets/file[@filename="' . $fname . '"]');
			}
			elseif($hasview && $file->inDirectory($viewd)){
				$el = $this->getElement('/view/file[@filename="' . $fname . '"]');
			}
			else{
				// Only add it if the file doesn't exist already.
				$el = $this->getElement('//library/file[@filename="' . $fname . '"]|//module/file[@filename="' . $fname . '"]|//view/file[@filename="' . $fname . '"]', false);
				// Scan through this file and file any classes that are provided.
				if(preg_match('/\.php$/i', $fname)){
					$fconts = file_get_contents($file->getFilename());
					
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
						
						preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
						foreach($ret[2] as $foundclass){
							if(in_array($foundclass, $viewclasses)) continue;
							$this->getElementFrom('provides[@type="class"][@name="' . $foundclass . '"]', $el);
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
		
		if(is_dir($d)) return $d;
		else return null;
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
	
	
	public function install(){
		// @todo I need actual error checking here.
		if($this->isInstalled()) return false;
		
		if(!$this->isLoadable()) return false;
		
		$this->_installAssets();
		
		$this->_parseConfigs();
		
		// Yay, it should be installed now.	Update the version in the database.
		$c = new ComponentModel('theme/' . $this->_name);
		$c->set('version', $this->_version);
		$c->save();
		
		$this->_versionDB = $this->_version;
		
		
		return true;
	}
	
	public function reinstall(){
		// @todo I need actual error checking here.
		if(!$this->isInstalled()) return false;
		
		$changed = false;
		
		if($this->_installAssets()) $changed = true;
		
		if($this->_parseConfigs()) $changed = true;
		
		// @todo What else should be done?
		
		return $changed;
	}
	
	public function upgrade(){
		// Cannot upgrade if not currently installed.
		if(!$this->isInstalled()) return false;
		
		// Cannot upgrade if no change required.
		if($this->_versionDB == $this->_version) return false;
			
		// Yay, it should be installed now.	Update the version in the database.
		DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array('theme/' . $this->_name, $this->_version));
		$this->_versionDB = $this->_version;
		
		$this->_installAssets();
		
		$this->_parseConfigs();
		
		return true;
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
			// Themes overwrite the settings regardless.
			$m->set('value', $confignode->getAttribute('default'));
			$m->set('description', $confignode->getAttribute('description'));
			$m->save();
		}
		
	} // private function _parseConfigs
	
	/**
	 * Copy in all the assets for this component into the assets location.
	 * 
	 * @return boolean True if something changed, false if nothing changed.
	 */
	private function _installAssets(){
		$assetbase = ConfigHandler::Get('/core/filestore/assetdir');
		$coretheme = ConfigHandler::Get('/theme/selected');
		$theme = $this->getName();
		$changed = false;
		foreach($this->getElements('/assets/file') as $node){
			$b = $this->getBaseDir();
			// Local file is guaranteed to be a local file.
			$f = new File_local_backend($b . $node->getAttribute('filename'));
			$nf = Core::File($node->getAttribute('filename'));
			// The new destination must be in the theme-specific directory, this is a 
			// bit of a hack from the usual behaviour of the filestore system.
			// Since that's designed to return the default if the theme-specific doesn't exist.
			if(strpos($nf->getFilename(), $assetbase . $theme) === false){
				// The only possible filename bases to be returned are the $coretheme and default.
				// so...
				if($theme == 'default'){
					$nf->setFilename(str_replace($assetbase . $coretheme, $assetbase . $theme, $nf->getFilename()));
				}
				else{
					$nf->setFilename(str_replace($assetbase . 'default', $assetbase . $theme, $nf->getFilename()));
				}
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
}
