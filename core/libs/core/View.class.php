<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
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
	const ERROR_ACCESSDENIED = 403;
	const ERROR_NOTFOUND     = 404;
	const ERROR_SERVERERROR  = 500;
	
	/* Modes for handling the rendering of the view */
	/**
	 * Handle the rendering of this view as a normal page.
	 */
	const MODE_PAGE       = 'page';
	const MODE_WIDGET     = 'widget';
	const MODE_NOOUTPUT   = 'nooutput';
	const MODE_AJAX       = 'ajax';
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
	public $access;
	public $templatename;
	public $mastertemplate;
	public $breadcrumbs = array();
	public $controls = array();
	public $mode;
	public $contenttype = 'text/html';
	
	
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

	public function assignVariable($key, $val){
		$this->getTemplate()->assign($key, $val);
	}

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
		//var_dump($this);
		
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
					else $mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::GetValue('/core/theme') . '/' . $this->mastertemplate;
					break;
				case View::MODE_NOOUTPUT:
				case View::MODE_AJAX:
					$mastertpl = false;
					break;
				case View::MODE_PAGE:
					$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::GetValue('/core/theme') . '/' . $this->mastertemplate;
					break;
				case View::MODE_WIDGET:
					$mastertpl = Template::ResolveFile('widgetcontainers/' . $this->mastertemplate);
					break;
			}
		}
		
		// If there's no template, I have nothing to even do!
		if(!$mastertpl) return $body;
		
		
		// @todo Handle the metadata.
		// Tack on the meta data.
		//foreach($this->metas as $k => $v){
		//	$head .= '<meta name="' . $k . '" content="' . $v . '"/>' . "\n";
		//}
		
		// Make sure the last element on breadcrumbs is not empty.
		if(($n = sizeof($this->breadcrumbs)) && !$this->breadcrumbs[$n-1]['title']){
			$this->breadcrumbs[$n-1]['title'] = $this->title;
		}
		
		$template = new Template();
		$template->setBaseURL('/');
		// Page-level views have some special variables.
		if($this->mode == View::MODE_PAGE){
			$template->assign('breadcrumbs', $this->breadcrumbs);
			$template->assign('controls', $this->controls);
			$template->assign('messages', Core::GetMessages());
		}
		// Widgets need some special variables too.
		if($this->mode == View::MODE_WIDGET){
			//var_dump($this->getVariable('widget')); die();
			$template->assign('widget', $this->getVariable('widget'));
		}
		$template->assign('title', $this->title);
		$template->assign('body', $body);
		
		$data = $template->fetch($mastertpl);
		
		if($this->mode == View::MODE_PAGE && $this->contenttype == 'text/html'){
			// Replace the </head> tag with the head data from the current page
			// and the </body> with the foot data from the current page.
			// This is needed to be done at this stage because some element in the template after rendering may add additional script to the head.
			$data = str_replace('</head>', CurrentPage::GetHead() . "\n" . '</head>', $data);
			$data = str_replace('</body>', CurrentPage::GetFoot() . "\n" . '</body>', $data);
		}
		
		return $data;
	}
	
	public function render(){
		echo $this->fetch();
	}
	
	public function addBreadcrumb($title, $link = null){
		$this->breadcrumbs[] = array('title' => $title, 'link' => $link);
	}
	
	public function setBreadcrumbs($array){
		// Array should be an array of either link => title keys or pages.
		$this->breadcrumbs = array();
		foreach($array as $k => $v){
			if($v instanceof PageModel) $this->addBreadcrumb($v->get('title'), $v->getResolvedURL());
			else $this->addBreadcrumb($v, $k);
		}
	}
	
	public function addControl($title, $link, $class = 'edit'){
		$this->controls[] = array('title' => $title, 'link' => Core::ResolveLink($link), 'class' => $class);
	}
}


class ViewException extends Exception{
	
}