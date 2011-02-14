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

	const ERROR_OTHER = 1;
	const ERROR_NOERROR = 200;
	const ERROR_NOTFOUND = 404;
	const ERROR_ACCESSDENIED = 403;


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

	public function __construct(){
		$this->error = View::ERROR_NOERROR;
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
		return $this->getTemplate()->getVariable($key);
	}

	public function fetchBody(){
		$t = $this->getTemplate();

		return $t->fetch($this->templatename);
	}

	public function render(){
		// Whee!
		$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::GetValue('/core/theme') . '/' . $this->mastertemplate;

		$head = '';
		foreach($this->metas as $k => $v){
			$head .= '<meta name="' . $k . '" content="' . $v . '"/>' . "\n";
		}
		
		// Make sure the last element on breadcrumbs is not empty.
		if(($n = sizeof($this->breadcrumbs)) && !$this->breadcrumbs[$n-1]['title']){
			$this->breadcrumbs[$n-1]['title'] = $this->title;
		}

		$template = new Template();
		$template->setBaseURL('/');
		$template->assign('head', $head);
		$template->assign('title', $this->title);
		$template->assign('body', $this->fetchBody());
		$template->assign('breadcrumbs', $this->breadcrumbs);
		$template->assign('controls', $this->controls);
		$template->assign('foot', '');

		echo $template->fetch($mastertpl);
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
