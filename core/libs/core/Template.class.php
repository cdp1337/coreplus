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
 * Description of Template
 *
 * @author powellc
 */
class Template extends Smarty{

	private $_baseurl;

    public function  __construct() {
		parent::__construct();

		// Tack on the current theme's directory.
		$this->addTemplateDir(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/');

		// Tack on the search directories from the loaded components.
		// Also handle the plugins directory search.
		foreach(Core::GetComponents() as $c){
			$d = $c->getViewSearchDir();
			$this->addTemplateDir($d);
			
			if( ($plugindir = $c->getSmartyPluginDirectory()) ) $this->addPluginsDir($plugindir);
		}

		$this->compile_dir = TMP_DIR . 'smarty_templates_c';
		$this->cache_dir = TMP_DIR . 'smarty_cache';
	}

	public function setBaseURL($url){
		$this->_baseurl = $url;
	}
	public function getBaseURL(){
		return $this->_baseurl;
	}

	/**
	 * Resolve a filename stub to a fully resolved path.
	 *
	 * @param string $filename Filename to resolve
	 */
	public static function ResolveFile($filename){
		// I need a new template so I can retrieve all the paths.
		$t = new Template();

		foreach($t->template_dir as $d){
			if(file_exists($d . $filename)) return $d . $filename;
		}

		// Nope?
		return null;
	}
}
