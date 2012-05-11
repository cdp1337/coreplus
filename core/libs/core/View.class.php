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
 * Provides all elements required to connect the Controller's data and logic
 * back to the browser in the necessary format.
 *
 * @author powellc
 */
class View {

	/* Errors that may be occured with views */
	const ERROR_OTHER        = 1;
	const ERROR_NOERROR      = 200;
	const ERROR_BADREQUEST   = 400;
	const ERROR_ACCESSDENIED = 403;
	const ERROR_NOTFOUND     = 404;
	const ERROR_SERVERERROR  = 500;
	
	/*** Modes for handling the rendering of the view ***/
	
	/**
	 * Standard page inside the master page template
	 */
	const MODE_PAGE       = 'page';
	/**
	 * Widget page inside a given widget container
	 */
	const MODE_WIDGET     = 'widget';
	/**
	 * No automatic output whatsoever
	 * Useful for file downloads and completely custom views.
	 */
	const MODE_NOOUTPUT   = 'nooutput';
	/**
	 * Render a template, but do not wrap in a master page template
	 * This is useful if you want the power of a template, but for an 
	 * ajax or otherwise custom response.
	 */
	const MODE_AJAX       = 'ajax';
	/**
	 * Detect if the page request is a standard or ajax, toggle between respectively
	 * If the page is loaded via an ajax request, no master template is used.
	 * otherwise, a standard page load will render with the master page template.
	 */
	const MODE_PAGEORAJAX = 'pageorajax';
	//const MODE_JSON       = 'json';
	
	/**
	 * Request method for standard GET request
	 */
	const METHOD_GET    = 'GET';
	/**
	 * Request method for standard form submission
	 */
	const METHOD_POST   = 'POST';
	/**
	 *  @todo Not supported
	 */
	const METHOD_PUT    = 'PUT';
	/**
	 * @todo Not supported
	 */
	const METHOD_HEAD   = 'HEAD';
	/**
	 *  @todo Not supported
	 */
	const METHOD_DELETE = 'DELETE';
	
	/* Content types for this view */
	const CTYPE_HTML      = 'text/html';
	const CTYPE_PLAIN     = 'text/plain';
	const CTYPE_JSON      = 'application/json';
	const CTYPE_XML       = 'application/xml';


	public $error;
	private $_template;

	private $_params;

	/**
	 * The base URL of this view.  Used to resolve the template filename.
	 * 
	 * @var string
	 */
	public $baseurl;
	public $title;
	
	/**
	 * The access string for this page.
	 * @var string
	 */
	public $access;
	
	/**
	 * The template to render this view with.
	 * Should be the partial path of the template, including pages/
	 * 
	 * @example pages/mycomponent/view.tpl
	 * @var string
	 */
	public $templatename;
	
	/**
	 * The content type of this view.
	 * Generally set from the controller.
	 * 
	 * This IS sent to the browser if it's a page-type view!
	 * 
	 * @var string
	 */
	public $contenttype = View::CTYPE_HTML;
	
	
	/**
	 * The master template to render this view with.
	 * Should be just the filename, as it will be located automatically.
	 * 
	 * @example index.tpl
	 * @var string
	 */
	public $mastertemplate;
	public $breadcrumbs = array();
	public $controls = array();
	
	/**
	 * The mode of this View.
	 * Greatly affects the rendering result, since this can be a full page or a single widget.
	 * 
	 * MUST be one of the valid View::MODE_* strings!
	 * 
	 * @var string
	 */
	public $mode;
	
	public $jsondata = array();
		
	public static $MetaData = array();
	public static $HeadScripts = array();
	public static $FootScripts = array();
	public static $Stylesheets = array();
	public static $HTMLAttributes = array();
	public static $HeadData = array();
	
	public function __construct(){
		$this->error = View::ERROR_NOERROR;
		$this->mode = View::MODE_PAGE;
	}

	public function setParameters($params){
		$this->_params = $params;
	}

