<?php
/**
 * Provides all elements required to connect the Controller's data and logic back to the browser in the necessary format.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @since 1.9
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

	/* Errors that may be occurred with views */
	const ERROR_OTHER                       = 1;
	const ERROR_NOERROR                     = 200;  // Request OK
	const ERROR_BADREQUEST                  = 400;  // Section 10.4.1: Bad Request
	const ERROR_UNAUTHORIZED                = 401;  // Section 10.4.2: Unauthorized
	const ERROR_PAYMENTREQUIRED             = 402;  // Section 10.4.3: Payment Required
	const ERROR_ACCESSDENIED                = 403;  // Section 10.4.4: Forbidden
	const ERROR_NOTFOUND                    = 404;  // Section 10.4.5: Not Found
	const ERROR_METHODNOTALLOWED            = 405;  // Section 10.4.6: Method Not Allowed
	const ERROR_NOTACCEPTABLE               = 406;  // Section 10.4.7: Not Acceptable
	const ERROR_PROXYAUTHENTICATIONREQUIRED = 407;  // Section 10.4.8: Proxy Authentication Required
	const ERROR_REQUESTTIMEOUT              = 408;  // Section 10.4.9: Request Time-out
	const ERROR_CONFLICT                    = 409;  // Section 10.4.10: Conflict
	const ERROR_GONE                        = 410;  // Section 10.4.11: Gone
	const ERROR_LENGTHREQUIRED              = 411;  // Section 10.4.12: Length Required
	const ERROR_PRECONDITIONFAILED          = 412;  // Section 10.4.13: Precondition Failed
	const ERROR_ENTITYTOOLARGE              = 413;  // Section 10.4.14: Request Entity Too Large
	const ERROR_URITOOLARGE                 = 414;  // Section 10.4.15: Request-URI Too Large
	const ERROR_UNSUPPORTEDMEDIATYPE        = 415;  // Section 10.4.16: Unsupported Media Type
	const ERROR_RANGENOTSATISFIABLE         = 416;  // Section 10.4.17: Requested range not satisfiable
	const ERROR_EXPECTATIONFAILED           = 417;  // Section 10.4.18: Expectation Failed
	const ERROR_SERVERERROR                 = 500;  // Generic server error

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

	/**
	 * Allow the mode to be set to a streamlined version of PAGE.
	 * This does not include debugging information and the like.
	 */
	const MODE_EMAILORPRINT = 'print';

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
	const CTYPE_RSS   = 'application/rss+xml';
	const CTYPE_CSV   = 'text/csv';


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
	 * This is sent to the browser if it's a page-type view as the Header: Content-Type field.
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
	 * Also handles the Header: Last-Modified field.
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
	 * When fetching the body numerous times, the contents may be cached here to speed up fetchBody().
	 *
	 * @var null|string
	 */
	private $_bodyCache = null;

	/**
	 * When fetching this entire skin+body numerous times, (ie: cache uses), the entire HTML may be cached here
	 * to be used by cache.
	 *
	 * @var null|string
	 */
	private $_fetchCache = null;

	/**
	 * An array of the body classes and their values.
	 *
	 * These automatically get appended in the <body> tag of the skin, providing the skin supports it.
	 *
	 * @var array
	 */
	public $bodyclasses = [];

	/**
	 * @var array Associative array of attributes for the <html> tag.
	 */
	public $htmlAttributes = [];

	/**
	 * @var array Associative array of custom headers to send to the browser automatically.
	 */
	public $headers = [];

	/**
	 * @var bool Set to false to prevent this page from being cacheable by Core.
	 */
	protected $cacheable = true;

	/**
	 * For widget and sub-page based views, they need to have a parent to render specific elements to,
	 * namely classes, styles, scripts, and the like.
	 *
	 * @var null|View
	 */
	public $parent = null;

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
	 * Get the template responsible for rendering this View's body content.
	 *
	 * Based on templatename.
	 * 
	 * @throws \Exception
	 *
	 * @return Core\Templates\TemplateInterface
	 */
	public function getTemplate() {
		if (!$this->_template) {
			$this->_template = \Core\Templates\Template::Factory($this->templatename);
			
			if(!$this->_template){
				throw new \Exception('Unable to load template file ' . $this->templatename);
			}
			
			// Ensure that the template is linked to this View correctly.
			$this->_template->setView($this);
		}

		return $this->_template;
	}

	/**
	 * Get the title of this page, (with automatic i18n translation)
	 *
	 * @return string
	 */
	public function getTitle(){
		if(strpos($this->title, 't:') === 0){
			return t(substr($this->title, 2));
		}
		else{
			return $this->title;
		}
	}

	/**
	 * Override a template, useful for forcing a different template type for this view.
	 *
	 * @param $template Core\Templates\TemplateInterface
	 * @return bool False on failure, True on success.
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
		return true;
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

		// Was this called before?
		if($this->_bodyCache !== null){
			return $this->_bodyCache;
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
					$ctemp = Core\Templates\Template::ResolveFile(preg_replace('/tpl$/i', 'xml.tpl', $tmpl));
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
					$ctemp = Core\Templates\Template::ResolveFile(preg_replace('/tpl$/i', 'ics.tpl', $tmpl));
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
				$ctemp = Core\Templates\Template::ResolveFile(preg_replace('/tpl$/i', 'json.tpl', $tmpl));
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

		switch ($this->mode) {
			case View::MODE_PAGE:
			case View::MODE_AJAX:
			case View::MODE_PAGEORAJAX:
			case View::MODE_EMAILORPRINT:
				$t = $this->getTemplate();
				$html = $t->fetch($tmpl);
				// Retrieve any/all JS, CSS, and Meta elements from that widget's View and transpose them here!
				// This is because now that View operations now correctly manipulate the View directly attached to the template,
				// the top-level page view no longer gets these directives.
				if($this->parent){
					$this->parent->_syncFromView($this);
				}
				break;
			case View::MODE_WIDGET:
				// This template can be a couple things.
				$tn = Core\Templates\Template::ResolveFile(preg_replace(':^[/]{0,1}pages/:', '/widgets/', $tmpl));
				if (!$tn) $tn = $tmpl;

				$t = $this->getTemplate();
				$html = $t->fetch($tn);

				// Retrieve any/all JS, CSS, and Meta elements from that widget's View and transpose them here!
				// This is because now that View operations now correctly manipulate the View directly attached to the template,
				// the top-level page view no longer gets these directives.
				if($this->parent){
					$this->parent->_syncFromView($this);
				}
				break;
		}

		// Save this HTML in local cache
		$this->_bodyCache = $html;

		return $html;
	}

	/**
	 * Fetch this view as an HTML string.
	 * @return mixed|null|string
	 */
	public function fetch() {

		if($this->_fetchCache !== null){
			// w00t ;)
			return $this->_fetchCache;
		}

		try{
			$body = $this->fetchBody();
			\Core\log_debug('Fetched application content from within View->fetch() for ' . $this->templatename);
		}
		catch(Exception $e){
			$this->error = View::ERROR_SERVERERROR;
			\Core\ErrorManagement\exception_handler($e, ($this->mode == View::MODE_PAGE));
			$body = '';
		}


		// If there's no template, I have nothing to even do!
		if ($this->mastertemplate === false) {
			return $body;
		}
		// Else if it's null, it's just not set yet :p
		// @deprecated here!
		elseif ($this->mastertemplate === null) {
			$this->mastertemplate = ConfigHandler::Get('/theme/default_template');
		}

		// Whee!
		//var_dump($this->templatename, Core\Templates\Template::ResolveFile($this->templatename));
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
				case View::MODE_EMAILORPRINT:
					$mastertpl = Core\Templates\Template::ResolveFile('skins/' . $this->mastertemplate);
					//$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/skins/' . $this->mastertemplate;
					break;
				case View::MODE_WIDGET:
					$mastertpl = Core\Templates\Template::ResolveFile('widgetcontainers/' . $this->mastertemplate);
					break;
			}
		}
		
		if($mastertpl == false && $this->mode == View::MODE_AJAX && $this->contenttype == View::CTYPE_HTML){
			// There is no master template to render scripts, but HTML was requested via AJAX.
			// Include them after the body!
			foreach($this->scripts['head'] as $idx => $script){
				if($idx == 0){
					// Skip the first head script; that's always the Core setup object.
					continue;
				}
				
				$body .= $script;
			}
			foreach($this->scripts['foot'] as $idx => $script){
				$body .= $script;
			}
			
			foreach($this->stylesheets as $s){
				$body .= $s;
			}
		}

		// If there's *still* no template, I still have nothing to do.
		if (!$mastertpl){
			return $body;
		}


		$template = \Core\Templates\Template::Factory($mastertpl);
		// Ensure that the template is linked to this View correctly.
		$template->setView($this);
		//$template = new Core\Templates\Template();
		//$template->setBaseURL('/');
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
			$template->assign('seotitle', $this->getTitle());
		}
		$template->assign('title', $this->getTitle());
		$template->assign('body', $body);

		// The body needs some custom classes for assisting the designers.
		// These are mainly pulled from the UA.
		$ua = \Core\UserAgent::Construct();

		$this->bodyclasses = array_merge($this->bodyclasses, $ua->getPseudoIdentifier(true));

		// Provide a way for stylesheets to target this page specifically.
		switch ($this->error) {
			case View::ERROR_BADREQUEST:
			case View::ERROR_PAYMENTREQUIRED:
			case View::ERROR_ACCESSDENIED:
			case View::ERROR_NOTFOUND:
			case View::ERROR_METHODNOTALLOWED:
			case View::ERROR_NOTACCEPTABLE:
			case View::ERROR_PROXYAUTHENTICATIONREQUIRED:
			case View::ERROR_REQUESTTIMEOUT:
			case View::ERROR_CONFLICT:
			case View::ERROR_GONE:
			case View::ERROR_LENGTHREQUIRED:
			case View::ERROR_PRECONDITIONFAILED:
			case View::ERROR_ENTITYTOOLARGE:
			case View::ERROR_URITOOLARGE:
			case View::ERROR_UNSUPPORTEDMEDIATYPE:
			case View::ERROR_RANGENOTSATISFIABLE:
			case View::ERROR_EXPECTATIONFAILED:
			case View::ERROR_UNAUTHORIZED:
				$url = 'error-' . $this->error;
				break;
			case 403:
				$url = "error-403 page-user-login";
				break;
			default:
				$url  = strtolower(trim(preg_replace('/[^a-z0-9\-]*/i', '', str_replace('/', '-', $this->baseurl)), '-'));
		}

		while($url != ''){
			$this->bodyclasses[] = 'page-' . $url;
			$url = substr($url, 0, strrpos($url, '-'));
		}


		$bodyclasses = strtolower(implode(' ', $this->bodyclasses));
		$template->assign('body_classes', $bodyclasses);

		try{
			$data = $template->fetch();
		}
		catch(SmartyException $e){
			$this->error = View::ERROR_SERVERERROR;
			error_log('[view error]');
			error_log('Template name: [' . $mastertpl . ']');
			\Core\ErrorManagement\exception_handler($e);
			require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
			die();
		}
		catch(TemplateException $e){
			$this->error = View::ERROR_SERVERERROR;
			error_log('[view error]');
			error_log('Template name: [' . $mastertpl . ']');
			\Core\ErrorManagement\exception_handler($e);
			require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
			die();
		}

		if($this->mode == View::MODE_EMAILORPRINT && $this->contenttype == View::CTYPE_HTML){
			// Inform other elements that the page is just about to be rendered.
			HookHandler::DispatchHook('/core/page/rendering', $this);

			// Replace the </head> tag with the head data from the current page
			// and the </body> with the foot data from the current page.
			// This is needed to be done at this stage because some element in
			// the template after rendering may add additional script to the head.
			// Also tack on any attributes for the <html> tag.
			if(preg_match('#</head>#i', $data)){
				// I need to do preg_replace because I only want to replace the FIRST instance of </head>
				$data = preg_replace('#</head>#i', $this->getHeadContent() . "\n" . '</head>', $data, 1);
			}
		}
		elseif ($this->mode == View::MODE_PAGE && $this->contenttype == View::CTYPE_HTML) {
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

				if(defined('ENABLE_XHPROF') && function_exists('xhprof_disable')){
					require_once('xhprof_lib/utils/xhprof_lib.php'); #SKIPCOMPILER
					require_once('xhprof_lib/utils/xhprof_runs.php'); #SKIPCOMPILER
					$xhprof_data = xhprof_disable();
					$namespace = trim(str_replace(['.', '/'], '-', HOST . REL_REQUEST_PATH), '-');
					$xhprof_runs = new XHProfRuns_Default();
					$run_id = $xhprof_runs->save_run($xhprof_data, $namespace);

					define('XHPROF_RUN', $run_id);
					define('XHPROF_SOURCE', $namespace);

					$xhprof_link = sprintf(
						'<a href="' . SERVERNAME . '/xhprof/index.php?run=%s&source=%s" target="_blank">View XHprof Profiler Report</a>' . "\n",
						$run_id,
						$namespace
					);
				}
				else{
					$xhprof_link = '';
				}

				// If the viewmode is regular and DEVELOPMENT_MODE is enabled, show some possibly useful information now that everything's said and done.
				if (DEVELOPMENT_MODE) {
					$legend = '<div class="fieldset-title">%s<i class="icon icon-chevron-down expandable-hint"></i><i class="icon icon-chevron-up collapsible-hint"></i></div>' . "\n";

					$debug = '';
					$debug .= '<pre class="xdebug-var-dump screen">';
					$debug .= '<fieldset class="debug-section collapsible" id="debug-section-template-information">';
					$debug .= sprintf($legend, 'Template Information');
					$debug .= "<span>";
					$debug .= 'Base URL: ' . $this->baseurl . "\n";
					$debug .= 'Template Requested: ' . $this->templatename . "\n";
					$debug .= 'Template Actually Used: ' . \Core\Templates\Template::ResolveFile($this->templatename) . "\n";
					$debug .= 'Master Skin: ' . $this->mastertemplate . "\n";
					$debug .= "</span>";
					$debug .= '</fieldset>';

					$debug .= '<fieldset class="debug-section collapsible" id="debug-section-performance-information">';
					$debug .= sprintf($legend, 'Performance Information');
					$debug .= "<span>";
					$debug .= $xhprof_link;
					$debug .= "Database Reads: " . \Core\Utilities\Profiler\DatamodelProfiler::GetDefaultProfiler()->readCount() . "\n";
					$debug .= "Database Writes: " . \Core\Utilities\Profiler\DatamodelProfiler::GetDefaultProfiler()->writeCount() . "\n";
					//$debug .= "Number of queries: " . DB::Singleton()->counter . "\n";
					//$debug .= "Amount of memory used by PHP: " . \Core\Filestore\format_size(memory_get_usage()) . "\n";
					$debug .= "Amount of memory used by PHP: " . \Core\Filestore\format_size(memory_get_peak_usage(true)) . "\n";
					$profiler = Core\Utilities\Profiler\Profiler::GetDefaultProfiler();
					$debug .= "Total processing time: " . $profiler->getTimeFormatted() . "\n";
					$debug .= "</span>";
					$debug .= '</fieldset>';

					$debug .= '<fieldset class="debug-section collapsible" id="debug-section-profiler-information">';
					$debug .= sprintf($legend, 'Core Profiler');
					$debug .= "<span>";
					$debug .= $profiler->getEventTimesFormatted();
					$debug .= "</span>";
					$debug .= '</fieldset>';

					$debug .= '<fieldset class="debug-section collapsible collapsed" id="debug-section-components-information">';
					// Tack on what components are currently installed.
					$debug .= sprintf($legend, 'Available Components');
					$debugcomponents = array_merge(Core::GetComponents(), Core::GetDisabledComponents());
					$debug .= "<span>";
					// Give me sorting!
					ksort($debugcomponents);
					foreach ($debugcomponents as $l => $v) {
						if($v->isEnabled() && $v->isReady()){
							$debug .= '[<span style="color:green;">Enabled</span>]';
						}
						elseif($v->isEnabled() && !$v->isReady()){
							$debug .= '[<span style="color:red;">!ERROR!</span>]';
						}
						else{
							$debug .= '[<span style="color:red;">Disabled</span>]';
						}


						$debug .= $v->getName() . ' ' . $v->getVersion() . "<br/>";
					}
					$debug .= "</span>";
					$debug .= '</fieldset>';

					$debug .= '<fieldset class="debug-section collapsible collapsed" id="debug-section-hooks-information">';
					// I wanna see what hooks are registered too!
					$debug .= sprintf($legend, 'Registered Hooks');
					foreach(HookHandler::GetAllHooks() as $hook){
						$debug .= "<span>";
						/** @var $hook Hook */
						$debug .= $hook->name;
						if($hook->description) $debug .= ' <em> - ' . $hook->description . '</em>';
						$debug .= "\n" . '<span style="color:#999;">Return expected: ' . $hook->returnType . '</span>';
						$debug .= "\n" . '<span style="color:#999;">Attached by ' . $hook->getBindingCount() . ' binding(s).</span>';
						foreach($hook->getBindings() as $b){
							$debug .= "\n" . ' * ' . $b['call'];
						}
						$debug .= "\n\n";
						$debug .= "</span>";
					}
					$debug .= '</fieldset>';

					// Display the licensed content on this application
					$debug .= '<fieldset class="debug-section collapsible collapsed" id="debug-section-licenser-information">';
					$debug .= sprintf($legend, 'Licensed Information');
					$lic = Core::GetLicensedDump();
					$debug .= '<div>';
					foreach($lic as $dat){
						$licPrefix = $dat['status'] ? '<span style="color:green;">' : '<span style="color:red;">';
						$debug .= $dat['component'] . ' license from ' . $dat['url'] . ' => ' . $licPrefix . $dat['message'] . "</span>\n";
						foreach($dat['features'] as $k => $v){
							$debug .= '&nbsp;&nbsp;&nbsp;&nbsp;' . $k . ': ' . $v . "\n";
						}
					}
					$debug .= '</div></fieldset>';

					$debug .= '<fieldset class="debug-section collapsible collapsed" id="debug-section-includes-information">';
					// I want to see how many files were included.
					$debug .= sprintf($legend, 'Included Files');
					$debug .= '<span>Number: ' . sizeof(get_included_files()) . "</span>";
					$debug .= '<span>'. implode("<br/>", get_included_files()) . "</span>";
					$debug .= '</fieldset>';

					$debug .= '<fieldset class="debug-section collapsible collapsed" id="debug-section-query-information">';
					$debug .= sprintf($legend, 'Query Log');
					$profiler = \Core\Utilities\Profiler\DatamodelProfiler::GetDefaultProfiler();
					$debug .= '<div>' . $profiler->getEventTimesFormatted() . '</div>';
					$debug .= '</fieldset>';

					// Display all the i18n strings available on the system.
					$debug .= '<fieldset class="debug-section collapsible collapsed" id="debug-section-i18nstrings-information">';
					$debug .= sprintf($legend, 'I18N Strings Available');
					$strings = \Core\i18n\I18NLoader::GetAllStrings();
					$debug .= '<ul>';
					foreach($strings as &$s){
						$debug .= '<li>' . $s['key'] . '</li>';
					}
					$debug .= '</ul>';
					$debug .= '</fieldset>';
					$debug .= '</pre>';

					// And append!
					$foot .= "\n" . $debug;
				}
				$data = substr_replace($data, $foot . "\n" . '</body>', $match, 7);
			}
			$data = preg_replace('#<html#', '<html ' . $this->getHTMLAttributes(), $data, 1);


			// This logic has been migrated to the {$body_classes} variable.
			/*
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
			*/
		}

		$this->_fetchCache = $data;

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
		if (
			!headers_sent() &&
			($this->mode == View::MODE_PAGE || $this->mode == View::MODE_PAGEORAJAX || $this->mode == View::MODE_AJAX || $this->mode == View::MODE_NOOUTPUT || $this->mode == View::MODE_EMAILORPRINT)
		) {
			switch ($this->error) {
				case View::ERROR_NOERROR:
					header('Status: 200 OK', true, $this->error);
					break;
				case View::ERROR_BADREQUEST:
					header('Status: 400 Bad Request', true, $this->error);
					break;
				case View::ERROR_UNAUTHORIZED:
					header('Status: 401 Unauthorized', true, $this->error);
					break;
				case View::ERROR_PAYMENTREQUIRED:
					header('Status: 402 Payment Required', true, $this->error);
					break;
				case View::ERROR_ACCESSDENIED:
					header('Status: 403 Forbidden', true, $this->error);
					break;
				case View::ERROR_NOTFOUND:
					header('Status: 404 Not Found', true, $this->error);
					break;
				case View::ERROR_METHODNOTALLOWED:
					header('Status: 405 Method Not Allowed', true, $this->error);
					break;
				case View::ERROR_NOTACCEPTABLE:
					header('Status: 406 Not Acceptable', true, $this->error);
					break;
				case View::ERROR_PROXYAUTHENTICATIONREQUIRED:
					header('Status: 407 Proxy Authentication Required', true, $this->error);
					break;
				case View::ERROR_REQUESTTIMEOUT:
					header('Status: 408 Request Time-out', true, $this->error);
					break;
				case View::ERROR_CONFLICT:
					header('Status: 409 Conflict', true, $this->error);
					break;
				case View::ERROR_GONE:
					header('Status: 410 Gone', true, $this->error);
					break;
				case View::ERROR_LENGTHREQUIRED:
					header('Status: 411 Length Required', true, $this->error);
					break;
				case View::ERROR_PRECONDITIONFAILED:
					header('Status: 412 Precondition Failed', true, $this->error);
					break;
				case View::ERROR_ENTITYTOOLARGE:
					header('Status: 413 Request Entity Too Large', true, $this->error);
					break;
				case View::ERROR_URITOOLARGE:
					header('Status: 414 Request-URI Too Large', true, $this->error);
					break;
				case View::ERROR_UNSUPPORTEDMEDIATYPE:
					header('Status: 415 Unsupported Media Type', true, $this->error);
					break;
				case View::ERROR_RANGENOTSATISFIABLE:
					header('Status: 416 Requested range not satisfiable', true, $this->error);
					break;
				case View::ERROR_EXPECTATIONFAILED:
					header('Status: 417 Expectation Failed', true, $this->error);
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

			header('X-Content-Encoded-By: Core Plus' . (DEVELOPMENT_MODE ? ' ' . Core::GetComponent()->getVersion() : ''));
			if(\ConfigHandler::Get('/core/security/x-frame-options')){
				header('X-Frame-Options: ' . \ConfigHandler::Get('/core/security/x-frame-options'));
			}

			if(\ConfigHandler::Get('/core/security/csp-frame-ancestors')){
				header('Content-Security-Policy: frame-ancestors \'self\' ' . \ConfigHandler::Get('/core/security/content-security-policy'));
			}

			if($this->updated !== null){
				header('Last-Modified: ' . Time::FormatGMT($this->updated, Time::TIMEZONE_USER, Time::FORMAT_RFC2822));
				//header('Last-Modified: ' . Time::FormatGMT($this->updated, Time::TIMEZONE_USER, Time::FORMAT_ISO8601));
			}

			// Are there any custom headers to send also?
			foreach($this->headers as $k => $v){
				header($k . ': ' . $v);
			}
		}

		// No SSL, skip all this!
		if(SSL_MODE != SSL_MODE_DISABLED){
			// If SSL is required by the controller and it's available, redirect there!
			if($this->ssl && !SSL){
				$u = ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1);
				
				// Smarty likes to be over zealous and send headers prematurely...
				if(!headers_sent()){
					header('Location: ' . $u );	
				}
				die('<html><body onload="window.location = \'' . $u . '\'" >This page requires SSL, please <a href="' . $u . '">Click Here to continue</a>.</body></html>');
			}
			// If SSL is set to be ondemand and the page does not have it set but it's enabled, redirect to the non-SSL version.
			elseif(!$this->ssl && SSL && SSL_MODE == SSL_MODE_ONDEMAND){
				$u = ROOT_URL_NOSSL . substr(REL_REQUEST_PATH, 1);

				// Smarty likes to be over zealous and send headers prematurely...
				if(!headers_sent()){
					header('Location: ' . $u );
				}
				die('<html><body onload="window.location = \'' . $u . '\'" >This page does not require SSL, please <a href="' . $u . '">Click Here to continue</a>.</body></html>');
			}
			// Else, SSL_MODE_ALLOWED doesn't care if SSL is enabled or not!
		}

		//echo mb_convert_encoding($data, 'UTF-8', 'auto');
		//echo mb_convert_encoding($data, 'HTML-ENTITIES', 'auto');
		echo $data;
	}

	/**
	 * Add a breadcrumb to the end of the breadcrumb stack.
	 *
	 * @param      $title
	 * @param null $link
	 */
	public function addBreadcrumb($title, $link = null) {

		// Allow a non-resolved link to be passed in.
		if ($link !== null && strpos($link, '://') === false){
			$link = \Core\resolve_link($link);
		}

		// New support for i18n strings!
		if(strpos($title, 't:') === 0){
			$title = t(substr($title, 2));
		}

		$this->breadcrumbs[] = array(
			'title' => $title,
			'link'  => $link
		);
	}

	/**
	 * Override and replace the breadcrumbs with an array.
	 *
	 * @param $array
	 */
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

	/**
	 * Get this view's breadcrumbs as an array
	 *
	 * @return array
	 */
	public function getBreadcrumbs() {
		$crumbs = $this->breadcrumbs;
		if ($this->title){
			$crumbs[] = [
				'title' => $this->getTitle(),
				'link'  => null
			];
		}

		// Remove duplicates
		// This can happen when the developer sets a breadcrumb manually AND the system adds the same breadcrumb,
		// (since breadcrumbs can be managed automatically by Core).
		$seen = [];
		foreach($crumbs as $k => $dat){
			if(in_array($dat['link'], $seen)){
				unset($crumbs[$k]);
			}
			else{
				$seen[] = $dat['link'];
				
				// Also ensure that the title for this entry has i18n translations.
				if(substr($dat['title'], 0, 2) == 't:'){
					$crumbs[$k]['title'] = t(substr($dat['title'], 2));
				}
			}
		}

		return $crumbs;
	}

	/**
	 * Add a control into the page template.
	 *
	 * Useful for embedding functions and administrative utilities inline without having to adjust the
	 * application template.
	 *
	 * @param string|array|Model $title  The title to set for this control
	 * @param string             $link   The link to set for this control
	 * @param string|array       $class  The class name or array of attributes to set on this control
	 *                            If this is an array, it should be an associative array for the advanced parameters
	 */
	public function addControl($title, $link = null, $class = 'edit') {

		if($title instanceof Model){
			// Allow a raw Model to be sent in as the control subject.
			// This is a shortcut for Controllers much like the {controls} smarty function has.
			$this->controls = ViewControls::DispatchModel($title);
			return;
		}

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
			$control->link = \Core\resolve_link($link);
		}

		// Is this control the current page?  If so don't display it.
		if($control->link != \Core\resolve_link($this->baseurl)){
			$this->controls[] = $control;
		}
	}

	/**
	 * Add an array of controls at once, useful in conjunction with the model->getControlLinks method.
	 *
	 * If a Model is provided as the subject, that is used as the subject and all system hooks apply thereof.
	 *
	 * @param array|Model $controls
	 */
	public function addControls($controls){
		if($controls instanceof Model){
			// Allow a raw Model to be sent in as the control subject.
			// This is a shortcut for Controllers much like the {controls} smarty function has.
			$this->controls = ViewControls::DispatchModel($controls);
			return;
		}

		foreach($controls as $c){
			$this->addControl($c);
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
		$u = \Core\user();
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
	 * Get if this View is cacheable
	 *
	 * @return bool
	 */
	public function isCacheable(){
		return $this->cacheable;
	}


	/**
	 * Get the content to be inserted into the <head> tag for this view.
	 *
	 * @return string
	 */
	public function getHeadContent(){
		$minified = ConfigHandler::Get('/core/markup/minified');

		// First, the basic ones.
		if($minified){
			$data = array_merge($this->stylesheets, $this->head, $this->scripts['head']);
		}
		else{
			$data = array_merge(
				['<!-- BEGIN STYLESHEET INSERTIONS -->'],
				$this->stylesheets,
				['<!-- END STYLESHEET INSERTIONS -->'],
				['<!-- BEGIN HEAD CONTENT INSERTIONS -->'],
				$this->head,
				['<!-- END HEAD CONTENT INSERTIONS -->'],
				['<!-- BEGIN JAVASCRIPT INSERTIONS -->'],
				$this->scripts['head'],
				['<!-- END JAVASCRIPT INSERTIONS -->']
			);
		}


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
				$this->canonicalurl = \Core\resolve_link($this->baseurl);
			}

			// Set the canonical URL in the necessary spots (if it's not in error)
			if($this->canonicalurl !== false){
				$this->meta['canonical'] = $this->canonicalurl;
			}

			$this->meta['og:site_name'] = SITENAME;
		}

		// Merge in the standard meta names and properties now.
		$data = array_merge($data, $this->meta->fetch());


		if ($minified) {
			$out = implode('', $data);
		}
		else {
			$out = '<!-- BEGIN Automatic HEAD generation -->' . "\n\n" . implode("\n", $data) . "\n\n" . '<!-- END Automatic HEAD generation -->';
		}

		return trim($out);
	}

	/**
	 * Get the content to be inserted just before the </body> tag for this view.
	 *
	 * @return string
	 */
	public function getFootContent(){
		$minified = ConfigHandler::Get('/core/markup/minified');

		// This section only contains scripts right now.
		if($minified){
			$data = $this->scripts['foot'];
		}
		else{
			$data = array_merge(
				['<!-- BEGIN JAVASCRIPT INSERTIONS -->'],
				$this->scripts['foot'],
				['<!-- END JAVASCRIPT INSERTIONS -->']
			);
		}


		if ($minified) {
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
	public function addScript($script, $location = 'head') {
		if (strpos($script, '<script') === false) {
			// Resolve the script and wrap it with a script block.
			$script = '<script type="text/javascript" src="' . \Core\resolve_asset($script) . '"></script>';
		}

		// This snippet is to allow AddScript to be called statically.
		// Core <= 2.6.0 used this method, and components built on it will be expecting this functionality.
		// ! IMPORTANT ! Do NOT remove this until if/else block until 2.6.0 is no longer supported!
		// 2013.08.16 - cpowell
		if(isset($this)){
			$scripts =& $this->scripts;
		}
		else{
			$scripts =& \Core\view()->scripts;
		}


		// I can check to see if this script has been loaded before.
		if (in_array($script, $scripts['head'])) return;
		if (in_array($script, $scripts['foot'])) return;

		// No? alright, add it to the requested location!
		if ($location == 'head'){
			$scripts['head'][] = $script;
		}
		else{
			$scripts['foot'][] = $script;
		}
	}

	public function appendBodyContent($content){

		// This snippet is to allow AppendBodyContent to be called statically.
		// Core <= 2.6.0 used this method, and components built on it will be expecting this functionality.
		// ! IMPORTANT ! Do NOT remove this until if/else block until 2.6.0 is no longer supported!
		// 2013.08.18 - cpowell
		if(isset($this)){
			$scripts =& $this->scripts;
		}
		else{
			$scripts =& \Core\view()->scripts;
		}

		// Yeah I know script is a weird one to use, but it works damnit!
		if (in_array($content, $scripts['foot'])) return;

		$scripts['foot'][] = $content;
	}

	/**
	 * Add a linked stylesheet file to the global View object.
	 *
	 * @param string $link The link of the stylesheet
	 * @param string $media Media to display the stylesheet with.
	 */
	public function addStylesheet($link, $media = "all") {
		if (strpos($link, '<style') === 0) {
			// This is a style tag, not a stylesheet.  Use that method instead.
			$this->addStyle($link);
			return;
		}

		if (strpos($link, '<link') === false) {

			// Is this a CSS file or a LESS file?
			if(strripos($link, '.less') == strlen($link)-5 ){
				$rel = 'stylesheet/less';
				Core::_AttachLessJS();
			}
			else{
				$rel = 'stylesheet';
			}

			// Resolve the script and wrap it with a script block.
			$link = '<link type="text/css" href="' . \Core\resolve_asset($link) . '" media="' . $media . '" rel="' . $rel . '"/>';
		}

		// This snippet is to allow AddStylesheet to be called statically.
		// Core <= 2.6.0 used this method, and components built on it will be expecting this functionality.
		// ! IMPORTANT ! Do NOT remove this until if/else block until 2.6.0 is no longer supported!
		// 2013.08.18 - cpowell
		if(isset($this)){
			$styles =& $this->stylesheets;
		}
		else{
			$styles =& \Core\view()->stylesheets;
		}

		// I can check to see if this script has been loaded before.
		if (!in_array($link, $styles)) $styles[] = $link;
	}

	/**
	 * Add an inline style to the global View object.
	 *
	 * @param string $style The contents of the <style> tag.
	 */
	public function addStyle($style) {
		if(strpos($style, '<link ') === 0){
			// This is a full stylesheet, not a style!
			$this->addStylesheet($style);
			return;
		}

		if (strpos($style, '<style') === false) {
			// Every style must be wrapped in style tags, so do that if not already done.
			$style = '<style>' . $style . '</style>';
		}

		// Don't forget to include the less compiler if it was requested!
		if(strpos($style, 'rel="stylesheet/less"') !== false){
			Core::_AttachLessJS();
		}
		if(strpos($style, "rel='stylesheet/less'") !== false){
			Core::_AttachLessJS();
		}

		// This snippet is to allow AddStyle to be called statically.
		// Core <= 2.6.0 used this method, and components built on it will be expecting this functionality.
		// ! IMPORTANT ! Do NOT remove this until if/else block until 2.6.0 is no longer supported!
		// 2013.08.18 - cpowell
		if(isset($this)){
			$styles =& $this->stylesheets;
		}
		else{
			$styles =& \Core\view()->stylesheets;
		}

		// I can check to see if this script has been loaded before.
		if (!in_array($style, $styles)) $styles[] = $style;
	}

	/**
	 * Set an HTML attribute
	 *
	 * @param string $attribute key
	 * @param string $value     value
	 */
	public function setHTMLAttribute($attribute, $value) {
		$this->htmlAttributes[$attribute] = $value;
	}

	/**
	 * Get the HTML attributes as either a string or an array.
	 *
	 * These attributes are from the <html> tag.
	 *
	 * @param bool $asarray Set to false for a string, true for an array.
	 *
	 * @return array|string
	 */
	public function getHTMLAttributes($asarray = false) {
		$atts = $this->htmlAttributes;

		if ($asarray) {
			return $atts;
		}
		else {
			$str = '';
			foreach ($atts as $k => $v) $str .= " $k=\"" . str_replace('"', '&quot;', $v) . "\"";
			return trim($str);
		}
	}

	/**
	 * Add a meta name, (and value), to this view.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addMetaName($key, $value) {
		// This snippet is to allow AddStyle to be called statically.
		// Core <= 2.6.0 used this method, and components built on it will be expecting this functionality.
		// ! IMPORTANT ! Do NOT remove this until if/else block until 2.6.0 is no longer supported!
		// 2013.08.18 - cpowell
		if(isset($this)){
			$this->meta[$key] = $value;
		}
		else{
			\Core\view()->meta[$key] = $value;
		}
	}

	/**
	 * Add a full meta string to the head of this view.
	 *
	 * This should be formatted as &lt;meta name="blah" content="foo"/&gt;, (or however as necessary).
	 *
	 * @param $string
	 */
	public function addMeta($string) {
		if (strpos($string, '<meta') === false) $string = '<meta ' . $string . '/>';

		// This snippet is to allow AddStyle to be called statically.
		// Core <= 2.6.0 used this method, and components built on it will be expecting this functionality.
		// ! IMPORTANT ! Do NOT remove this until if/else block until 2.6.0 is no longer supported!
		// 2013.08.18 - cpowell
		if(isset($this)){
			$this->head[] = $string;
		}
		else{
			\Core\view()->head[] = $string;
		}
	}

	/**
	 * Add a full string to the head of this view.
	 *
	 * @param $string
	 */
	public function addHead($string){
		// This snippet is to allow AddStyle to be called statically.
		// Core <= 2.6.0 used this method, and components built on it will be expecting this functionality.
		// ! IMPORTANT ! Do NOT remove this until if/else block until 2.6.0 is no longer supported!
		// 2013.08.18 - cpowell
		if(isset($this)){
			$this->head[] = $string;
		}
		else{
			\Core\view()->head[] = $string;
		}
	}

	/**
	 * Method to append a header onto the array of headers to be sent to the browser when render is called.
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addHeader($key, $value){
		$this->headers[$key] = $value;
	}

	/**
	 * Disable the View cache on this object and any/all parents
	 */
	public function disableCache(){
		$this->cacheable = false;
		if($this->parent){
			$this->parent->disableCache();
		}
	}

	/**
	 * Internal only method to sync another view's metadata into this one.
	 *
	 * This is to allow Views to have certain attributes bubble up to the top-most view to be rendered out correctly.
	 *
	 * @param View $view
	 */
	protected function _syncFromView(View $view){
		if($view === $this){
			// !??!
			return;
		}

		foreach($view->head as $h){
			$this->addHead($h);
		}

		foreach($view->meta as $m){
			$this->addMeta($m);
		}

		foreach($view->scripts['head'] as $s){
			$this->addScript($s, 'head');
		}
		foreach($view->scripts['foot'] as $s){
			$this->addScript($s, 'foot');
		}
		foreach($view->stylesheets as $s){
			$this->addStyle($s);
		}

		if($view->ssl){
			// Allow children to mandate that a given page be presented in SSL if available.
			$this->ssl = true;
		}

		// Simple merges are possible here!
		$this->bodyclasses += $view->bodyclasses;
		$this->htmlAttributes += $view->htmlAttributes;
	}


	/**
	 * Get the head data for the system view.
	 *
	 * @deprecated 2013.08.18
	 * @static
	 * @return string
	 */
	public static function GetHead() {
		trigger_error('View::GetHead is deprecated, please use \Core\view()->getHeadContent instead!', E_USER_DEPRECATED);
		return \Core\view()->getHeadContent();
	}

	/**
	 * @deprecated 2013.08.18
	 * @return string
	 */
	public static function GetFoot() {
		trigger_error('View::GetFoot is deprecated, please use \Core\view()->getFootContent instead!', E_USER_DEPRECATED);
		return \Core\view()->getFootContent();
	}
}
