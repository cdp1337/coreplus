<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * 
 * Copyright (C) 2009  Charlie Powell <powellc@powelltechs.com>
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
 * CurrentPage.class.php
 * 
 * @package 
 * @subpackage 
 * @version 
 * @author 
 *
 */


/**
 * Singleton class for the currently viewing page.
 * 
 * @author powellc
 *
 */
class CurrentPage{
	private static $_instance = null;
	
	private $_headscripts = array();
	
	private $_headstylesheets = array();

	/**
	 * @var PageModel The page component currently being viewed.
	 */
	private $_page;

	private function __construct(){
		$uri = $_SERVER['QUERY_STRING'];

		// If blank, default to '/' (should be root url)
		if(!$uri) $uri = '/';

		// Trim off anything after the first & if present.
		if(strpos('&', $uri)) $uri = substr($uri, 0, strpos('&', $uri));
		
		$p = PageModel::Find(array('rewriteurl' => $uri, 'fuzzy' => 0), 1);
		

		if($p){
			// :) Found it
			$this->_page = $p;
		}
		else{
			// Is this even a valid controller?
			$a = PageModel::SplitBaseURL($uri);
			if($a){
				$p = new PageModel();
				$p->set('baseurl', $uri);
				$p->set('rewriteurl', $uri);
				$this->_page = $p;
			}
		}
	}

	/**
	 *
	 * @return CurrentPage
	 */
	public static function Singleton(){
		// This system has no function in CLI mode.
		if(EXEC_MODE != 'WEB') return null;
		
		if(!self::$_instance){
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	public static function Render(){
		return self::Singleton()->_render();
	}
	
	public static function AddScript($script){
		if(strpos($script, '<script') === false){
			// Resolve the script and wrap it with a script block.
			$script = '<script type="text/javascript" src="' . Core::ResolveAsset($script) . '"></script>';
		}
		
		$obj = self::Singleton();
		// I can check to see if this script has been loaded before.
		if(!in_array($script, $obj->_headscripts)) $obj->_headscripts[] = $script;
	}
	
	public static function GetHead(){
		$obj = self::Singleton();
		
		$out = implode("\n", $obj->_headscripts);
		
		return $out;
	}

	private function _render(){
		// This may or may not be the original page...
		if($this->_page){
			$view = $this->_page->execute();
		}
		else{
			$view = new View();
			$view->error = View::ERROR_NOTFOUND;
		}
		
		
		if($view->error != View::ERROR_NOERROR){
			// Update some information in the view.
			// Transpose some useful data for it.
			$view->baseurl = '/Error/Error' . $view->error;
			$view->setParameters(array());
			$view->templatename = '/pages/error/error' . $view->error . '.tpl';
			$view->mastertemplate = ConfigHandler::GetValue('/core/theme/default_template');
		}
		
		
		//$view->headscripts = array_merge($view->headscripts, $this->_headscripts);
		$view->render();
	}
}
