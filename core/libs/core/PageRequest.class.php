<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
 * @since 1.9
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

class PageRequest {

	// NOT supported
	// const METHOD_OPTIONS = 'OPTIONS';

	// NOT supported
	// const METHOD_TRACE  = 'TRACE';
	const METHOD_HEAD   = 'HEAD';
	const METHOD_GET    = 'GET';
	const METHOD_POST   = 'POST';
	const METHOD_PUT    = 'PUT';
	const METHOD_PUSH   = 'PUSH';
	const METHOD_DELETE = 'DELETE';

	/**
	 * Array of content types accepted by the browser.
	 *
	 * @var array
	 */
	public $contentTypes = array();

	/**
	 * Array of languages accepted by the browser.
	 *
	 * @var array
	 */
	// @todo Complete this
	//public $acceptLanguages = array();

	/**
	 * Request method, one of the PageRequest::METHOD_* strings.
	 *
	 * @var string
	 */
	public $method = null;

	/**
	 * Full string of the incoming user agent.
	 *
	 * @var string
	 */
	public $useragent = null;
	public $uri = null;
	public $uriresolved = null;
	public $protocol = null;

	public $parameters = array();

	/**
	 * Content type requested
	 *
	 * @var string
	 */
	public $ctype = View::CTYPE_HTML;

	/**
	 * The cached pagemodel for this request.
	 * @var PageModel
	 */
	private $_pagemodel = null;

	public function __construct($uri = '') {

		$this->uri = $uri;

		// Resolve the URI, this will ensure a usable, valid path.

		// If blank, default to '/' (should be root url)
		if (!$uri) $uri = ROOT_WDIR;

		// Now I can trim off the prefix, since that's not needed in deciding the path.
		$uri = substr($uri, strlen(ROOT_WDIR));

		// Split the string on the '?'.  Obviously anything after are parameters.
		if (($_qpos = strpos($uri, '?')) !== false) $uri = substr($uri, 0, $_qpos);

		// the URI should start with a '/'.
		if ($uri{0} != '/') $uri = '/' . $uri;

		// If the useragent requested a specifc mode type, remember that and set it for the page.
		if (preg_match('/\.[a-z]{3,4}$/i', $uri)) {
			$ctype = strtolower(preg_replace('/^.*\.([a-z]{3,4})$/i', '\1', $uri));
			$uri   = substr($uri, 0, -1 - strlen($ctype));
		}
		else {
			$ctype = 'html';
		}

		$this->uriresolved = $uri;
		$this->protocol    = $_SERVER['SERVER_PROTOCOL'];
		// Specified with prepending ".xml|.json,etc" to the resource.
		switch ($ctype) {
			case 'xml':
				$this->ctype = View::CTYPE_XML;
				break;
			case 'json':
				$this->ctype = View::CTYPE_JSON;
				break;
			default:
				$this->ctype = View::CTYPE_HTML;
				break;
		}

		$this->_resolveMethod();
		$this->_resolveAcceptHeader();
		$this->_resolveUAHeader();

		if (is_array($_GET)) {
			foreach ($_GET as $k => $v) {
				if (is_numeric($k)) continue;
				$this->parameters[$k] = $v;
			}
		}

		return;

		// Trim off anything after the first & if present.
		//if(strpos($uri, '&') !== false) $uri = substr($uri, 0, strpos($uri, '&'));

		$p = PageModel::Find(
			array('rewriteurl' => $uri,
			      'fuzzy'      => 0), 1
		);

		// Split this URL, it'll be used somewhere.


		// The core information can be retrieved from the PageModel's logic.
		$pagedat = PageModel::SplitBaseURL($uri);
		var_dump($pagedat, $_GET);
		die();


		if ($p) {
			// :) Found it
			$this->pagemodel = $p;
		}
		elseif ($pagedat) {
			// Is this even a valid controller?
			// This will allow a page to be called with it being in the pages database.
			$p = new PageModel();
			$p->set('baseurl', $uri);
			$p->set('rewriteurl', $uri);
			$this->pagemodel = $p;
		}
		else {
			// No page in the database and no valid controller... sigh
			return false;
		}

		//var_dump($p); die();

		// Make sure all the parameters from both standard GET and core parameters are tacked on.
		if ($pagedat && $pagedat['parameters']) {
			foreach ($pagedat['parameters'] as $k => $v) {
				$this->pagemodel->setParameter($k, $v);
			}
		}
		if (is_array($_GET)) {
			foreach ($_GET as $k => $v) {
				if (is_numeric($k)) continue;
				$this->pagemodel->setParameter($k, $v);
			}
		}

		// Some pages may support dynamic content types from the getgo.
		// @todo Should the $_SERVER['HTTP_ACCEPT'] flag be used here?
		switch ($ctype) {
			case 'xml':
				$ctype = View::CTYPE_XML;
				break;
			case 'json':
				$ctype = View::CTYPE_JSON;
				break;
			default:
				$ctype = View::CTYPE_HTML;
				break;
		}
	}

