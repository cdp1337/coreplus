<?php
/**
 * The main object responsible for setting up the page request and getting the data corresponding to it.
 *
 * @package Core
 * @author Charlie Powell <charlie@eval.bz>
 * @since 1.9
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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
	public $acceptLanguages = array();

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
	public $useragent;
	/**
	 * @var string Full string of the path + query string requested
	 */
	public $uri;
	/**
	 * @var string String of the path requested
	 */
	public $uriresolved;
	/**
	 * @var string Protocol of the requested connection, (HTTP/1.1 or HTTP/1.0 usually)
	 */
	public $protocol;

	/**
	 * @var array Array of the GET parameters on this request.
	 */
	public $parameters = array();

	/**
	 * Content type requested
	 *
	 * @var string
	 */
	public $ctype = View::CTYPE_HTML;

	/**
	 * The extension of the file requested, usually html, but may be pdf, gif, png, etc if necessary.
	 *
	 * @var string
	 */
	public $ext = 'html';

	/**
	 * @var string Hostname of the requested connection
	 */
	public $host;

	/**
	 * The cached pagemodel for this request.
	 * @var PageModel
	 */
	private $_pagemodel = null;

	/**
	 * The view that will be used to render the page.
	 * *IMPORTANT*, this may change throughout the page execution, should a component "hijack" the view.
	 *
	 * @var View
	 */
	private $_pageview = null;

	/**
	 * Set to true if this is already a cached View, (so it doesn't re-cache it again).
	 *
	 * @var bool
	 */
	private $_cached = false;

	public function __construct($uri = '') {

		$this->host = SERVERNAME;
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

		// The URL should not end with a trailing slash.
		$uri = rtrim($uri, '/');
		if($uri == ''){
			// Ensure that the index page remains "/".
			$uri = '/';
		}

		$this->uriresolved = $uri;
		$this->protocol    = $_SERVER['SERVER_PROTOCOL'];
		// Specified with prepending ".xml|.json,etc" to the resource.
		// This is merely a suggestion by the user agent.  If the application doesn't support this medium.... it won't return it.
		$this->ext = $ctype;
		$this->ctype = \Core\Filestore\extension_to_mimetype($ctype);

		$this->_resolveMethod();
		$this->_resolveAcceptHeader();
		$this->_resolveUAHeader();
		$this->_resolveLanguageHeader();

		// Set the request parameters
		if (is_array($_GET)) {
			foreach ($_GET as $k => $v) {
				if (is_numeric($k)) continue;
				$this->parameters[$k] = $v;
			}
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

		// The PageModel handles all the Core utilities, but it skips the GET parameters, as those are not part of the page schema.
		// Instead however, the PageRequest needs to provide all interfaces for the user's request,
		// including GET parameters!
		if($ret['parameters'] === null){
			$ret['parameters'] = [];
		}

		$ret['parameters'] = array_merge($ret['parameters'], $_GET);

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
		return isset($parts['baseurl']) ? $parts['baseurl'] : null;
	}

	/**
	 * Get the view component for this page request.
	 *
	 * @return View
	 */
	public function getView(){
		if($this->_pageview === null){
			$this->_pageview = new View();
		}

		return $this->_pageview;
	}

	/**
	 * Execute the controller and method this page request points to.
	 */
	public function execute() {

		\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Starting PageRequest->execute()');

		if($this->isCacheable()){
			$uakey = \Core\UserAgent::Construct()->getPseudoIdentifier();
			$urlkey = $this->host . $this->uri;
			$expires = $this->getPageModel()->get('expires');
			$key = 'page-cache-' . md5($urlkey . '-' . $uakey);

			$cached = \Core\Cache::Get($key, $expires);
			if($cached && $cached instanceof View){
				$this->_pageview = $cached;
				$this->_cached = true;
				return;
			}
		}

		// Anything that needs to fire off *before* the page is rendered.
		// This includes widgets, script addons, and anything else that needs a CurrentPage.
		HookHandler::DispatchHook('/core/page/preexecute');

		// Load the underlying controller.
		$pagedat   = $this->splitParts();

		/** @var View $view The valid view object for this page */
		$view = $this->getView();

		// The controller must exist first!
		// (note, the SplitParts logic already takes care of the "Is this a valid controller" logic)
		if (!(isset($pagedat['controller']) && $pagedat['controller'])) {
			$view->error = View::ERROR_NOTFOUND;
			return;
		}

		$component = Core::GetComponentByController($pagedat['controller']);

		//////////////////////////////////////////////////////////////////////////////
		///  In this block of logic, either the page is executed and a view returned,
		///  or a view is generated with an error.
		//////////////////////////////////////////////////////////////////////////////
		if (!$component) {
			// Not found
			$view->error = View::ERROR_NOTFOUND;
			return;
		}
		elseif (is_a($component, 'Component')) {
			// It's a 1.0 style component...
			CurrentPage::Render();
			die();
		}
		//elseif (is_a($component, 'Component_2_1')) {
		//	$this->_pageview = $view = $request->execute();
		//}
		elseif(!is_a($component, 'Component_2_1')) {
			$view->error = View::ERROR_NOTFOUND;
			return;
		}

		// Any method that starts with a "_" is an internal-only method!
		if ($pagedat['method']{0} == '_') {
			$view->error = View::ERROR_NOTFOUND;
			return;
		}

		// It also must be a part of the class... obviously
		if (!method_exists($pagedat['controller'], $pagedat['method'])) {
			$view->error = View::ERROR_NOTFOUND;
			return;
		}


		/** @var $controller Controller_2_1 This will be a Controller object. */
		$controller = Controller_2_1::Factory($pagedat['controller']);

		$view->baseurl = $this->getBaseURL();
		$controller->setView($view);

		// Make sure that the controller can access this object.
		$controller->setPageRequest($this);

		// The main page object.
		$page = $this->getPageModel();

		// Check the access string first, (if there is one)
		if ($controller->accessstring !== null) {
			// Update the page's access string, (just in case it's saved at the end of execution)
			$page->set('access', $controller->accessstring);

			// And if the user doesn't have access to it...
			if (!\Core\user()->checkAccess($controller->accessstring)) {
				$view->error = View::ERROR_ACCESSDENIED;
				return;
			}
		}

		// If the parent Controller object has a method named $pagedat['method'], assume it's a security error!
		// This is because if the parent Controller object has a method, it's most likely a utility method
		// that shouldn't be called from the public web!
		foreach(get_class_methods('Controller_2_1') as $parentmethod){
			$parentmethod = strtolower($parentmethod);
			if($parentmethod == $pagedat['method']){
				$view->error = View::ERROR_BADREQUEST;
				return;
			}
		}

		// Additional security logic for existing pages in multi-site mode.
		// If this exact URL is registered to another site, then
		// don't allow this site to display it.
		if(!$page->exists() && Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$site = MultiSiteHelper::GetCurrentSiteID();

			$anypage = PageModel::Find(['baseurl = ' . $page->get('baseurl')], 1);

			if($anypage){

				if($anypage->get('site') == -1){
					// If this is a global page.... that's ok.
					// Just remap the page variable to this one!
					$page = $anypage;
				}
				elseif($anypage->get('site') == $site){
					// Strange... it should have located this page...
					// Anyway, it's allowed, the site matches up.
					$page = $anypage;
				}
				else{
					\Core\redirect($anypage->getResolvedURL());
				}
			}
		}

		$return = call_user_func(array($controller, $pagedat['method']));
		if (is_int($return)) {
			// A generic error code was returned.  Create a View with that code and return that instead.
			$view->error = $return;
			//return;
		}
		elseif(is_a($return, 'View') && $return != $view){
			// The controller method changed the view, (which is allowed),
			// but this needs to be remapped to this object so render knows about it.
			$this->_pageview = $view = $return;
		}
		elseif ($return === null) {
			// Hopefully it's setup!
			$return = $controller->getView();
			if($return != $view){
				$this->_pageview = $view = $return;
			}
		}
		elseif(!is_a($return, 'View')){
			if(DEVELOPMENT_MODE){
				var_dump('Controller method returned', $return);
				die('Sorry, but this controller did not return a valid object.  Please ensure that your method returns either an integer, null, or a View object!');
			}
			else{
				$view->error = View::ERROR_SERVERERROR;
				return;
			}
		}
		// No else needed, else it's a valid object.


		// You may be asking why $view is one object, but $return is the return from page execution.
		// GREAT QUESTION, The $view is the original view object created from the page request.  That is passed into
		// the controller and exposed via $this->getView().  The return can be a view, int, or other status indicator.
		// However since the controller can return a different view, that view should be used instead!
		///** @var $return View */


		// Allow the controller to assign controls via a shortcut function.
		if($view->error == View::ERROR_NOERROR){
			$controls = $controller->getControls();

			// This method may do absolutely nothing, add the controls to the view itself, or return an array of them.
			if(is_array($controls)){
				foreach($controls as $control){
					$view->addControl($control);
				}
			}
		}


		// For some of the options, there may be some that can be used for a fuzzy page, ie: a page's non-fuzzy template,
		// title, or meta information.
		if($view->error == View::ERROR_NOERROR){
			if ($page->exists()) {
				$defaultpage = $page;
			} else {
				$defaultpage = null;
				$url         = $view->baseurl;
				while ($url != '') {
					$url = substr($url, 0, strrpos($url, '/'));
					$p   = PageModel::Find(array('baseurl' => $url, 'fuzzy' => 1), 1);
					if ($p === null) continue;
					if ($p->exists()) {
						$defaultpage = $p;
						break;
					}
				}
				if ($defaultpage === null) {
					// Fine....
					$defaultpage = $page;
				}
			}

			$defaultmetas = $defaultpage->getLink('PageMeta');

			// Make a list of the existing ones so I know which ones not to overwrite!
			// Just the key will suffice quite nicely.
			$currentmetas = array();
			foreach($view->meta as $k => $meta){
				$currentmetas[] = $k;
			}

			// Load some of the page information into the view now!
			foreach($defaultmetas as $meta){
				/** @var $meta PageMetaModel */
				$key = $meta->get('meta_key');

				$viewmeta = $meta->getViewMetaObject();

				// again, allow the executed controller have the final say on meta information.
				if ($meta->get('meta_value_title') && !in_array($key, $currentmetas)) {
					$view->meta[$key] = $viewmeta;
				}
			}


			// Since the controller already ran, do not overwrite the title.
			if ($view->title === null){
				$view->title = $defaultpage->get('title');
			}

			// Tracker to see if this page, (or a parent's page), is an admin-level page.
			// This is required because "admin" pages may have a different skin and should always have the dashboard as the top-level breadcrumb.
			/** @var boolean $isadmin */
			$isadmin = ($page->get('admin') == '1');

			$parents = array();
			$parenttree = $page->getParentTree();
			foreach ($parenttree as $parent) {
				/** @var PageModel $parent */
				$parents[] = array(
					'title' => $parent->get('title'),
					'link'  => $parent->getResolvedURL()
				);

				// Since I'm here, check if this page is an admin page.
				if($parent->get('admin')){
					$isadmin = true;
				}
			}
			$view->breadcrumbs = array_merge($parents, $view->breadcrumbs);

			if($isadmin && $view->baseurl != '/admin'){
				// Make sure that admin is the top breadcrumb.
				// This block doesn't need to apply for the actual admin page itself, as that doesn't need its own breadcrumb :/
				$adminlink = \Core\resolve_link('/admin');
				if(!isset($view->breadcrumbs[0])){
					// Nothing is even set!
					$view->breadcrumbs[] = ['title' => 'Administration', 'link' => $adminlink];
				}
				elseif($view->breadcrumbs[0]['link'] != $adminlink){
					// It's set, but not to admin.
					$view->breadcrumbs = array_merge([['title' => 'Administration', 'link' => $adminlink]], $view->breadcrumbs);
				}
			}
		}
		else{
			$defaultpage = null;
			$isadmin = false;
		}


		// Try to guess the templatename if it wasn't set.
		if ($view->error == View::ERROR_NOERROR && $view->contenttype == View::CTYPE_HTML && $view->templatename === null) {
			$cnameshort           = (strpos($pagedat['controller'], 'Controller') == strlen($pagedat['controller']) - 10) ? substr($pagedat['controller'], 0, -10) : $pagedat['controller'];
			$view->templatename = strtolower('/pages/' . $cnameshort . '/' . $pagedat['method'] . '.tpl');
		}
		elseif ($view->error == View::ERROR_NOERROR && $view->contenttype == View::CTYPE_XML && $view->templatename === null) {
			$cnameshort           = (strpos($pagedat['controller'], 'Controller') == strlen($pagedat['controller']) - 10) ? substr($pagedat['controller'], 0, -10) : $pagedat['controller'];
			$view->templatename = Template::ResolveFile(strtolower('pages/' . $cnameshort . '/' . $pagedat['method'] . '.xml.tpl'));
		}

		// In addition to the autogeneration, also support the page_template from the datastore.
		if($defaultpage && $defaultpage->get('page_template')){
			// Switch the template over to that custom one.
			// Some legacy data will have the fully resolved path for this template.
			// This has been switched to just the basename of the custom template,
			// but legacy data be legacy, 'yo.                            0.o

			$base     = substr($view->templatename, 0, -4);
			$override = $defaultpage->get('page_template');
			if($base && strpos($override, $base) === 0){
				$view->templatename = $override;
			}
			elseif($base){
				$view->templatename = $base . '/' . $override;
			}
		}

		// Guess which theme skin (mastertemplate) should be used if one wasn't specified.
		if($view->mastertemplate == 'admin'){
			// If the master template is set explictly to be the admin skin, then transpose that to the set admin skin.
			// This is useful for the pages that may not be under the "/admin" umbrella, but still rendered with the admin UI.
			$view->mastertemplate = ConfigHandler::Get('/theme/default_admin_template');
		}
		elseif($view->mastertemplate){
			// No change needed, just skip the below cases.
		}
		elseif($view->mastertemplate === false){
			// If the master template is explictly set to false, the page wanted no master template!
		}
		elseif($isadmin){
			// This page doesn't have a master template set, but it or a parent is set as an admin-level page.
			$view->mastertemplate = ConfigHandler::Get('/theme/default_admin_template');
		}
		elseif ($defaultpage && $defaultpage->get('theme_template')) {
			// Master template set in the database?
			$view->mastertemplate = $defaultpage->get('theme_template');
		}
		elseif($defaultpage && $defaultpage->exists() && $defaultpage->get('admin')){
			// Or an admin level page?
			$view->mastertemplate = ConfigHandler::Get('/theme/default_admin_template');
		}
		elseif(sizeof($view->breadcrumbs) && $view->breadcrumbs[0]['title'] == 'Administration'){
			// Whatever, close e-damn-nough!
			// This happens for pages that don't actually exist, like "edit"....
			$view->mastertemplate = ConfigHandler::Get('/theme/default_admin_template');
		}
		else{
			$view->mastertemplate = ConfigHandler::Get('/theme/default_template');
		}

		// First of all, if the current theme is not available, reset back to the first theme available!
		if(!($theme = ThemeHandler::GetTheme())){
			/** @var \Theme\Theme $theme */
			$theme = ThemeHandler::GetTheme('base-v2');
			$view->mastertemplate = 'basic.tpl';

			Core::SetMessage('You have an invalid theme selected, please fix that!', 'error');
		}

		// Make sure the selected mastertemplate actually exists!
		if($view->mastertemplate !== false){
			$themeskins = $theme->getSkins();
			$mastertplgood = false;
			foreach($themeskins as $skin){
				if($skin['file'] == $view->mastertemplate){
					// It's located!
					$mastertplgood =true;
					break;
				}
			}

			// A few special cases.
			if($view->mastertemplate == 'blank.tpl'){
				// This is acceptable as a default one.
				$mastertplgood =true;
			}

			if(!$mastertplgood){
				// Just use the first one instead!
				trigger_error('Invalid skin [' . $view->mastertemplate . '] selected for this page, skin is not located within the selected theme!  Using first available instead.', E_USER_NOTICE);
				$view->mastertemplate = $themeskins[0]['file'];
			}
		}

		// Handle some of the new automatic meta data associated with Pages and the resulting View.

		if(!$page->get('indexable')){
			// Bots have no business indexing user-action pages.
			$view->addMetaName('robots', 'noindex');
		}
		if(!isset($view->meta['title'])){
			$view->meta['title'] = $page->getSEOTitle();
		}

		HookHandler::DispatchHook('/core/page/postexecute');

		\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Completed PageRequest->execute()');
	}

	/**
	 * Render the View to the browser.
	 */
	public function render(){
		\Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->record('Starting PageRequest->render()');

		$view = $this->getView();
		$page = $this->getPageModel();

		// Dispatch the hooks here if it's a 404 or 403.
		if ($view->error == View::ERROR_ACCESSDENIED || $view->error == View::ERROR_NOTFOUND) {
			// Let other things chew through it... (optionally)
			HookHandler::DispatchHook('/core/page/error-' . $view->error, $view);
		}

		try {
			// This will pre-fetch the contents of the entire page and store it into memory.
			// If it is cacheable, then it will be cached and used for the next execution.
			$view->fetch();
		}
		catch (Exception $e) {
			// If something happens in the rendering of the template... consider it a server error.
			$view->error   = View::ERROR_SERVERERROR;
			$view->baseurl = '/error/error/500';
			$view->setParameters(array());
			$view->templatename   = '/pages/error/error500.tpl';
			$view->mastertemplate = ConfigHandler::Get('/theme/default_template');
			$view->assignVariable('exception', $e);
			\Core\ErrorManagement\exception_handler($e);

			$view->fetch();
		}


		if($this->isCacheable()){
			$uakey = \Core\UserAgent::Construct()->getPseudoIdentifier();
			$urlkey = $this->host . $this->uri;
			$expires = $page->get('expires'); // Number of seconds.
			$key = 'page-cache-' . md5($urlkey . '-' . $uakey);

			$d = new \Core\Date\DateTime();
			$d->modify('+' . $expires . ' seconds');

			$view->headers['Cache-Control'] = 'max-age=' . $expires;
			$view->headers['Expires'] = $d->format('r', \Core\Date\Timezone::TIMEZONE_GMT);
			$view->headers['Vary'] = 'Accept-Encoding,User-Agent,Cookie';
			$view->headers['X-Core-Cached-Date'] = \Core\Date\DateTime::NowGMT('r');
			$view->headers['X-Core-Cached-Server'] = 1; // @todo Implement multi-server support.
			$view->headers['X-Core-Cached-Render-Time'] = \Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->getTimeFormatted();

			// Record the actual View into cache.
			\Core\Cache::Set($key, $view, $expires);

			// And record the key onto an index cache record so there's a record of what to delete on updates.
			$indexkey = $page->getIndexCacheKey();
			$index = \Core\Cache::Get($indexkey, 86400);
			if(!$index){
				$index = [];
			}
			$index[] = $key;
			\Core\Cache::Set($indexkey, $index, 86400);
		}
		$view->headers['X-Core-Render-Time'] = \Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->getTimeFormatted();

		$view->render();

		// Make sure I update any existing page now that the controller has ran.
		if ($page && $page->exists() && $view->error == View::ERROR_NOERROR) {

			// Only increase the pageview count if the visitor is not a bot.
			// UA detection isn't very accurate, but this isn't for precision accuracy, merely a rough estimate.
			if(!\Core\UserAgent::Construct()->isBot()){
				$page->set('pageviews', $page->get('pageviews') + 1);
			}

			$page->set('last_template', $view->templatename);
			$page->set('body', $view->fetchBody());

			$page->save();
		}

		// Just before the page stops execution...
		HookHandler::DispatchHook('/core/page/postrender');
	}

	public function isCacheable(){
		// I opted to break the cacheable logic out like this because it's easier to document than one large if block.
		// Start with every page being cacheable, (default).
		$cacheable = true;

		if(DEVELOPMENT_MODE){
			// If in development mode, do not cache any pages for any users.
			$cacheable = false;
		}
		elseif($this->_cached){
			// If this page is already cached, do not try to re-cache.
			$cacheable = false;
		}
		elseif(\Core\user()->exists()){
			// If the user is currently logged in, do no cache any page.
			$cacheable = false;
		}
		elseif($this->method != PageRequest::METHOD_GET){
			// Only cache and provide caching for GET requests.
			$cacheable = false;
		}
		elseif($this->getPageModel()->get('expires') == 0){
			// Pages that are set to 0 are also not cacheable.
			$cacheable = false;
		}
		elseif($this->getView()->mode != View::MODE_PAGE){
			// Only traditional page views are cacheable.
			$cacheable = false;
		}

		return $cacheable;
	}

	/**
	 * Set all parameters for this view
	 * @param $params
	 */
	public function setParameters($params) {
		$this->parameters = $params;
	}

	/**
	 * Set a single parameter, useful for overriding.
	 *
	 * @param $key
	 * @param $value
	 */
	public function setParameter($key, $value){
		$this->parameters[$key] = $value;
	}

	/**
	 * Get all parameters from the GET variables.
	 *
	 * "Core" parameters are returned on a 0-based index, whereas named GET variables are returned with their respective name.
	 *
	 * @return array
	 */
	public function getParameters() {
		$data = $this->splitParts();

		if($data['parameters'] === null){
			// There were no parameters requested.
			return [];
		}
		else{
			return $data['parameters'];
		}
	}

	/**
	 * Get a single parameter from the GET variables.
	 *
	 * @param $key string|int The parameter to request
	 *
	 * @return null|string
	 */
	public function getParameter($key) {
		$data = $this->splitParts();

		if($data['parameters'] === null){
			// There were no parameters requested.
			return null;
		}
		elseif(array_key_exists($key, $data['parameters'])){
			// The parameter key was located and available.
			return $data['parameters'][$key];
		}
		else{
			// The parameter wasn't provided.
			return null;
		}
	}

	/**
	 * Just a shortcut function to make things consistent; returns a given POST variable.
	 * If the parameter does not exist, null is simply returned.
	 *
	 * It is still better to use the form system, as that has data sanitization and everything built in,
	 * but this allows a lower-level of access to the variables without resorting to raw access.
	 *
	 * @param $key string The POST variable to get
	 * @return null|string
	 */
	public function getPost($key){
		// Damn nested data.... :/
		$src = &$_POST;
		if(strpos($key, '[') !== false){
			$k1 = substr($key, 0, strpos($key, '['));
			$key = substr($key, strlen($k1) + 1, -1);
			$src = &$_POST[$k1];
		}

		return (isset($src[$key])) ? $src[$key] : null;
		// Yup, that's it... like I said, shortcut function.
	}

	/**
	 * Get the page model for the current page.
	 *
	 * @return PageModel
	 */
	public function getPageModel() {
		if ($this->_pagemodel === null) {
			$uri = $this->uriresolved;


			$pagefac = new ModelFactory('PageModel');
			$pagefac->where('rewriteurl = ' . $uri);
			$pagefac->where('fuzzy = 0');
			$pagefac->limit(1);
			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				$pagefac->whereGroup('OR', array('site = -1', 'site = ' . MultiSiteHelper::GetCurrentSiteID()));
			}

			$p = $pagefac->get();

			// Split this URL, it'll be used somewhere.
			$pagedat = $this->splitParts();

			if ($p) {
				// :) Found it
				$this->_pagemodel = $p;
			}
			elseif ($pagedat && isset($pagedat['baseurl'])) {
				// Is this even a valid controller?
				// This will allow a page to be called with it being in the pages database.
				$p = new PageModel($pagedat['baseurl']);
				if(!$p->exists()){
					$p->set('rewriteurl', $pagedat['rewriteurl']);
				}
				$this->_pagemodel = $p;
			}
			else {
				// No page in the database and no valid controller... sigh
				$this->_pagemodel = new PageModel();
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

	/**
	 * Simple check to see if the page request is a GET method.
	 *
	 * Returns true if it is GET, false if anything else.
	 *
	 * @return bool
	 */
	public function isGet() {
		return ($this->method == PageRequest::METHOD_GET);
	}

	/**
	 * Simple check to see if the page request is a json content type.
	 *
	 * @return bool
	 */
	public function isJSON(){
		return ($this->ctype == View::CTYPE_JSON);
	}

	/**
	 * Simple check to guess if the page request was an ajax-based request.
	 *
	 * @return bool
	 */
	public function isAjax(){
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	/**
	 * Get the user agent for this request.
	 *
	 * @return \Core\UserAgent
	 */
	public function getUserAgent(){
		return new \Core\UserAgent($this->useragent);
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
			if (ALLOW_NONXHR_JSON || $this->isAjax()) {
				$this->contentTypes[] = array(
					'type'   => View::CTYPE_JSON,
					'weight' => 1.0
				);
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

	private function _resolveLanguageHeader() {
		// I need to ensure there's at least a default.
		$header = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'en';

		// As per the Accept HTTP 1.1 spec, all accepts MUST be separated with a comma.
		$header = explode(',', $header);

		// Clear the array
		$this->acceptLanguages = array();
		$langs = [];

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

			$content = str_replace('-', '_', $content);

			$langs[$content] = $weight;
		}

		// Sort the languages by weight.
		arsort($langs);
		foreach($langs as $l => $w){
			$this->acceptLanguages[] = $l;
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