	public function getParameters(){
		if(!$this->_params){
			$this->_params = array();
		}

		return $this->_params;
	}

	public function getParameter($key){
		$p = $this->getParameters();
		return (array_key_exists($key, $p))? $p[$key] : null;
	}

	/**
	 *
	 * @return Template
	 */
	public function getTemplate(){
		if(!$this->_template){
			$this->_template = new Template();
			$this->_template->setBaseURL($this->baseurl);
		}

		return $this->_template;
	}

	/**
	 * Assign a variable to this view
	 * 
	 * @param $key string
	 * @param $val mixed
	 */
	public function assign($key, $val){
		$this->getTemplate()->assign($key, $val);
	}
	
	/**
	 * Alias of assign
	 * 
	 * @param $key string
	 * @param $val mixed
	 */
	public function assignVariable($key, $val){
		$this->assign($key, $val);
	}

	/**
	 * Get a variable that was set with "assign()"
	 * 
	 * @param string $key
	 * @return mixed 
	 */
	public function getVariable($key){
		// Damn smarty and its being more difficult...
		$v = $this->getTemplate()->getVariable($key);
		return ($v)? $v->value : null;
	}

	public function fetchBody(){
		// If there is set to be no system content, don't even bother with anything here!
		if($this->mode == View::MODE_NOOUTPUT){
			return null;
		}
		
		// Resolve the template based on the error code. (if present)
		if($this->error != View::ERROR_NOERROR){
			// Update some information in the view.
			// Transpose some useful data for it.
			//$view->baseurl = '/Error/Error' . $view->error;
			//$view->setParameters(array());
			$tmpl = '/pages/error/error' . $this->error . '.tpl';
			//$mastertmpl = ConfigHandler::Get('/theme/default_template');
		}
		else{
			$tmpl = $this->templatename;
			//$mastertmpl = 
		}
		
		// If the content type is set to something other that html, check if that template exists.
		switch($this->contenttype){
			case View::CTYPE_XML:
				$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'xml.tpl', $tmpl));
				if($ctemp){
					$tmpl = $ctemp;
					//$this->mastertemplate = 'index.xml.tpl';
				}
				else{
					$this->contenttype = View::CTYPE_HTML;
				}
				break;
			case View::CTYPE_JSON:
				// Did the controller send data to this view directly?
				// (because JSON supports raw data ^_^ )
				if(sizeof($this->jsondata)){
					$this->mastertemplate = false;
					$tmpl = false;
					return json_encode($this->jsondata);
				}
				$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'json.tpl', $tmpl));
				if($ctemp){
					$tmpl = $ctemp;
					//$this->mastertemplate = 'index.json.tpl';
					$this->mastertemplate = false;
				}
				else{
					$this->contenttype = View::CTYPE_HTML;
				}
				break;
		}
		
		if(!$tmpl && $this->templatename == ''){
			throw new Exception('Please set the variable "templatename" on the page view.');
		}

		switch($this->mode){
			case View::MODE_PAGE:
			case View::MODE_AJAX:
			case View::MODE_PAGEORAJAX:
				$t = $this->getTemplate();
				$tmpl = Template::ResolveFile($tmpl);
				//var_dump(Template::ResolveFile($tmpl)); die();
				return $t->fetch($tmpl);
				break;
			case View::MODE_WIDGET:
				// This template can be a couple things.
				$tn = Template::ResolveFile(preg_replace(':^[/]{0,1}pages/:', '/widgets/', $tmpl));
				if(!$tn) $tn = $tmpl;

				$t = $this->getTemplate();
				//var_dump($t);
				return $t->fetch($tn);
				break;
		}
		
	}

	public function fetch(){
		$body = $this->fetchBody();
		
		// If there's no template, I have nothing to even do!
		if($this->mastertemplate === false){
			return $body;
		}
		// Else if it's null, it's just not set yet :p
		elseif($this->mastertemplate === null){
			$this->mastertemplate = ConfigHandler::Get('/theme/default_template');
		}
		
		// Whee!
		//var_dump($this->templatename, Template::ResolveFile($this->templatename));
		// Content types take priority on controlling the master template.
		if($this->contenttype == View::CTYPE_JSON){
			$mastertpl = false;
		}
		else{
			// Master template depends on the render mode.
			switch($this->mode){
				case View::MODE_PAGEORAJAX:
					if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') $mastertpl = false;
					else $mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $this->mastertemplate;
					break;
				case View::MODE_NOOUTPUT:
				case View::MODE_AJAX:
					$mastertpl = false;
					break;
				case View::MODE_PAGE:
					$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/' . $this->mastertemplate;
					break;
				case View::MODE_WIDGET:
					$mastertpl = Template::ResolveFile('widgetcontainers/' . $this->mastertemplate);
					break;
			}
		}
		
		// If there's *still* no template, I still have nothing to do.
		if(!$mastertpl) return $body;
		
		
		// @todo Handle the metadata.
		// Tack on the meta data.
		//foreach($this->metas as $k => $v){
		//	$head .= '<meta name="' . $k . '" content="' . $v . '"/>' . "\n";
		//}
		
		
		$template = new Template();
		$template->setBaseURL('/');
		// Page-level views have some special variables.
		if($this->mode == View::MODE_PAGE){
			$template->assign('breadcrumbs', $this->getBreadcrumbs());
			$template->assign('controls', $this->controls);
			$template->assign('messages', Core::GetMessages());
			
			// Tack on the pre and post body variables from the current page.
			//$body = CurrentPage::GetBodyPre() . $body . CurrentPage::GetBodyPost();
		}
		// Widgets need some special variables too.
		//if($this->mode == View::MODE_WIDGET){
		//	//var_dump($this->getVariable('widget')); die();
		//	$template->assign('widget', $this->getVariable('widget'));
		//}
		$template->assign('title', $this->title);
		$template->assign('body', $body);
		
		$data = $template->fetch($mastertpl);
		
		if($this->mode == View::MODE_PAGE && $this->contenttype == View::CTYPE_HTML){
			// Replace the </head> tag with the head data from the current page
			// and the </body> with the foot data from the current page.
			// This is needed to be done at this stage because some element in 
			// the template after rendering may add additional script to the head.
			// Also tack on any attributes for the <html> tag.
			$data = str_replace('</head>', self::GetHead() . "\n" . '</head>', $data);
			$data = str_replace('</body>', self::GetFoot() . "\n" . '</body>', $data);
			$data = str_replace('<html', '<html ' . self::GetHTMLAttributes(), $data);
			
			// Provide a way for stylesheets to target this page specifically.
			$url = strtolower(trim(preg_replace('/[^a-z0-9\-]*/i', '', str_replace('/', '-', $this->baseurl)), '-'));
			$data = str_replace('<body', '<body class="page-' . $url . '"', $data);
			
			// If the viewmode is regular and DEVELOPMENT_MODE is enabled, show some possibly useful information now that everything's said and done.
			if(DEVELOPMENT_MODE){
				$debug = '';
				$debug .= '<pre class="xdebug-var-dump">';
				$debug .= "Database Reads: " . Core::DB()->readCount() . "\n";
				$debug .= "Database Writes: " . Core::DB()->writeCount() . "\n";
				//$debug .= "Number of queries: " . DB::Singleton()->counter . "\n";
				$debug .= "Amount of memory used by PHP: " . Core::FormatSize(memory_get_usage()) . "\n";
				$debug .= "Total processing time: " . round(Core::GetProfileTimeTotal(), 4) * 1000 . ' ms' . "\n";
				if(FULL_DEBUG){
					foreach(Core::GetProfileTimes() as $t){
						$debug .= "[" . Core::FormatProfileTime($t['timetotal']) . "] - " . $t['event'] . "\n";
					}
				}
				// Tack on what components are currently installed.
				$debug .= '<b>Available Components</b>' . "\n";
				foreach(Core::GetComponents() as $l => $v){
					$debug .= $v->getName() . ' ' . $v->getVersion() . "\n";
				}

				$debug .= '<b>Query Log</b>' . "\n";
				$debug .= print_r(Core::DB()->queryLog(), true);
				$debug .= '</pre>';
				
				// And append!
				$data = str_replace('</body>', $debug . "\n" . '</body>', $data);
			}
		}
		
		return $data;
	}
	
	/**
	 * Render this view and send all appropriate headers to the browser, (if applicable)
	 * 
	 * @return void 
	 */
	public function render(){
		
		// Before I go about rendering anything, enable UTF-8 to ensure proper i18n!
		if($this->contenttype && $this->contenttype == View::CTYPE_HTML){
			View::AddMeta('http-equiv="Content-Type" content="text/html;charset=UTF-8"');
		}
		
		$data = $this->fetch();
		
		// Be sure to send the content type and status to the browser, (if it's a page)
		if($this->mode == View::MODE_PAGE){
			switch($this->error){
				case View::ERROR_NOERROR:      header('Status: 200 OK', true, $this->error); break;
				case View::ERROR_ACCESSDENIED: header('Status: 403 Forbidden', true, $this->error); break;
				case View::ERROR_NOTFOUND:     header('Status: 404 Not Found', true, $this->error); break;
				case View::ERROR_SERVERERROR:  header('Status: 500 Internal Server Error', true, $this->error); break;
				default:                       header('Status: 500 Internal Server Error', true, $this->error); break; // I don't know WTF happened...
			}

			if($this->contenttype){
				if($this->contenttype == View::CTYPE_HTML) header( 'Content-Type: text/html; charset=UTF-8' );
				else header('Content-Type: ' . $this->contenttype);
			}

			if(DEVELOPMENT_MODE) header('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());
		}
		
		echo $data;
	}
	
	public function addBreadcrumb($title, $link = null){
		
		// Allow a non-resolved link to be passed in.
		if($link !== null && strpos($link, '://') === false) $link = Core::ResolveLink($link);
		
		$this->breadcrumbs[] = array('title' => $title, 'link' => $link);
	}
	
	public function setBreadcrumbs($array){
		// Array should be an array of either link => title keys or pages.
		$this->breadcrumbs = array();
		
		// If null is passed in, just leave them blank.
		// This is useful for implementing completely custom breadcrumbs.
		if(!$array) return;
		
		foreach($array as $k => $v){
			if($v instanceof PageModel) $this->addBreadcrumb($v->get('title'), $v->getResolvedURL());
			else $this->addBreadcrumb($v, $k);
		}
	}
	
	public function getBreadcrumbs(){
		$crumbs = $this->breadcrumbs;
		if($this->title) $crumbs[] = array('title' => $this->title, 'link' => null);
		
		return $crumbs;
		
		//var_dump($crumbs); die();
		
		// Make sure the last element on breadcrumbs is not empty.
		/*if(($n = sizeof($this->breadcrumbs)) && !$this->breadcrumbs[$n-1]['title']){
			$this->breadcrumbs[$n-1]['title'] = $this->title;
		}*/
	}
	
	public function addControl($title, $link, $class = 'edit'){
		$this->controls[] = array('title' => $title, 'link' => Core::ResolveLink($link), 'class' => $class);
	}
	
	/**
	 * Set the access string for this view and do the access checks against the
	 * currently logged in user.
	 * 
	 * If the user does not have access to the resource, $this->error is set to 403.
	 * 
	 * (if you only want to set the access string, please just use $view->access = 'your_string';)
	 * 
	 * @since 2011.08
	 * @param string $accessstring
	 * @return boolean True or false based on access for current user.
	 */
	public function setAccess($accessstring){
		$this->access = $accessstring;
		
		return $this->checkAccess();
	}
	
	/**
	 * Check the access currently set on the view against the currently logged in user.
	 * 
	 * If the user does not have access to the resource, $this->error is set to 403.
	 * 
	 * @since 2011.10
	 * @return boolean 
	 */
	public function checkAccess(){
		// And do some logic to see if the current user can access this resource.
		// This is more of a helper function to Controllers.
		$u = Core::User();
		if($u->checkAccess($this->access)){
			// yay.
			return true;
		}
		else{
			$this->error = View::ERROR_ACCESSDENIED;
			return false;
		}
	}
	
	
	/**
	 * Add a script to the global View object.
	 * 
	 * This will be rendered when a page-level view is rendered.
	 * 
	 * @param string $script
	 * @param string $location
	 */
	public static function AddScript($script, $location = 'head'){
		if(strpos($script, '<script') === false){
			// Resolve the script and wrap it with a script block.
			$script = '<script type="text/javascript" src="' . Core::ResolveAsset($script) . '"></script>';
		}
		
		
		// I can check to see if this script has been loaded before.
		if(in_array($script, self::$HeadScripts)) return;
		if(in_array($script, self::$FootScripts)) return;
		
		// No? alright, add it to the requested location!
		if($location == 'head') self::$HeadScripts[] = $script;
		else self::$FootScripts[] = $script;
	}
	
	/**
	 * Add a linked stylesheet file to the global View object.
	 * 
	 * @param string $link The link of the stylesheet
	 * @param type $media Media to display the stylesheet with.
	 */
	public static function AddStylesheet($link, $media="all"){
		if(strpos($link, '<link') === false){
			// Resolve the script and wrap it with a script block.
			$link = '<link type="text/css" href="' . Core::ResolveAsset($link) . '" media="' . $media . '" rel="stylesheet"/>';
		}
		
		// I can check to see if this script has been loaded before.
		if(!in_array($link, self::$Stylesheets)) self::$Stylesheets[] = $link;
	}
	
	/**
	 * Add an inline style to the global View object.
	 * 
	 * @param string $style The contents of the <style> tag.
	 */
	public static function AddStyle($style){
		if(strpos($style, '<style') === false){
			$style = '<style>' . $style . '</style>';
		}
		
		// I can check to see if this script has been loaded before.
		if(!in_array($style, self::$Stylesheets)) self::$Stylesheets[] = $style;
	}
	
	public static function SetHTMLAttribute($attribute, $value){
		self::$HTMLAttributes[$attribute] = $value;
	}
	
	public static function GetHTMLAttributes($asarray = false){
		$atts = self::$HTMLAttributes;
		
		if($asarray){
			return $atts;
		}
		else{
			$str = '';
			foreach($atts as $k => $v) $str .= " $k=\"" . str_replace('"', '\"', $v) . "\"";
			return trim($str);
		}
	}
	
	public static function GetHead(){
		// Combine the scripts and stylesheets that are set to go in the head.
		$data = array_merge(self::$HeadData, self::$HeadScripts, self::$Stylesheets);
		
		// Throw in the meta information if it's present.
		foreach(self::$MetaData as $k => $v){
			$data[] = '<meta name="' . $k . '" content="' . $v . '"/>';
		}
		
		if(ConfigHandler::Get('/core/markup/minified')){
			$out = implode('', $data);
		}
		else{
			$out = implode("\n", $data);
		}
		
		return trim($out);
	}
	
	public static function GetFoot(){
		$data = self::$FootScripts;
		
		if(ConfigHandler::Get('/core/markup/minified')){
			$out = implode('', $data);
		}
		else{
			$out = implode("\n", $data);
		}
		
		return trim($out);
	}
	
	public static function AddMetaName($key, $value){
		self::$MetaData[$key] = $value;
	}
	
	public static function AddMeta($string){
		if(strpos($string, '<meta') === false) $string = '<meta ' . $string . '/>';
		self::$HeadData[] = $string;
	}
	
}


class ViewException extends Exception{
	
}