	/**
	 * Check to see if the page request prefers a particular type of content type request.
	 * This is useful for allowing JSON requests on a per-case basis in the controller.
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function prefersContentType($type) {
		// First, find the current.
		$current     = 0;
		$currentmain = substr($this->ctype, 0, strpos($this->ctype, '/'));
		foreach ($this->contentTypes as $t) {
			if ($t['type'] == $this->ctype || ($t['type'] == $t['group'] . '/*' && $t['group'] == $currentmain)) {
				$current = max($current, $t['weight']);
			}
		}

		// Now that I have the current weight...
		$typeweight = 0;
		$typemain   = substr($type, 0, strpos($type, '/'));
		foreach ($this->contentTypes as $t) {
			if ($t['type'] == $type || ($t['type'] == $t['group'] . '/*' && $t['group'] == $typemain)) {
				$typeweight = max($typeweight, $t['weight']);
			}
		}

		// Now I have the weight values, (if any), of both current and requested.
		return ($typeweight > $current);
	}

	/**
	 * Get an array of all the parts of this request, including:
	 * 'controller', 'method', 'parameters', 'baseurl', 'rewriteurl'
	 *
	 * @return array
	 */
	public function splitParts() {
		$ret = PageModel::SplitBaseURL($this->uriresolved);

		// No?
		if (!$ret) {
			$ret = array(
				'controller' => null,
				'method'     => null,
				'parameters' => null,
				'baseurl'    => null,
				'rewriteurl' => null
			);
		}

		// Tack on the parameters
		if ($ret['parameters'] === null) $ret['parameters'] = array();
		$ret['parameters'] = array_merge($ret['parameters'], $this->parameters);

		return $ret;
	}

	/**
	 * Shortcut function to return just the base url
	 *
	 * Utilizes the SplitBaseURL method.
	 *
	 * @return string
	 */
	public function getBaseURL() {
		$parts = $this->splitParts();
		return $parts['baseurl'];
	}

	/**
	 * Execute the controller and method this page request points to.
	 *
	 * @return View
	 */
	public function execute() {
		$pagedat = $this->splitParts();

		/// A few sanity/security checks for the controller's sake.

		// The controller must exist first!
		// (note, the SplitParts logic already takes care of the "Is this a valid controller" logic)
		if (!$pagedat['controller']) {
			$view        = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}

		// Any method that starts with a "_" is an internal-only method!
		if ($pagedat['method']{0} == '_') {
			$view        = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}

		// It also must be a part of the class... obviously
		if (!method_exists($pagedat['controller'], $pagedat['method'])) {
			$view        = new View();
			$view->error = View::ERROR_NOTFOUND;
			return $view;
		}

		// This will be a Controller object.
		$c = Controller_2_1::Factory($pagedat['controller']);

		// The main page object.
		$page = $this->getPageModel();

		// Check the access string first, (if there is one)
		if ($c->accessstring !== null) {
			// Update the page's access string, (just in case it's saved at the end of execution)
			$page->set('access', $c->accessstring);

			// And if the user doesn't have access to it...
			if (!\Core\user()->checkAccess($c->accessstring)) {
				$view        = new View();
				$view->error = View::ERROR_ACCESSDENIED;
				return $view;
			}
		}

		$return = call_user_func(array($c, $pagedat['method']));
		if (is_int($return)) {
			// A generic error code was returned.  Create a View with that code and return that instead.
			$view        = new View();
			$view->error = $return;
			return $view;
		}
		elseif ($return === null) {
			// Hopefully it's setup!
			$return = $c->getView();
		}
		// No else needed, else it's a valid object.

		// Load some of the page information into the view now!

		foreach ($page->getMetas() as $key => $val) {
			if ($val) {
				View::AddMetaName($key, $val);
			}
		}

		if ($page->get('title') !== null) $return->title = $page->get('title');

		$parents = array();
		foreach ($page->getParentTree() as $parent) {
			$parents[] = array(
				'title' => $parent->get('title'),
				'link'  => $parent->getResolvedURL()
			);
		}
		$return->breadcrumbs = array_merge($parents, $return->breadcrumbs);

		// Try to guess the templatename if it wasn't set.
		if ($return->error == View::ERROR_NOERROR && $return->contenttype == View::CTYPE_HTML && $return->templatename === null) {
			$cnameshort           = (strpos($pagedat['controller'], 'Controller') == strlen($pagedat['controller']) - 10) ? substr($pagedat['controller'], 0, -10) : $pagedat['controller'];
			$return->templatename = strtolower('/pages/' . $cnameshort . '/' . $pagedat['method'] . '.tpl');
		}
		elseif ($return->error == View::ERROR_NOERROR && $return->contenttype == View::CTYPE_XML && $return->templatename === null) {
			$cnameshort           = (strpos($pagedat['controller'], 'Controller') == strlen($pagedat['controller']) - 10) ? substr($pagedat['controller'], 0, -10) : $pagedat['controller'];
			$return->templatename = Template::ResolveFile(strtolower('pages/' . $cnameshort . '/' . $pagedat['method'] . '.xml.tpl'));
		}

		// Master template set in the database?
		if ($page->get('theme_template')) {
			$return->mastertemplate = $page->get('theme_template');
		}


		// Make sure I update any existing page now that the controller has ran.
		if ($page->exists() && $return->error == View::ERROR_NOERROR) {
			$page->save();
		}

		return $return;
	}

