<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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

		// Tack on the search directories from the loaded components.
		foreach(ComponentHandler::GetLoadedComponents() as $c){
			$d = $c->getViewSearchDir();
			$this->addTemplateDir($d);
		}

		$this->addPluginsDir(ROOT_PDIR . 'core/libs/smarty-plugins/');

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
