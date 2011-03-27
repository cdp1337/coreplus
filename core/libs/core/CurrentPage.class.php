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
		if(strpos($uri, '&') !== false) $uri = substr($uri, 0, strpos($uri, '&'));
		
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
		
		// Merge in any GET parameters.
		if(is_array($_GET)){
			// Skip the first one...
			array_shift($_GET);
			foreach($_GET as $k => $v){
				if(is_numeric($k)) continue;
				$this->_page->setParameter($k, $v);
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
	
	public static function AddStylesheet($link, $media="all"){
		if(strpos($link, '<link') === false){
			// Resolve the script and wrap it with a script block.
			$link = '<link type="text/css" href="' . Core::ResolveAsset($link) . '" media="' . $media . '" rel="stylesheet"/>';
		}
		
		$obj = self::Singleton();
		// I can check to see if this script has been loaded before.
		if(!in_array($link, $obj->_headstylesheets)) $obj->_headstylesheets[] = $link;
	}
	
	public static function GetHead(){
		$obj = self::Singleton();
		
		$out = implode("\n", $obj->_headscripts);
		$out .= "\n";
		$out .= implode("\n", $obj->_headstylesheets);
		
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
		try{
			$data = $view->fetch();
		}
		// If something happens in the rendering of the template... consider it a server error.
		catch(Exception $e){
			$view->error = View::ERROR_SERVERERROR;
			$view->baseurl = '/Error/Error' . $view->error;
			$view->setParameters(array());
			$view->templatename = '/pages/error/error' . $view->error . '.tpl';
			$view->mastertemplate = ConfigHandler::GetValue('/core/theme/default_template');
			$view->assignVariable('exception', $e);
			$data = $view->fetch();
		}
		
		// Save this page back in the database if it's available.
		if($view->error == View::ERROR_NOERROR){
			// Save this page data too.
			if($this->_page->exists()){
				$this->_page->set('title', $view->title);
				$this->_page->set('access', $view->access);
				$this->_page->save();
			} 
		}
		
		// Yay, send the content type and status to the browser.
		switch($view->error){
			case View::ERROR_NOERROR:      header('Status: 200 OK', true, $view->error); break;
			case View::ERROR_ACCESSDENIED: header('Status: 403 Forbidden', true, $view->error); break;
			case View::ERROR_NOTFOUND:     header('Status: 404 Not Found', true, $view->error); break;
			case View::ERROR_SERVERERROR:  header('Status: 500 Internal Server Error', true, $view->error); break;
			default:                       header('Status: 500 Internal Server Error', true, $view->error); break; // I don't know WTF happened...
		}
		
		if($view->contenttype) header('Content-Type: ' . $view->contenttype);
		
		if(DEVELOPMENT_MODE) header('X-Content-Encoded-By: CAE2 ' . Core::GetComponent()->getVersion());
		
		echo $data;
		
		
		// If the viewmode is regular and DEVELOPMENT_MODE is enabled, show some possibly useful information now that everything's said and done.
		if(DEVELOPMENT_MODE && $view->mode == View::MODE_PAGE){
			echo '<pre class="xdebug-var-dump">';
			echo "Number of queries: " . DB::Singleton()->counter . "\n";
			echo "Amount of memory used by PHP: " . File::FormatSize(memory_get_usage()) . "\n";
			echo "Total processing time: " . round(Core::GetProfileTimeTotal(), 3) . ' seconds';
			echo '</pre>';
		}
	}
}