	public function setParameters($params) {
		$this->parameters = $params;
	}

	public function getParameters() {
		$data = $this->splitParts();
		return $data['parameters'];
	}

	public function getParameter($key) {
		$data = $this->splitParts();
		return (array_key_exists($key, $data['parameters'])) ? $data['parameters'][$key] : null;
	}

	/**
	 * Get the page model for the current page.
	 *
	 * @return PageModel
	 */
	public function getPageModel() {
		if ($this->_pagemodel === null) {
			$uri = $this->uriresolved;

			$p = PageModel::Find(
				array('rewriteurl' => $uri,
				      'fuzzy'      => 0), 1
			);

			// Split this URL, it'll be used somewhere.
			$pagedat = $this->splitParts();

			if ($p) {
				// :) Found it
				$this->_pagemodel = $p;
			}
			elseif ($pagedat) {
				// Is this even a valid controller?
				// This will allow a page to be called with it being in the pages database.
				$p = new PageModel();
				$p->set('baseurl', $uri);
				$p->set('rewriteurl', $uri);
				$this->_pagemodel = $p;
			}
			else {
				// No page in the database and no valid controller... sigh
				return false;
			}

			//var_dump($p); die();

			// Make sure all the parameters from both standard GET and core parameters are tacked on.
			if ($pagedat && $pagedat['parameters']) {
				foreach ($pagedat['parameters'] as $k => $v) {
					$this->_pagemodel->setParameter($k, $v);
				}
			}
			if (is_array($_GET)) {
				foreach ($_GET as $k => $v) {
					if (is_numeric($k)) continue;
					$this->_pagemodel->setParameter($k, $v);
				}
			}
		}

		return $this->_pagemodel;
	}

	/**
	 * Simple check to see if the page request is a POST method.
	 *
	 * Returns true if it is POST, false if anything else.
	 *
	 * @return bool
	 */
	public function isPost() {
		return ($this->method == PageRequest::METHOD_POST);
	}


	private function _resolveMethod() {
		// Make sure it's a valid METHOD... don't know what else it could be, but...
		switch ($_SERVER['REQUEST_METHOD']) {
			case self::METHOD_DELETE:
			case self::METHOD_GET:
			case self::METHOD_HEAD:
			case self::METHOD_POST:
			case self::METHOD_PUSH:
			case self::METHOD_PUT:
				$this->method = $_SERVER['REQUEST_METHOD'];
				break;
			default:
				$this->method = self::METHOD_GET;
		}
	}

	private function _resolveAcceptHeader() {
		// I need to ensure there's at least a default.
		$header = (isset($_SERVER['HTTP_ACCEPT'])) ? $_SERVER['HTTP_ACCEPT'] : 'text/html';

		// As per the Accept HTTP 1.1 spec, all accepts MUST be separated with a comma.
		$header = explode(',', $header);

		// Clear the array
		$this->contentTypes = array();

		// There are a couple special-case exceptions that must go first.
		if ($this->ctype == View::CTYPE_JSON) {
			// JSON is dependent on either the config being true or an appropriate header.
			if (ALLOW_NONXHR_JSON || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
				$this->contentTypes[] = array('type'   => View::CTYPE_JSON,
				                              'weight' => 1.0);
			}
			else {
				// DENIED :p
				$this->ctype = View::CTYPE_HTML;
			}
		}

		// And set each one.
		foreach ($header as $h) {
			if (strpos($h, ';') === false) {
				$weight  = 1.0; // Do 1.0 to ensure it's parsed as a float and not an int.
				$content = $h;
			}
			else {
				list($content, $weight) = explode(';', $h);
				// Trim off the "q=" bit.
				$weight = floatval(substr($weight, 3));
			}

			$this->contentTypes[] = array(
				'type'   => $content,
				'weight' => $weight
			);
		}

		// And finally, run through all the content types and make them a little easier to parse.
		foreach ($this->contentTypes as $k => $v) {
			$this->contentTypes[$k]['group'] = substr($v['type'], 0, strpos($v['type'], '/'));
		}
	}

	private function _resolveUAHeader() {
		$ua              = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$this->useragent = $ua;
	}


	/**
	 * The core page request instantiated from the browser.
	 *
	 * @return PageRequest
	 */
	public static function GetSystemRequest() {
		static $instance = null;
		if ($instance === null) {
			$instance = new PageRequest($_SERVER['REQUEST_URI']);
		}
		return $instance;
	}
}
