<?php
/**
 *
 * @package Theme
 * @since 2011.06
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>,
 * and please contribute back to the community :)
 */

namespace Theme;
use Core\CLI\CLI;

/**
 * Theme object.
 *
 * Themes consist just of the template files and corresponding assets.
 */
class Theme{

	/**
	 * Underlying XML Loader object of the theme.xml file.
	 *
	 * Responsible for retrieving most information about this theme.
	 *
	 * @var \XMLLoader
	 */
	private $_xmlloader = null;

	/**
	 * The name of the theme.
	 * Has to be unique, (because the name is a directory in /theme)
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Version of the theme, (propagates to libraries and modules).
	 *
	 * @var string
	 */
	protected $_version;

	/**
	 * Description of this library.
	 * As set from the XML file.
	 *
	 * @var string
	 */
	protected $_description;

	/**
	 * Is this theme explictly disabled?
	 * (themes cannot be disabled...)
	 *
	 * @var boolean
	 */
	protected $_enabled = true;

	/**
	 * Version of the theme, as per the database (installed version).
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
		$this->_xmlloader = new \XMLLoader();
		$this->_xmlloader->setRootName('theme');

		$filename = ROOT_PDIR . 'themes/' . $name . '/theme.xml';

		if(!$this->_xmlloader->loadFromFile($filename)){
			throw new \Exception('Parsing of XML Metafile [' . $filename . '] failed, not valid XML.');
		}
	}

	public function load(){
		if($this->_loaded) return;

		$this->_name = $this->_xmlloader->getRootDOM()->getAttribute('name');
		$this->_version = $this->_xmlloader->getRootDOM()->getAttribute("version");

		// Load the database information, if there is any.
		$dat = \ComponentFactory::_LookupComponentData('theme/' . $this->_name);
		if(!$dat) return;

		$this->_versionDB = $dat['version'];
		$this->_enabled = ($dat['enabled']) ? true : false;

		if(
			DEVELOPMENT_MODE &&
			defined('AUTO_INSTALL_ASSETS') &&
			AUTO_INSTALL_ASSETS &&
			EXEC_MODE == 'WEB' &&
			CDN_TYPE == 'local' &&
			$this->getKeyName() == \ConfigHandler::Get('/theme/selected')
		){
			\Core\Utilities\Logger\write_debug('Auto-installing assets for theme [' . $this->getName() . ']');
			$this->_installAssets();
		}

		$this->_loaded = true;
	}

	/**
	 * Get all the templates registered for this theme.
	 * Each template can be a different site skin, ie: 2-column, 3-column, etc.
	 *
	 * @return array
	 */
	public function getSkins(){
		$out = [];
		$default = null;
		$admindefault = null;
		$currenttheme = false;

		// If this theme is currently selected, check the default template too.
		if($this->getKeyName() == \ConfigHandler::Get('/theme/selected')){
			$default = \ConfigHandler::Get('/theme/default_template');
			$admindefault = \ConfigHandler::Get('/theme/default_admin_template');

			// Defaults to the main public skin.
			if(!$admindefault) $admindefault = $default;

			$currenttheme = true;
		}

		foreach($this->_xmlloader->getElements('//skins/file') as $f){
			$basefilename = $f->getAttribute('filename');
			$filename = $this->getBaseDir() . 'skins/' . $basefilename;


			if($basefilename == 'blank.tpl'){
				continue;
			}

			$skin = \Core\Templates\Template::Factory($filename);

			$title = $f->getAttribute('title') ? $f->getAttribute('title') : $basefilename;

			// The return is expecting an array.
			$out[] = [
				'filename'        => $filename,
				'file'            => $basefilename,
				'title'           => $title,
				'default'         => ($default == $basefilename),
				'admindefault'    => ($admindefault == $basefilename),
				'has_stylesheets' => $skin->hasOptionalStylesheets(),
				'current_theme'   => $currenttheme,
			];
		}

		return $out;
	}

