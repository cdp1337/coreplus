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
	
	/* Content types for this view */
	const CTYPE_HTML      = 'text/html';
	const CTYPE_PLAIN     = 'text/plain';
	const CTYPE_JSON      = 'application/json';
	const CTYPE_XML       = 'application/xml';


	public $error;
	private $_template;

	private $_params;

	public $baseurl;
	public $title;
	public $metas = array();
	
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
	 * The master template to render this view with.
	 * Should be just the filename, as it will be located automatically.
	 * 
	 * @example index.tpl
	 * @var string
	 */
	public $mastertemplate;
	public $breadcrumbs = array();
	public $controls = array();
	public $mode;
	public $contenttype = 'text/html';
	
	public $jsondata = array();
	
	public $headscripts = array();
	public $headstylesheets = array();

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
		//var_dump($this);die();
		
		// If the content type is set to something other that html, check if that template exists.
		switch($this->contenttype){
			case View::CTYPE_XML:
				$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'xml.tpl', $this->templatename));
				if($ctemp){
					$this->templatename = $ctemp;
					$this->mastertemplate = 'index.xml.tpl';
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
					$this->templatename = false;
					return json_encode($this->jsondata);
				}
				$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'json.tpl', $this->templatename));
				if($ctemp){
					$this->templatename = $ctemp;
					//$this->mastertemplate = 'index.json.tpl';
					$this->mastertemplate = false;
				}
				else{
					$this->contenttype = View::CTYPE_HTML;
				}
				break;
		}
		//var_dump($this->templatename, $this->contenttype);
		
		
		switch($this->mode){
			case View::MODE_PAGE:
			case View::MODE_AJAX:
			case View::MODE_PAGEORAJAX:
				$t = $this->getTemplate();
				return $t->fetch($this->templatename);
				break;
			case View::MODE_WIDGET:
				// This template can be a couple things.
				$tn = Template::ResolveFile(preg_replace('/^pages\//', 'widgets/', $this->templatename));
				if(!$tn) $tn = $this->templatename;
				//var_dump($tn);
				$t = $this->getTemplate();
				//var_dump($t);
				return $t->fetch($tn);
				break;
		}
		
	}

	public function fetch(){
		$body = $this->fetchBody();
		
		// If there's no template, I have nothing to even do!
		if(!$this->mastertemplate) return $body;
		
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
					else $mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/core/theme') . '/' . $this->mastertemplate;
					break;
				case View::MODE_NOOUTPUT:
				case View::MODE_AJAX:
					$mastertpl = false;
					break;
				case View::MODE_PAGE:
					$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/core/theme') . '/' . $this->mastertemplate;
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
			$body = CurrentPage::GetBodyPre() . $body . CurrentPage::GetBodyPost();
		}
		// Widgets need some special variables too.
		//if($this->mode == View::MODE_WIDGET){
		//	//var_dump($this->getVariable('widget')); die();
		//	$template->assign('widget', $this->getVariable('widget'));
		//}
		$template->assign('title', $this->title);
		$template->assign('body', $body);
		
		$data = $template->fetch($mastertpl);
		
		if($this->mode == View::MODE_PAGE && $this->contenttype == 'text/html'){
			// Replace the </head> tag with the head data from the current page
			// and the </body> with the foot data from the current page.
			// This is needed to be done at this stage because some element in 
			// the template after rendering may add additional script to the head.
			// Also tack on any attributes for the <html> tag.
			$data = str_replace('</head>', CurrentPage::GetHead() . "\n" . '</head>', $data);
			$data = str_replace('</body>', CurrentPage::GetFoot() . "\n" . '</body>', $data);
			$data = str_replace('<html', '<html ' . CurrentPage::GetHTMLAttributes(), $data);
		}
		
		return $data;
	}
	
	public function render(){
		echo $this->fetch();
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
	
}


class ViewException extends Exception{
	
}

