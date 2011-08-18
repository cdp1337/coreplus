<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
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
	
	private $_footscripts = array();
	
	private $_headstylesheets = array();
	
	/**
	 * Any html to prepend to the body before rendering.
	 * Useful for plugins and widgets.
	 * 
	 * @var array
	 */
	private $_prebody = array();
	
	/**
	 * Any html to append to the body before rendering.
	 * Useful for plugins and widgets.
	 * 
	 * @var array
	 */
	private $_postbody = array();
	
	/**
	 * Array of attributes to tack onto the <html> tag automatically.
	 * @var array
	 */
	private $_htmlattributes = array('xmlns' => "http://www.w3.org/1999/xhtml");

	/**
	 * @var PageModel The page component currently being viewed.
	 */
	private $_page;

	private function __construct(){
		$uri = $_SERVER['REQUEST_URI'];

		// If blank, default to '/' (should be root url)
		if(!$uri) $uri = ROOT_WDIR;
		
		// Now I can trim off the prefix, since that's not needed in deciding the path.
		$uri = substr($uri, strlen(ROOT_WDIR));
		
		// Split the string on the '?'.  Obviously anything after are parameters.
		if(($_qpos = strpos($uri, '?')) !== false) $uri = substr($uri, 0, $_qpos);
		
		// the URI should start with a '/'.
		if($uri{0} != '/') $uri = '/' . $uri;
		
		// If the useragent requested a specifc mode type, remember that and set it for the page.
		if(preg_match('/\.[a-z]{3,4}$/i', $uri)){
			$ctype = strtolower(preg_replace('/^.*\.([a-z]{3,4})$/i', '\1', $uri));
			$uri = substr($uri, 0, -1 - strlen($ctype));
		}
		else{
			$ctype = 'html';
		}
		
		
		// Trim off anything after the first & if present.
		//if(strpos($uri, '&') !== false) $uri = substr($uri, 0, strpos($uri, '&'));
		
		$p = PageModel::Find(array('rewriteurl' => $uri, 'fuzzy' => 0), 1);
		
		// Split this URL, it'll be used somewhere.
		$pagedat = PageModel::SplitBaseURL($uri);
		
		if($p){
			// :) Found it
			$this->_page = $p;
		}
		elseif($pagedat){
			// Is this even a valid controller?
			// This will allow a page to be called with it being in the pages database.
			$p = new PageModel();
			$p->set('baseurl', $uri);
			$p->set('rewriteurl', $uri);
			$this->_page = $p;
		}
		else{
			// No page in the database and no valid controller... sigh
			return false;
		}
		
		//var_dump($p); die();
		
		// Make sure all the parameters from both standard GET and core parameters are tacked on.
		if($pagedat && $pagedat['parameters']){
			foreach($pagedat['parameters'] as $k => $v){
				$this->_page->setParameter($k, $v);
			}
		}
		if(is_array($_GET)){
			foreach($_GET as $k => $v){
				if(is_numeric($k)) continue;
				$this->_page->setParameter($k, $v);
			}
		}
		
		// Some pages may support dynamic content types from the getgo.
		// @todo Should the $_SERVER['HTTP_ACCEPT'] flag be used here?
		switch($ctype){
			case 'xml':  $ctype = View::CTYPE_XML;  break;
			case 'json': $ctype = View::CTYPE_JSON; break;
			default:     $ctype = View::CTYPE_HTML; break;
		}
		
		$this->_page->getView()->contenttype = $ctype;
		
		//$this->_page->getView();
		//var_dump($this->_page); die();
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
	
	/**
	 * Add a script to the current page.
	 * 
	 * @param string $script
	 * @param string $location
	 */
	public static function AddScript($script, $location = 'head'){
		if(strpos($script, '<script') === false){
			// Resolve the script and wrap it with a script block.
			$script = '<script type="text/javascript" src="' . Core::ResolveAsset($script) . '"></script>';
		}
		
		$obj = self::Singleton();
		
		// I can check to see if this script has been loaded before.
		if(in_array($script, $obj->_headscripts)) return;
		if(in_array($script, $obj->_footscripts)) return;
		
		// No? alright, add it to the requested location!
		if($location == 'head') $obj->_headscripts[] = $script;
		else $obj->_footscripts[] = $script;
	}
	
	/**
	 * Add some content to the body before rendering is complete.
	 * This is useful to modify page content from a widget or plugin.
	 * 
	 * @param string $content
	 * @param string $location 'pre' or 'post', prepend or append the regular body.
	 */
	public static function AddBodyContent($content, $location = 'pre'){
		$obj = self::Singleton();
				
		// Only allow content to be added once.
		if(in_array($content, $obj->_prebody)) return;
		if(in_array($content, $obj->_postbody)) return;
		
		// No? alright, add it to the requested location!
		if($location == 'pre') $obj->_prebody[] = $content;
		else $obj->_postbody[] = $content;
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
	
	public static function SetHTMLAttribute($attribute, $value){
		self::Singleton()->_htmlattributes[$attribute] = $value;
	}
	
	public static function GetHead(){
		$obj = self::Singleton();
		
		$out = implode("\n", $obj->_headscripts);
		$out .= "\n";
		$out .= implode("\n", $obj->_headstylesheets);
		
		return trim($out);
	}
	
	public static function GetFoot(){
		$obj = self::Singleton();
		
		$out = implode("\n", $obj->_footscripts);
		
		return trim($out);
	}
	
	public static function GetBodyPre(){
		return trim(implode("\n", self::Singleton()->_prebody));
	}
	
	public static function GetBodyPost(){
		return trim(implode("\n", self::Singleton()->_postbody));
	}
	
	public static function GetHTMLAttributes($asarray = false){
		$atts = self::Singleton()->_htmlattributes;
		
		if($asarray){
			return $atts;
		}
		else{
			$str = '';
			foreach($atts as $k => $v) $str .= " $k=\"" . str_replace('"', '\"', $v) . "\"";
			return trim($str);
		}
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
		
		// Dispatch the hooks here if it's a 404 or 403.
		if($view->error == View::ERROR_ACCESSDENIED || $view->error == View::ERROR_NOTFOUND){
			// Let other things chew through it... (optionally)
			HookHandler::DispatchHook('/core/page/error-' . $view->error, $view);
		}
		
		if($view->error != View::ERROR_NOERROR){
			// Update some information in the view.
			// Transpose some useful data for it.
			$view->baseurl = '/Error/Error' . $view->error;
			$view->setParameters(array());
			$view->templatename = '/pages/error/error' . $view->error . '.tpl';
			$view->mastertemplate = ConfigHandler::GetValue('/core/theme/default_template');
		}
		
		
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
		
		// Yay, send the content type and status to the browser.
		switch($view->error){
			case View::ERROR_NOERROR:      header('Status: 200 OK', true, $view->error); break;
			case View::ERROR_ACCESSDENIED: header('Status: 403 Forbidden', true, $view->error); break;
			case View::ERROR_NOTFOUND:     header('Status: 404 Not Found', true, $view->error); break;
			case View::ERROR_SERVERERROR:  header('Status: 500 Internal Server Error', true, $view->error); break;
			default:                       header('Status: 500 Internal Server Error', true, $view->error); break; // I don't know WTF happened...
		}
		
		if($view->contenttype) header('Content-Type: ' . $view->contenttype);
		
		if(DEVELOPMENT_MODE) header('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());
		
		echo $data;
		
		
		// If the viewmode is regular and DEVELOPMENT_MODE is enabled, show some possibly useful information now that everything's said and done.
		if(DEVELOPMENT_MODE && $view->mode == View::MODE_PAGE && $view->contenttype == View::CTYPE_HTML){
			echo '<pre class="xdebug-var-dump">';
			echo "Database Reads: " . Core::DB()->readCount() . "\n";
			echo "Database Writes: " . Core::DB()->writeCount() . "\n";
			//echo "Number of queries: " . DB::Singleton()->counter . "\n";
			echo "Amount of memory used by PHP: " . Core::FormatSize(memory_get_usage()) . "\n";
			echo "Total processing time: " . round(Core::GetProfileTimeTotal(), 3) . ' seconds';
			echo '</pre>';
		}
	}
}