	/**
	 * Get all the email skins registered for this theme.
	 * Each template can be a different site skin, ie: 2-column, 3-column, etc.
	 *
	 * @return array
	 */
	public function getEmailSkins(){
		$out = [];
		$default = null;
		$currenttheme = false;

		// If this theme is currently selected, check the default template too.
		if($this->getKeyName() == \ConfigHandler::Get('/theme/selected')){
			$default = \ConfigHandler::Get('/theme/default_email_template');

			$currenttheme = true;
		}

		foreach($this->_xmlloader->getElements('//emailskins/file') as $f){
			$basefilename = $f->getAttribute('filename');
			$filename = $this->getBaseDir() . 'emailskins/' . $basefilename;

			$skin = \Core\Templates\Template::Factory($filename);
			$title = $basefilename;

			// The return is expecting an array.
			$out[] = [
				'filename'        => $filename,
				'file'            => $basefilename,
				'title'           => $title,
				'default'         => ($default == $basefilename),
				'current_theme'   => $currenttheme,
			];
		}

		// Tack on the main default... no skin!
		$out[] = [
			'filename'        => '',
			'file'            => '',
			'title'           => '-- No Skin --',
			'default'         => ($default == ''),
			'current_theme'   => $currenttheme,
		];

		return $out;
	}

	/**
	 * Alias of getSkins()
	 * @return mixed
	 */
	public function getTemplates(){
		return $this->getSkins();
	}

	/**
	 * Get this theme's "key" name.
	 *
	 * This *must* be the name of the directory it's installed in
	 * and *must not* contain spaces or other weird characters.
	 *
	 * @return string
	 */
	public function getKeyName(){
		return str_replace(' ', '-', strtolower($this->_name));
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
	 * Get the base directory of this theme
	 *
	 * Generally /home/foo/public_html/themes/theme-name/
	 *
	 * @param mixed|string $prefix Path to prepend to the string.  Use "" for relative, or ROOT_PDIR for fully resolved.
	 *
	 * @return string
	 */
	public function getBaseDir($prefix = ROOT_PDIR){
		return $prefix . 'themes/' . $this->getKeyName() . '/';
	}

	/**
	 * Save this theme metadata back to its XML file.
	 * Useful in packager scripts.
	 */
	public function save($minified = false){
		// Ensure there's a required namespace on the root node.
		$this->_xmlloader->getRootDOM()->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");

		// This needs to be the final step... write the XML doc back to the file.
		$XMLFilename = $this->getBaseDir() . 'theme.xml';

		if ($minified) {
			file_put_contents($XMLFilename, $this->_xmlloader->asMinifiedXML());
		}
		else {
			file_put_contents($XMLFilename, $this->_xmlloader->asPrettyXML());
		}
	}

	/**
	 * Save or get the package XML for this theme.  This is useful for the
	 * packager
	 *
	 * @param boolean $minified
	 * @param string  $filename
	 */
	public function savePackageXML($minified = true, $filename = false) {

		// Instantiate a new XML Loader object and get it ready to use.
		$dom = new \XMLLoader();
		$dom->setRootName('package');
		$dom->load();

		// Populate the root attributes for this theme package.
		$dom->getRootDOM()->setAttribute('type', 'theme');
		$dom->getRootDOM()->setAttribute('name', $this->getName());
		$dom->getRootDOM()->setAttribute('version', $this->getVersion());

		// Declare the packager
		$dom->createElement('packager[version="' . \Core::GetComponent()->getVersion() . '"]');

		/* // Themes don't have any provide directives.
		// Copy over any provide directives.
		foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('provides') as $u) {
			$newu = $dom->getDOM()->importNode($u);
			$dom->getRootDOM()->appendChild($newu);
		}
		$dom->getElement('/provides[type="component"][name="' . strtolower($this->getName()) . '"][version="' . $this->getVersion() . '"]');
		*/

		/* // Themes don't have any requrie directives.
		// Copy over any requires directives.
		foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('requires') as $u) {
			$newu = $dom->getDOM()->importNode($u);
			$dom->getRootDOM()->appendChild($newu);
		}
		*/

		// Copy over any upgrade directives.
		// This one can be useful for an existing installation to see if this
		// package can provide a valid upgrade path.
		foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('upgrade') as $u) {
			$newu = $dom->getDOM()->importNode($u);
			$dom->getRootDOM()->appendChild($newu);
		}

		// Tack on description
		$desc = $this->_xmlloader->getElement('/description', false);
		if ($desc) {
			$newd            = $dom->getDOM()->importNode($desc);
			$newd->nodeValue = $desc->nodeValue;
			$dom->getRootDOM()->appendChild($newd);
		}


		$out = ($minified) ? $dom->asMinifiedXML() : $dom->asPrettyXML();

		if ($filename) {
			file_put_contents($filename, $out);
		}
		else {
			return $out;
		}
	}

