<?php
/**
 * Provides all elements required to connect the Controller's data and logic back to the browser in the necessary format.
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
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
	const MODE_PAGE = 'page';
	/**
	 * Widget page inside a given widget container
	 */
	const MODE_WIDGET = 'widget';
	/**
	 * No automatic output whatsoever
	 * Useful for file downloads and completely custom views.
	 */
	const MODE_NOOUTPUT = 'nooutput';
	/**
	 * Render a template, but do not wrap in a master page template
	 * This is useful if you want the power of a template, but for an
	 * ajax or otherwise custom response.
	 */
	const MODE_AJAX = 'ajax';
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
	const METHOD_GET = 'GET';
	/**
	 * Request method for standard form submission
	 */
	const METHOD_POST = 'POST';
	/**
	 * @todo Not supported
	 */
	const METHOD_PUT = 'PUT';
	/**
	 * @todo Not supported
	 */
	const METHOD_HEAD = 'HEAD';
	/**
	 * @todo Not supported
	 */
	const METHOD_DELETE = 'DELETE';

	/* Content types for this view */
	const CTYPE_HTML  = 'text/html';
	const CTYPE_PLAIN = 'text/plain';
	const CTYPE_JSON  = 'application/json';
	const CTYPE_XML   = 'application/xml';
	const CTYPE_ICS   = 'text/calendar';


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

	/**
	 * The controls for ths view
	 *
	 * @var ViewControls
	 */
	public $controls;

	/**
	 * The mode of this View.
	 * Greatly affects the rendering result, since this can be a full page or a single widget.
	 *
	 * MUST be one of the valid View::MODE_* strings!
	 *
	 * @var string
	 */
	public $mode;

	/**
	 * An array, object, string, or other data that is sent to the browser via json_encode if content type is set to JSON.
	 *
	 * @var mixed
	 */
	public $jsondata = null;

	/**
	 * Set this to a non-null value to set the http-equiv="last-modified" metatag.
	 *
	 * @var null|int
	 */
	public $updated = null;

	/**
	 * Any "other" string to put into the head.  This can include <link> tags, or any other tag not defined otherwise.
	 *
	 * @var array
	 */
	public $head = array();

	/**
	 * Associative array of meta data for this view.
	 *
	 * @var array
	 */
	public $meta = array();

	/**
	 * Array of scripts to load in the head and foot of the document, respectively.
	 *
	 * @var array
	 */
	public $scripts = array('head' => array(), 'foot' => array());

	/**
	 * Array of stylesheets to load in the head of the document.
	 *
	 * @var array
	 */
	public $stylesheets = array();

	/**
	 * If you wish to override the canonical URL for this page, it can be done so with this variable.
	 * If left null, it will be populated automatically with the URL resolution system.
	 *
	 * By setting this variable to false, the canonical link is ignored and not rendered to the browser.
	 *
	 * This variable is used to set the appropriate meta data, ie: link type="canonical" and meta key="og:url".
	 *
	 * @var null|false|string
	 */
	public $canonicalurl = null;

	/**
	 * Set to true to allow the page template to run with errors.
	 *
	 * By default, all errors are caught and the system template overrides the page template. This is
	 * a security precaution to prevent the template from being rendered when access is denied.
	 *
	 * If HOWEVER that is the preferred behaviour, ie: user logins, set this to true to allow the page template
	 * to be used in rendering.
	 *
	 * @var bool
	 */
	public $allowerrors = false;

	/**
	 * Set to true to require this page to be viewed as SSL.
	 * Obviously if SSL is not enabled on this site, this has no effect.
	 *
	 * @var bool
	 */
	public $ssl = false;

	/**
	 * Set to false to skip this View from being recorded in analytical tools and navigation.  Useful for JSON or POST pages.
	 *
	 * @var bool
	 */
	public $record = true;

	/**
	 * @deprecated 2012.06.25 cpowell The non-static version should be better.
	 * @var array
	 */
	public static $MetaData = array();
	/**
	 * @deprecated 2012.06.25 cpowell The non-static version should be better.
	 * @var array
	 */
	public static $HeadScripts = array();
	/**
	 * @deprecated 2012.06.25 cpowell The non-static version should be better.
	 * @var array
	 */
	public static $FootScripts = array();
	/**
	 * @deprecated 2012.06.25 cpowell The non-static version should be better.
	 * @var array
	 */
	public static $Stylesheets = array();
	public static $HTMLAttributes = array();

	/**
	 * @deprecated 2012.06.25 cpowell The non-static version should be better.
	 * @var array
	 */
	public static $HeadData = array();

	public function __construct() {
		$this->error = View::ERROR_NOERROR;
		$this->mode  = View::MODE_PAGE;
		$this->controls = new ViewControls();
		$this->meta = new ViewMetas();
	}

	public function setParameters($params) {
		$this->_params = $params;
	}

	public function getParameters() {
		if (!$this->_params) {
			$this->_params = array();
		}

		return $this->_params;
	}

	public function getParameter($key) {
		$p = $this->getParameters();
		return (array_key_exists($key, $p)) ? $p[$key] : null;
	}

	/**
	 *
	 * @return Template
	 */
	public function getTemplate() {
		if (!$this->_template) {
			$this->_template = new Template();
			$this->_template->setBaseURL($this->baseurl);
		}

		return $this->_template;
	}

	/**
	 * Override a template, useful for forcing a different template type for this view.
	 *
	 * @param $template TemplateInterface
	 */
	public function overrideTemplate($template){
		if(!is_a($template, 'TemplateInterface')){
			return false;
		}

		if($template == $this->_template){
			return false;
		}

		if($this->_template !== null){
			foreach($this->_template->getTemplateVars() as $k => $v){
				$template->assign($k, $v);
			}
		}

		$this->_template = $template;
	}

	/**
	 * Assign a variable to this view
	 *
	 * @param $key string
	 * @param $val mixed
	 */
	public function assign($key, $val) {
		$this->getTemplate()->assign($key, $val);
	}

	/**
	 * Alias of assign
	 *
	 * @param $key string
	 * @param $val mixed
	 */
	public function assignVariable($key, $val) {
		$this->assign($key, $val);
	}

	/**
	 * Get a variable that was set with "assign()"
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getVariable($key) {
		// Damn smarty and its being more difficult...
		$v = $this->getTemplate()->getVariable($key);
		return ($v) ? $v->value : null;
	}

	public function fetchBody() {
		// If there is set to be no system content, don't even bother with anything here!
		if ($this->mode == View::MODE_NOOUTPUT) {
			return null;
		}

		// Resolve the template based on the error code. (if present)
		if ($this->error != View::ERROR_NOERROR && !$this->allowerrors) {
			// Update some information in the view.
			// Transpose some useful data for it.
			//$view->baseurl = '/Error/Error' . $view->error;
			//$view->setParameters(array());
			$tmpl = '/pages/error/error' . $this->error . '.tpl';
			//$mastertmpl = ConfigHandler::Get('/theme/default_template');
		}
		else {
			$tmpl = $this->templatename;
			//$mastertmpl = 
		}

		// If the content type is set to something other that html, check if that template exists.
		switch ($this->contenttype) {
			case View::CTYPE_XML:
				// Already resolved?
				if (strpos($tmpl, ROOT_PDIR) === 0 && strpos($tmpl, '.xml.tpl') !== false) {
					$this->mastertemplate = false;
				}
				else {
					$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'xml.tpl', $tmpl));
					if ($ctemp) {
						$tmpl                 = $ctemp;
						$this->mastertemplate = false;
					}
					else {
						$this->contenttype = View::CTYPE_HTML;
					}
				}
				break;
			case View::CTYPE_ICS:
				// Already resolved?
				if(strpos($tmpl, ROOT_PDIR) === 0 && strpos($tmpl, '.ics.tpl') !== false){
					$this->mastertemplate = false;
				}
				else{
					$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'ics.tpl', $tmpl));
					if($ctemp){
						$tmpl = $ctemp;
						$this->mastertemplate = false;
					}
					else{
						$this->contenttype = View::CTYPE_HTML;
					}
				}

				// TESTING
				//$this->contenttype = View::CTYPE_HTML;
				break;
			case View::CTYPE_JSON:
				// Did the controller send data to this view directly?
				// (because JSON supports raw data ^_^ )
				if ($this->jsondata !== null) {
					$this->mastertemplate = false;
					$tmpl                 = false;
					return json_encode($this->jsondata);
				}
				$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'json.tpl', $tmpl));
				if ($ctemp) {
					$tmpl                 = $ctemp;
					$this->mastertemplate = false;
				}
				else {
					$this->contenttype = View::CTYPE_HTML;
				}
				break;
		}

		if (!$tmpl && $this->templatename == '') {
			throw new Exception('Please set the variable "templatename" on the page view.');
		}


		// Allow pages requested by anonymous users to be cached if there are no errors.
		// @todo Implement caching like this.... only that works :/
		if(false && $this->error == View::ERROR_NOERROR && !\Core\user()->exists() && $this->updated){
			// Yay, see if there's a cached version available!
			$cacheable = true;
			$key = 'page-body' . str_replace('/', '-', $this->baseurl);
			$cache = Cache::GetSystemCache()->get($key, (60*30));
			if($cache){
				//var_dump($cache, $key); die();
				// Check the updated timestamp.
				if($this->updated == $cache['updated']){
					return $cache['html'];
				}
			}
		}
		else{
			$cacheable = false;
		}



		switch ($this->mode) {
			case View::MODE_PAGE:
			case View::MODE_AJAX:
			case View::MODE_PAGEORAJAX:
				$t = $this->getTemplate();
				$html = $t->fetch($tmpl);
				break;
			case View::MODE_WIDGET:
				// This template can be a couple things.
				$tn = Template::ResolveFile(preg_replace(':^[/]{0,1}pages/:', '/widgets/', $tmpl));
				if (!$tn) $tn = $tmpl;

				$t = $this->getTemplate();
				//var_dump($t);
				$html = $t->fetch($tn);
				break;
		}

		// Is it cacheable?
		if($cacheable){
			Cache::GetSystemCache()->set($key, array('updated' => $this->updated, 'html' => $html), (60 * 30));
		}
		return $html;
	}

	/**
	 * Fetch this view as an HTML string.
	 * @return mixed|null|string
	 */
	public function fetch() {
		$body = $this->fetchBody();

		// If there's no template, I have nothing to even do!
		if ($this->mastertemplate === false) {
			return $body;
		}
		// Else if it's null, it's just not set yet :p
		elseif ($this->mastertemplate === null) {
			$this->mastertemplate = ConfigHandler::Get('/theme/default_template');
		}

		// Whee!
		//var_dump($this->templatename, Template::ResolveFile($this->templatename));
		// Content types take priority on controlling the master template.
		if ($this->contenttype == View::CTYPE_JSON) {
			$mastertpl = false;
		}
		else {
			// Master template depends on the render mode.
			switch ($this->mode) {
				case View::MODE_PAGEORAJAX:
					if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
						$mastertpl = false;
						$this->mode = View::MODE_AJAX;
					}
					else{
						$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/skins/' . $this->mastertemplate;
						$this->mode = View::MODE_PAGE;
					}
					break;
				case View::MODE_NOOUTPUT:
				case View::MODE_AJAX:
					$mastertpl = false;
					break;
				case View::MODE_PAGE:
					$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/skins/' . $this->mastertemplate;
					break;
				case View::MODE_WIDGET:
					$mastertpl = Template::ResolveFile('widgetcontainers/' . $this->mastertemplate);
					break;
			}
		}

		// If there's *still* no template, I still have nothing to do.
		if (!$mastertpl) return $body;


		$template = new Template();
		$template->setBaseURL('/');
		// Page-level views have some special variables.
		if ($this->mode == View::MODE_PAGE) {
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


		// This logic is needed for the SEO title, since that's usually completely human unfriendly.
		if(isset($this->meta['title']) && $this->meta['title']){
			$template->assign('seotitle', $this->meta['title']);
		}
		else{
			$template->assign('seotitle', $this->title);
		}
		$template->assign('title', $this->title);
		$template->assign('body', $body);

		try{
			$data = $template->fetch($mastertpl);
		}
		catch(SmartyException $e){
			$this->error = View::ERROR_SERVERERROR;
			error_log('[view error]');
			error_log('Template name: [' . $mastertpl . ']');
			error_log($e->getMessage());
			require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
			die();
		}
		catch(TemplateException $e){
			$this->error = View::ERROR_SERVERERROR;
			error_log('[view error]');
			error_log('Template name: [' . $mastertpl . ']');
			error_log($e->getMessage());
			require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
			die();
		}

		if ($this->mode == View::MODE_PAGE && $this->contenttype == View::CTYPE_HTML) {
			// Inform other elements that the page is just about to be rendered.
			HookHandler::DispatchHook('/core/page/rendering', $this);

			// Metadata!  w00t

			// Replace the </head> tag with the head data from the current page
			// and the </body> with the foot data from the current page.
			// This is needed to be done at this stage because some element in
			// the template after rendering may add additional script to the head.
			// Also tack on any attributes for the <html> tag.
			if(preg_match('#</head>#i', $data)){
				// I need to do preg_replace because I only want to replace the FIRST instance of </head>
				$data = preg_replace('#</head>#i', $this->getHeadContent() . "\n" . '</head>', $data, 1);
			}
			if(preg_match('#</body>#i', $data)){
				// I need to use strrpos because I only want the LAST instance of </body>
				$match = strrpos($data, '</body>');

				$foot = $this->getFootContent();

				// If the viewmode is regular and DEVELOPMENT_MODE is enabled, show some possibly useful information now that everything's said and done.
				if (DEVELOPMENT_MODE) {
					$debug = '';
					$debug .= '<pre class="xdebug-var-dump screen">';
					$debug .= '<b>Template Information</b>' . "\n";
					$debug .= 'Base URL: ' . $this->baseurl . "\n";
					$debug .= 'Template Used: ' . $this->templatename . "\n";
					$debug .= "\n" . '<b>Performance Information</b>' . "\n";
					$debug .= "Database Reads: " . Core::DB()->readCount() . "\n";
					$debug .= "Database Writes: " . Core::DB()->writeCount() . "\n";
					//$debug .= "Number of queries: " . DB::Singleton()->counter . "\n";
					$debug .= "Amount of memory used by PHP: " . Core::FormatSize(memory_get_usage()) . "\n";
					$debug .= "Total processing time: " . round(Core::GetProfileTimeTotal(), 4) * 1000 . ' ms' . "\n";
					if (FULL_DEBUG) {
						foreach (Core::GetProfileTimes() as $t) {
							$debug .= "[" . Core::FormatProfileTime($t['timetotal']) . "] - " . $t['event'] . "\n";
						}
					}
					// Tack on what components are currently installed.
					$debug .= "\n" . '<b>Available Components</b>' . "\n";
					foreach (Core::GetComponents() as $l => $v) {
						$debug .= ($v->isEnabled() ? '[<span style="color:green;">Enabled</span>]' : '[<span style="color:red;">Disabled</span>]').
							$v->getName() . ' ' . $v->getVersion() . "\n";
					}

					// I wanna see what hooks are registered too!
					$debug .= "\n" . '<b>Registered Hooks</b>' . "\n";
					foreach(HookHandler::GetAllHooks() as $hook){
						/** @var $hook Hook */
						$debug .= $hook->name;
						if($hook->description) $debug .= ' <i> - ' . $hook->description . '</i>';
						$debug .= "\n" . '<span style="color:#999;">Return expected: ' . $hook->returnType . '</span>';
						$debug .= "\n" . '<span style="color:#999;">Attached by ' . $hook->getBindingCount() . ' binding(s).</span>' . "\n\n";
					}

					// I want to see how many files were included.
					$debug .= "\n" . '<b>Included Files</b>' . "\n";
					$debug .= 'Number: ' . sizeof(get_included_files()) . "\n";
					$debug .= implode("\n", get_included_files()) . "\n";

					$debug .= "\n" . '<b>Query Log</b>' . "\n";
					$debug .= print_r(Core::DB()->queryLog(), true);
					$debug .= '</pre>';

					// And append!
					$foot .= "\n" . $debug;
				}
				$data = substr_replace($data, $foot . "\n" . '</body>', $match, 7);
			}
			$data = preg_replace('#<html#', '<html ' . self::GetHTMLAttributes(), $data, 1);

			// Provide a way for stylesheets to target this page specifically.
			$url  = strtolower(trim(preg_replace('/[^a-z0-9\-]*/i', '', str_replace('/', '-', $this->baseurl)), '-'));
			$bodyclass = 'page-' . $url;
			// Merge them.
			if(preg_match('/<body[^>]*>/', $data, $matches)){
				// body is $matches[0].
				$fullbody = $matches[0];
				if($fullbody == '<body>'){
					$body = '<body class="' . $bodyclass . '">';
				}
				elseif(strpos($fullbody, 'class=') === false){
					// Almost as easy, other elements but no class.
					$body = substr($fullbody, 0, -1) . ' class="' . $bodyclass . '">';
				}
				else{
					// parsing HTML is far easier with XML objects.
					$node = new SimpleXMLElement($fullbody . '</body>');
					$body = '<body';
					foreach($node->attributes() as $k => $v){
						if($k == 'class'){
							$body .= ' ' . $k . '="' . $bodyclass . ' ' . $v . '"';
						}
						else{
							$body .= ' ' . $k . '="' . $v . '"';
						}
					}
					$body .= '>';
				}

				// And replace!
				$data = preg_replace('#<body[^>]*>#', $body, $data, 1);
			}
		}

		return $data;
	}

	/**
	 * Render this view and send all appropriate headers to the browser, (if applicable)
	 *
	 * @return void
	 */
	public function render() {

		// Before I go about rendering anything, enable UTF-8 to ensure proper i18n!
		if ($this->contenttype && $this->contenttype == View::CTYPE_HTML) {
			View::AddMeta('http-equiv="Content-Type" content="text/html;charset=UTF-8"');
		}

		$data = $this->fetch();

		// Be sure to send the content type and status to the browser, (if it's a page)
		if ($this->mode == View::MODE_PAGE || $this->mode == View::MODE_PAGEORAJAX || $this->mode == View::MODE_AJAX) {
			switch ($this->error) {
				case View::ERROR_NOERROR:
					header('Status: 200 OK', true, $this->error);
					break;
				case View::ERROR_ACCESSDENIED:
					header('Status: 403 Forbidden', true, $this->error);
					break;
				case View::ERROR_NOTFOUND:
					header('Status: 404 Not Found', true, $this->error);
					break;
				case View::ERROR_SERVERERROR:
					header('Status: 500 Internal Server Error', true, $this->error);
					break;
				default:
					header('Status: 500 Internal Server Error', true, $this->error);
					break; // I don't know WTF happened...
			}

			if ($this->contenttype) {
				if ($this->contenttype == View::CTYPE_HTML) header('Content-Type: text/html; charset=UTF-8');
				else header('Content-Type: ' . $this->contenttype);
			}
			//mb_internal_encoding('utf-8');

			header('X-Content-Encoded-By: Core Plus ' . (DEVELOPMENT_MODE ? Core::GetComponent()->getVersion() : ''));
		}

		// No SSL, skip all this!
		if(SSL_MODE != SSL_MODE_DISABLED){
			// If SSL is required by the controller and it's available, redirect there!
			if($this->ssl && !SSL){
				header('Location: ' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1));
				die('This page requires SSL, if it does not redirect you automatically, please <a href="' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
			}
			// If SSL is set to be ondemand and the page does not have it set but it's enabled, redirect to the non-SSL version.
			elseif(!$this->ssl && SSL && SSL_MODE == SSL_MODE_ONDEMAND){
				header('Location: ' . ROOT_URL_NOSSL . substr(REL_REQUEST_PATH, 1));
				die('This page does not require SSL, if it does not redirect you automatically, please <a href="' . ROOT_URL_NOSSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
			}
			// Else, SSL_MODE_ALLOWED doesn't care if SSL is enabled or not!
		}

		//echo mb_convert_encoding($data, 'UTF-8', 'auto');
		//echo mb_convert_encoding($data, 'HTML-ENTITIES', 'auto');
		echo $data;
	}

	public function addBreadcrumb($title, $link = null) {

		// Allow a non-resolved link to be passed in.
		if ($link !== null && strpos($link, '://') === false) $link = Core::ResolveLink($link);

		$this->breadcrumbs[] = array('title' => $title,
		                             'link'  => $link);
	}

	public function setBreadcrumbs($array) {
		// Array should be an array of either link => title keys or pages.
		$this->breadcrumbs = array();

		// If null is passed in, just leave them blank.
		// This is useful for implementing completely custom breadcrumbs.
		if (!$array) return;

		foreach ($array as $k => $v) {
			if ($v instanceof PageModel) $this->addBreadcrumb($v->get('title'), $v->getResolvedURL());
			else $this->addBreadcrumb($v, $k);
		}
	}

	public function getBreadcrumbs() {
		$crumbs = $this->breadcrumbs;
		if ($this->title) $crumbs[] = array('title' => $this->title,
		                                    'link'  => null);

		return $crumbs;

		//var_dump($crumbs); die();

		// Make sure the last element on breadcrumbs is not empty.
		/*if(($n = sizeof($this->breadcrumbs)) && !$this->breadcrumbs[$n-1]['title']){
			$this->breadcrumbs[$n-1]['title'] = $this->title;
		}*/
	}

	/**
	 * Add a control into the page template.
	 *
	 * Useful for embedding functions and administrative utilities inline without having to adjust the
	 * application template.
	 *
	 * @param string|array $title       The title to set for this control
	 * @param string $link        The link to set for this control
	 * @param string|array $class The class name or array of attributes to set on this control
	 *                            If this is an array, it should be an associative array for the advanced parameters
	 */
	public function addControl($title, $link = null, $class = 'edit') {
		$control = new ViewControl();

		// Completely associative-array based version!
		if(func_num_args() == 1 && is_array($title)){
			foreach($title as $k => $v){
				$control->set($k, $v);
			}
		}
		else{
			// Advanced method, allow for associative arrays.
			if(is_array($class)){
				foreach($class as $k => $v){
					$control->set($k, $v);
				}
			}
			// Default method; just a string for the class name.
			else{
				$control->class = $class;
			}

			$control->title = $title;
			$control->link = Core::ResolveLink($link);
		}

		// Some legacy updates for the icon.
		if(!$control->icon){
			switch($control->class){
				case 'add':
				case 'edit':
				case 'directory':
					$control->icon = $control->class;
					break;
				case 'delete':
					$control->icon = 'remove';
					break;
				case 'view':
					$control->icon = 'eye-open';
					break;
				default:
					$control->icon = $control->class;
					break;
			}
		}

		// Is this control the current page?  If so don't display it.
		if($control->link != Core::ResolveLink($this->baseurl)){
			$this->controls[] = $control;
		}
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
	 *
	 * @param string $accessstring
	 *
	 * @return boolean True or false based on access for current user.
	 */
	public function setAccess($accessstring) {
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
	public function checkAccess() {
		// And do some logic to see if the current user can access this resource.
		// This is more of a helper function to Controllers.
		$u = Core::User();
		if ($u->checkAccess($this->access)) {
			// yay.
			return true;
		}
		else {
			$this->error = View::ERROR_ACCESSDENIED;
			return false;
		}
	}


	/**
	 * Get the content to be inserted into the <head> tag for this view.
	 *
	 * @return string
	 */
	public function getHeadContent(){
		// First, the basic ones.
		$data = array_merge($this->head, $this->scripts['head'], $this->stylesheets);

		// Some of the automatic settings only get set if no errors.
		if($this->error == View::ERROR_NOERROR){

			// Custom meta tag :: http-equiv="last-modified"
			if($this->updated !== null){
				// last-modified is no longer a valid attribute in HTML5
				//$data[] = '<meta http-equiv="last-modified" content="' . Time::FormatGMT($this->updated, Time::TIMEZONE_GMT, Time::FORMAT_RFC2822) . '" />';
				$this->meta['article:modified_time'] = Time::FormatGMT($this->updated, Time::TIMEZONE_GMT, Time::FORMAT_ISO8601);
			}

			// Set the generator metatag, (this is handled internally within the tag)
			$this->meta['generator'] = true;

			// Some standard tags that also have og equivalents.
			if(!isset($this->meta['og:title'])){
				$this->meta['og:title'] = $this->title;
			}

			// Set the canonical url if not set.
			if($this->canonicalurl === null){
				$this->canonicalurl = Core::ResolveLink($this->baseurl);
			}

			// Set the canonical URL in the necessary spots (if it's not in error)
			if($this->canonicalurl !== false){
				$this->meta['canonical'] = $this->canonicalurl;
			}

			$this->meta['og:site_name'] = SITENAME;
		}

		// Merge in the standard meta names and properties now.
		$data = array_merge($data, $this->meta->fetch());


		if (ConfigHandler::Get('/core/markup/minified')) {
			$out = implode('', $data);
		}
		else {
			$out = '<!-- BEGIN Automatic meta tag generation -->' . "\n" . implode("\n", $data) . "\n" . '<!-- END Automatic meta tag generation -->';
		}

		return trim($out);
	}

	/**
	 * Get the content to be inserted just before the </body> tag for this view.
	 *
	 * @return string
	 */
	public function getFootContent(){
		// This section only contains scripts right now.
		$data = $this->scripts['foot'];

		if (ConfigHandler::Get('/core/markup/minified')) {
			$out = implode('', $data);
		}
		else {
			$out = implode("\n", $data);
		}

		return trim($out);
	}


	/**
	 * Add a script to the global View object.
	 *
	 * This will be rendered when a page-level view is rendered.
	 *
	 * @param string $script
	 * @param string $location
	 */
	public static function AddScript($script, $location = 'head') {
		if (strpos($script, '<script') === false) {
			// Resolve the script and wrap it with a script block.
			$script = '<script type="text/javascript" src="' . Core::ResolveAsset($script) . '"></script>';
		}

		$scripts =& PageRequest::GetSystemRequest()->getView()->scripts;

		// I can check to see if this script has been loaded before.
		if (in_array($script, $scripts['head'])) return;
		if (in_array($script, $scripts['foot'])) return;

		// No? alright, add it to the requested location!
		if ($location == 'head') $scripts['head'][] = $script;
		else $scripts['foot'][] = $script;
	}

	public static function AppendBodyContent($content){
		// Yeah I know script is a weird one to use, but it works damnit!
		$scripts =& PageRequest::GetSystemRequest()->getView()->scripts;

		if (in_array($content, $scripts['foot'])) return;

		$scripts['foot'][] = $content;
	}

	/**
	 * Add a linked stylesheet file to the global View object.
	 *
	 * @param string $link The link of the stylesheet
	 * @param type   $media Media to display the stylesheet with.
	 */
	public static function AddStylesheet($link, $media = "all") {
		if (strpos($link, '<link') === false) {
			// Resolve the script and wrap it with a script block.
			$link = '<link type="text/css" href="' . Core::ResolveAsset($link) . '" media="' . $media . '" rel="stylesheet"/>';
		}

		$styles =& PageRequest::GetSystemRequest()->getView()->stylesheets;

		// I can check to see if this script has been loaded before.
		if (!in_array($link, $styles)) $styles[] = $link;
	}

	/**
	 * Add an inline style to the global View object.
	 *
	 * @param string $style The contents of the <style> tag.
	 */
	public static function AddStyle($style) {
		if (strpos($style, '<style') === false) {
			$style = '<style>' . $style . '</style>';
		}

		$styles =& PageRequest::GetSystemRequest()->getView()->stylesheets;

		// I can check to see if this script has been loaded before.
		if (!in_array($style, $styles)) $styles[] = $style;
	}

	public static function SetHTMLAttribute($attribute, $value) {
		self::$HTMLAttributes[$attribute] = $value;
	}

	public static function GetHTMLAttributes($asarray = false) {
		$atts = self::$HTMLAttributes;

		if ($asarray) {
			return $atts;
		}
		else {
			$str = '';
			foreach ($atts as $k => $v) $str .= " $k=\"" . str_replace('"', '\"', $v) . "\"";
			return trim($str);
		}
	}


	/**
	 * Get the head data for the system view.
	 *
	 * @static
	 * @return string
	 */
	public static function GetHead() {
		return PageRequest::GetSystemRequest()->getView()->getHeadContent();
	}

	public static function GetFoot() {
		return PageRequest::GetSystemRequest()->getView()->getFootContent();
	}

	public static function AddMetaName($key, $value) {
		PageRequest::GetSystemRequest()->getView()->meta[$key] = $value;
	}

	public static function AddMeta($string) {
		if (strpos($string, '<meta') === false) $string = '<meta ' . $string . '/>';
		PageRequest::GetSystemRequest()->getView()->head[] = $string;
	}

	public static function AddHead($string){
		PageRequest::GetSystemRequest()->getView()->head[] = $string;
	}

}


class ViewException extends Exception {

}


