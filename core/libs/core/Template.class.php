<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
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

		$dirs = $t->getTemplateDir();

		// Trim off the beginning '/' if there is one;  All directories end with a '/'.
		if($filename{0} == '/') $filename = substr($filename, 1);

		foreach($dirs as $d){
			if(file_exists($d . $filename)) return $d . $filename;
		}

		// Nope?
		return null;
	}
}