	/**
	 * Get the raw XML of this theme, useful for debugging.
	 *
	 * @return string (XML)
	 */
	public function getRawXML($minified = false) {
		return ($minified) ? $this->_xmlloader->asMinifiedXML() : $this->_xmlloader->asPrettyXML();
	}

	/**
	 * Set all asset files in this theme.  Only really usable in the installer.
	 *
	 * @param $files array Array of files to set.
	 */
	public function setAssetFiles($files) {
		// Clear out the array first.
		$this->_xmlloader->removeElements('//theme/assets/file');

		// It would be nice to have them alphabetical.
		$newarray = [];
		foreach ($files as $f) {
			$newarray[$f['file']] = $f;
		}
		ksort($newarray);

		// And recreate them all.
		foreach ($newarray as $f) {
			$this->addAssetFile($f);
		}
	}

	/**
	 * Add a single asset file to this theme.
	 *
	 * @param array $file The array is expected to be an associative array with attributes "file" and "md5".
	 */
	public function addAssetFile($file){
		$this->_xmlloader->createElement('//theme/assets/file[@filename="' . $file['file'] . '"][@md5="' . $file['md5'] . '"]');
	}

	/**
	 * Set all skin files in this theme.  Only really usable in the installer.
	 *
	 * @param $files array Array of files to set.
	 */
	public function setSkinFiles($files) {

		// This behaves slightly differently than the other ones, since this can include metadata for the skin.
		// As such, they are not simply deleted to begin with.

		// It would be nice to have them alphabetical.
		$newarray = [];
		foreach ($files as $f) {
			// Make sure that the file does not start with 'skins/'...
			if(strpos($f['file'], 'skins/') === 0) $f['file'] = substr($f['file'], 6);
			$newarray[$f['file']] = $f;
		}
		ksort($newarray);

		$used = [];
		// Instead, I'm checking each existing one.
		foreach($this->_xmlloader->getElements('//theme/skins/file') as $el){
			$att_file = $el->getAttribute('filename');
			if(isset($newarray[$att_file])){
				$used[] = $att_file;
				$el->setAttribute('md5', $newarray[$att_file]['md5']);
			}
			else{
				// Remove it!
				$this->_xmlloader->getElement('//theme/skins', false)->removeChild($el);
			}
		}

		// And make sure that I didn't miss any new ones.
		foreach($newarray as $f){
			if(!in_array($f['file'], $used)){
				// Make the title something generic.
				$title = substr($f['file'], 6, -4);
				$this->_xmlloader->createElement('//theme/skins/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"][@title="' . $title . '"]');
			}
		}
	}

	/**
	 * Set all view files in this theme.  Only really usable in the installer.
	 *
	 * @param $files array Array of files to set.
	 */
	public function setViewFiles($files) {
		// Clear out the array first.
		$this->_xmlloader->removeElements('//theme/view/file');

		// It would be nice to have them alphabetical.
		$newarray = [];
		foreach ($files as $f) {
			$newarray[$f['file']] = $f;
		}
		ksort($newarray);

		// And recreate them all.
		foreach ($newarray as $f) {
			$this->_xmlloader->createElement('//theme/view/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');
		}
	}

