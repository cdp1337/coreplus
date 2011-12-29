<?php

class PageRequest{
	public $contentType = 'text/html';
	public $method = null;
	public $useragent = null;
	public $uri = null;
	public $uriresolved = null;
	public $protocol = null;
	
	public function __construct(){
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
		
		$view = $this->_page->getView();
		$view->request['contenttype'] = $ctype;
		$view->response['contenttype'] = $ctype; // By default, this can be the same.
		$view->request['method'] = $_SERVER['REQUEST_METHOD'];
		$view->request['useragent'] = $_SERVER['HTTP_USER_AGENT'];
		$view->request['uri'] = $_SERVER['REQUEST_URI'];
		$view->request['uriresolved'] = $uri;
		$view->request['protocol'] = $_SERVER['SERVER_PROTOCOL'];
		
		
		//$this->_page->getView();
		//var_dump($this->_page); die();
	}
}