	/**
	 * Set all other files in this theme.  Only really usable in the installer.
	 *
	 * @param $files array Array of files to set.
	 */
	public function setOtherFiles($files) {
		// Clear out the array first.
		$this->_xmlloader->removeElements('//theme/otherfiles/file');

		// It would be nice to have them alphabetical.
		$newarray = [];
		foreach ($files as $f) {
			$newarray[$f['file']] = $f;
		}
		ksort($newarray);

		// And recreate them all.
		foreach ($newarray as $f) {
			$this->_xmlloader->createElement('//theme/otherfiles/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');
		}
	}

	/**
	 * Alias of setOtherFiles
	 *
	 * @param $files
	 */
	public function setFiles($files) {
		$this->setOtherFiles($files);
	}

	/**
	 * Return an array of every license, (and its URL), in this theme.
	 */
	public function getLicenses() {
		$ret = [];
		foreach ($this->_xmlloader->getElementsByTagName('license') as $el) {
			$url   = @$el->getAttribute('url');
			$ret[] = [
				'title' => $el->nodeValue,
				'url'   => $url
			];
		}
		return $ret;
	}

	public function setLicenses($licenses) {
		// First, remove any licenses currently in the XML.
		$this->_xmlloader->removeElements('/license');

		// Now I can add the ones in the licenses array.
		foreach ($licenses as $lic) {
			$str          = '/license' . ((isset($lic['url']) && $lic['url']) ? '[@url="' . $lic['url'] . '"]' : '');
			$l            = $this->_xmlloader->getElement($str);
			$l->nodeValue = $lic['title'];
		}
	}

	/**
	 * Return an array of every author in this theme.
	 */
	public function getAuthors() {
		$ret = [];
		foreach ($this->_xmlloader->getElementsByTagName('author') as $el) {
			$ret[] = [
				'name'  => $el->getAttribute('name'),
				'email' => @$el->getAttribute('email'),
			];
		}
		return $ret;
	}

	public function setAuthors($authors) {
		// First, remove any authors currently in the XML.
		$this->_xmlloader->removeElements('/author');

		// Now I can add the ones in the authors array.
		foreach ($authors as $a) {
			if (isset($a['email']) && $a['email']) {
				$this->_xmlloader->getElement('//theme/author[@name="' . $a['name'] . '"][@email="' . $a['email'] . '"]');
			}
			else {
				$this->_xmlloader->getElement('//theme/author[@name="' . $a['name'] . '"]');
			}
		}
	}

	/**
	 * Get this theme's version
	 *
	 * @return string
	 */
	public function getVersion() {
		return $this->_version;
	}

	/**
	 * Set the version of this theme
	 *
	 * This affects the theme.xml metafile of the package.
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
	 * Get the description for this theme
	 * @return string
	 */
	public function getDescription() {
		if ($this->_description === null) {
			$this->_description = trim($this->_xmlloader->getElement('//description')->nodeValue);
		}

		return $this->_description;
	}

	/**
	 * Set the description for this theme
	 * @param $desc string
	 */
	public function setDescription($desc) {
		// Set the cache first.
		$this->_description = $desc;
		// And set the data in the original DOM.
		$this->_xmlloader->getElement('//description')->nodeValue = $desc;
	}

	public function getViewSearchDir(){
		$d = $this->getBaseDir() . 'templates/';
		return (is_dir($d)) ? $d : null;
	}

	public function getAssetDir(){
		$d = $this->getBaseDir() . 'assets/';
		return (is_dir($d)) ? $d : null;
	}

	public function getSkinDir(){
		$d = $this->getBaseDir() . 'skins/';
		return (is_dir($d)) ? $d : null;
	}

	/**
	 * Get all ConfigModels loaded for this specific theme.
	 *
	 * @return array of ConfigModel
	 */
	public function getConfigs(){
		$configs = array();

		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('configs');

		// Now, get every table under this node.
		foreach ($node->getElementsByTagName('config') as $confignode) {
			/** @var \DOMElement $confignode */
			$key         = $confignode->getAttribute('key');

			// Themes only allow for keys starting with "/theme/"!
			// This is to encourage that all themes share a common subset of configuration options.
			// EG: if the end user sees: "Site Logo", "Business Address", "Business Phone" on one theme,
			// they would be expecting to see those same options with the same values if they change the theme,
			// (and the new theme supports those same options).
			if(strpos($key, '/theme/') === 0){
				$configs[] = \ConfigHandler::GetConfig($key);
			}
		}

		return $configs;
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
	 * Install this theme and its assets.
	 *
	 * Returns false if nothing changed, else will return an array containing all changes.
	 *
	 * @param int $verbose 0 for standard output, 1 for real-time, 2 for real-time verbose output.
	 *
	 * @return false | array
	 * @throws \InstallerException
	 */
	public function install($verbose = 0){
		// @todo I need actual error checking here.
		//if($this->isInstalled()) return false;

		$changes = $this->_performInstall($verbose);

		if(is_array($changes) && sizeof($changes)){
			\SystemLogModel::LogInfoEvent('/updater/theme/install', 'Theme ' . $this->getName() . ' installed successfully!', implode("\n", $changes));
		}

		return $changes;
	}

	/**
	 * "Reinstall" (aka) Install this theme and its assets.
	 *
	 * Alias of install()
	 *
	 * @param int $verbose 0 for standard output, 1 for real-time, 2 for real-time verbose output.
	 *
	 * @return false | array
	 * @throws \InstallerException
	 */
	public function reinstall($verbose = 0){
		// @todo I need actual error checking here.
		//if(!$this->isInstalled()) return false;

		$changes =  $this->_performInstall($verbose);

		if(is_array($changes) && sizeof($changes)){
			\SystemLogModel::LogInfoEvent('/updater/theme/reinstall', 'Theme ' . $this->getName() . ' installed successfully!', implode("\n", $changes));
		}

		return $changes;
	}

	/**
	 * "Upgrade" (aka) Install this theme and its assets.
	 *
	 * Alias of install()
	 *
	 * @return false | array
	 * @throws \InstallerException
	 */
	public function upgrade(){
		//if(!$this->isInstalled()) return false;

		$changes =  $this->_performInstall();

		if(is_array($changes) && sizeof($changes)){
			\SystemLogModel::LogInfoEvent('/updater/theme/upgrade', 'Theme ' . $this->getName() . ' installed successfully!', implode("\n", $changes));
		}

		return $changes;
	}

	/**
	 * Get if this theme is currently set as the site default.
	 *
	 * @return bool
	 */
	public function isDefault(){
		return \ConfigHandler::Get('/theme/selected') == $this->getKeyName();
	}

	/**
	 * Get the primary, (first), screenshot of this theme.
	 *
	 * @return array
	 */
	public function getScreenshot(){
		$s = $this->_xmlloader->getElement('//screenshots/screenshot', false);

		if(!$s){
			return [
				'file' => '',
				'title' => $this->getName()
			];
		}
		else{

			$f = \Core\Filestore\Factory::File($this->getBaseDir() . $s->getAttribute('file'));

			return [
				'file' => $f,
				'title' => ($s->getAttribute('title') ? $s->getAttribute('title') : $this->getName()),
			];
		}
	}

	/**
	 * Because install, upgrade and remove all are actually the exact same logic for themes.
	 *
	 * Returns false if nothing changed, else will return an array containing all changes.
	 *
	 * @param int $verbose 0 for standard output, 1 for real-time, 2 for real-time verbose output.
	 *
	 * @return false | array
	 * @throws \InstallerException
	 */
	private function _performInstall($verbose = 0){
		$changed = [];

		$change = $this->_installAssets($verbose);
		if($change !== false) $changed = array_merge($changed, $change);

		$change = $this->_parseConfigs(true);
		if($change !== false) $changed = array_merge($changed, $change);

		// Make sure the version is correct in the database.
		$c = new \ComponentModel('theme/' . $this->_name);
		$c->set('version', $this->_version);
		$c->save();

		return (sizeof($changed)) ? $changed : false;
	}

	/**
	 * Internal function to parse and handle the configs in the theme.xml file.
	 * This is used for installations and upgrades.
	 *
	 * Returns false if nothing changed, else will return the configuration options changed.
	 *
	 * @param boolean $install Set to false to force uninstall/disable mode.
	 *
	 * @return false | array
	 *
	 * @throws \InstallerException
	 */
	private function _parseConfigs($install = true){
		// Keep track of if this changed anything.
		$changes = array();

		$action = $install ? 'Installing' : 'Uninstalling';
		$set    = $install ? 'Set' : 'Unset';

		\Core\Utilities\Logger\write_debug($action . ' configs for ' . $this->getName());

		// I need to get the schema definitions first.
		$node = $this->_xmlloader->getElement('configs');
		//$prefix = $node->getAttribute('prefix');

		// Now, get every table under this node.
		foreach ($node->getElementsByTagName('config') as $confignode) {
			/** @var \DOMElement $confignode */
			$key         = $confignode->getAttribute('key');
			$options     = $confignode->getAttribute('options');
			$type        = $confignode->getAttribute('type');
			$default     = $confignode->getAttribute('default');
			$title       = $confignode->getAttribute('title');
			$description = $confignode->getAttribute('description');
			$mapto       = $confignode->getAttribute('mapto');
			$encrypted   = $confignode->getAttribute('encrypted');
			$formAtts    = $confignode->getAttribute('form-attributes');

			if($encrypted === null || $encrypted === '') $encrypted = '0';

			// Themes only allow for keys starting with "/theme/"!
			// This is to encourage that all themes share a common subset of configuration options.
			// EG: if the end user sees: "Site Logo", "Business Address", "Business Phone" on one theme,
			// they would be expecting to see those same options with the same values if they change the theme,
			// (and the new theme supports those same options).
			if(strpos($key, '/theme/') !== 0){
				trigger_error('Please ensure that all config options in themes start with "/theme/"! (Mismatched config found in ' . $this->getName() . ':' . $key, E_USER_NOTICE);
				continue;
			}

			// Default if omitted.
			if(!$type) $type = 'string';

			$m   = \ConfigHandler::GetConfig($key);
			$m->set('options', $options);
			$m->set('type', $type);
			$m->set('default_value', $default);
			$m->set('title', $title);
			$m->set('description', $description);
			$m->set('mapto', $mapto);
			$m->set('encrypted', $encrypted);
			$m->set('form_attributes', $formAtts);

			// Default from the xml, only if it's not already set.
			if ($m->get('value') === null || !$m->exists()){
				$m->set('value', $confignode->getAttribute('default'));
			}
			// Allow configurations to overwrite any value.  This is useful on the initial installation.
			if (isset($_SESSION['configs']) && isset($_SESSION['configs'][$key])){
				$m->set('value', $_SESSION['configs'][$key]);
			}

			if ($m->save()) $changes[] = $set . ' configuration [' . $m->get('key') . '] to [' . $m->get('value') . ']';

			// Make it available immediately
			\ConfigHandler::CacheConfig($m);
		}

		return (sizeof($changes)) ? $changes : false;
	} // private function _parseConfigs

	/**
	 * Copy in all the assets for this theme into the assets location.
	 *
	 * Returns false if nothing changed, else will return an array of all the changes that occured.
	 *
	 * @param int $verbose 0 for standard output, 1 for real-time, 2 for real-time verbose output.
	 *
	 * @return false | array
	 * @throws \InstallerException
	 */
	private function _installAssets($verbose = 0){
		$assetbase = \Core\Filestore\get_asset_path();

		$coretheme = \ConfigHandler::Get('/theme/selected');
		// WHY is core theme set to blank?!?
		// Damn installer...
		// this happens in the installer.
		if($coretheme === null) $coretheme = 'default';
		$theme = $this->getKeyName();
		$changes = [];

		foreach($this->_xmlloader->getElements('/assets/file') as $node){
			// Cannot install assets if the directory is not setup!
			if(!$this->getAssetDir()){
				continue;
			}
			$b = $this->getBaseDir();
			// The base filename with the directory.
			$filename = $node->getAttribute('filename');

			// The new theme asset will be installed into the same directory as its theme.
			// This differs from usual components because they just follow whatever theme is currently running.
			//$nf = Core::File($assetbase . $theme . '/' . $filename);
			$trimmedfilename = substr($b . $node->getAttribute('filename'), strlen($this->getAssetDir()));
			$themespecificfilename = $assetbase . $theme . '/' . $trimmedfilename;
			$newfilename = 'assets/' . $trimmedfilename;


			// Before anything, check and see if this file has a custom override file present.
			if(file_exists(ROOT_PDIR . 'themes/custom/' . $newfilename)){
				// If so, then copy that asset to the custom directory too!
				$f = \Core\Filestore\Factory::File(ROOT_PDIR . 'themes/custom/' . $newfilename);
			}
			else{
				// Otherwise, the local file is guaranteed to be a local file.
				$f = new \Core\Filestore\Backends\FileLocal($b . $filename);
			}

			if($verbose == 2){
				CLI::PrintActionStart('Installing asset ' . $f->getBasename());
			}

			$nf = \Core\Filestore\Factory::File($newfilename);

			/*
			// The various replacement possibilities for this file.
			// The new destination must be in the theme-specific directory, this is a
			// bit of a hack from the usual behaviour of the filestore system.
			// Since that's designed to return the default if the theme-specific doesn't exist.
			$replacements = array(
				// The theme is not default, but the system translated the path to the default directory.
				// This is because the file doesn't exist in any theme.
				// This is actually expected behaviour, except unwanted here.
				'default/' . $trimmedfilename => $theme . '/' . $trimmedfilename,
				// The theme is not the currently installed, but the system translated the path to the that directory.
				// This is because the filename is the same as the installed theme, so the system just translated there.
				// We don't want that.
				$coretheme . '/' . $trimmedfilename => $theme . '/' . $trimmedfilename,
			);


			foreach($replacements as $k => $v){
				if($k == $v) continue;
				if(strpos($nf->getFilename(), $k) !== false){
					$nf->setFilename( str_replace($k, $v, $nf->getFilename()) );
				}
			}
*/
			// Check if this file even needs updated. (this is primarily used for reporting reasons)
			if($nf->exists() && $nf->identicalTo($f)){
				//echo "Skipping file, it's identical.<br/>";

				if($verbose == 2){
					CLI::PrintActionStatus('skip');
				}

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

			if(!$f->isReadable()){
				throw new \InstallerException('Source file [' . $f->getFilename() . '] is not readable.');
			}

			try{
				$f->copyTo($nf, true);
			}
			catch(\Exception $e){
				throw new \InstallerException('Unable to copy [' . $f->getFilename() . '] to [' . $nf->getFilename() . ']');
			}


			$change = $action . ' ' . $nf->getFilename();
			$changes[] = $change;

			if($verbose == 1){
				CLI::PrintLine($change);
			}
			elseif($verbose == 2){
				CLI::PrintActionStatus('ok');
			}
		}

		if(!sizeof($changes)){
			if($verbose > 0){
				CLI::PrintLine('No changes required');
			}
			return false;
		}

		// Make sure the asset cache is purged!
		\Core\Cache::Delete('asset-resolveurl');

		return $changes;
	}
}
