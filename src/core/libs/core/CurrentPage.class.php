<?php
/**
 * Singleton class for the currently viewing page.
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
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

class CurrentPage {
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

	private function __construct() {
		$uri = $_SERVER['REQUEST_URI'];

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


		// Trim off anything after the first & if present.
		//if(strpos($uri, '&') !== false) $uri = substr($uri, 0, strpos($uri, '&'));

		$p = PageModel::Find(
			array('rewriteurl' => $uri,
			      'fuzzy'      => 0), 1
		);

		// Split this URL, it'll be used somewhere.
		$pagedat = PageModel::SplitBaseURL($uri);

		if ($p) {
			// :) Found it
			$this->_page = $p;
		}
		elseif ($pagedat) {
			// Is this even a valid controller?
			// This will allow a page to be called with it being in the pages database.
			$p = new PageModel();
			$p->set('baseurl', $uri);
			$p->set('rewriteurl', $uri);
			$this->_page = $p;
		}
		else {
			// No page in the database and no valid controller... sigh
			return false;
		}

		//var_dump($p); die();

		// Make sure all the parameters from both standard GET and core parameters are tacked on.
		if ($pagedat && $pagedat['parameters']) {
			foreach ($pagedat['parameters'] as $k => $v) {
				$this->_page->setParameter($k, $v);
			}
		}
		if (is_array($_GET)) {
			foreach ($_GET as $k => $v) {
				if (is_numeric($k)) continue;
				$this->_page->setParameter($k, $v);
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

		$view                          = $this->_page->getView();
		$view->request['contenttype']  = $ctype;
		$view->response['contenttype'] = $ctype; // By default, this can be the same.
		$view->request['method']       = $_SERVER['REQUEST_METHOD'];
		$view->request['useragent']    = $_SERVER['HTTP_USER_AGENT'];
		$view->request['uri']          = $_SERVER['REQUEST_URI'];
		$view->request['uriresolved']  = $uri;
		$view->request['protocol']     = $_SERVER['SERVER_PROTOCOL'];


		//$this->_page->getView();
		//var_dump($this->_page); die();
	}

	/**
	 *
	 * @return CurrentPage
	 */
	public static function Singleton() {
		// This system has no function in CLI mode.
		if (EXEC_MODE != 'WEB') return null;

		if (!self::$_instance) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	public static function Render() {
		return self::Singleton()->_render();
	}

	/**
	 * Add a script to the current page.
	 *
	 * @deprecated 2013.09
	 *
	 * @param string $script
	 * @param string $location
	 */
	public static function AddScript($script, $location = 'head') {
		trigger_error('CurrentPage::AddScript is deprecated, please use \\Core\\view()->addScript instead.', E_USER_DEPRECATED);
		\Core\view()->addScript($script, $location);
	}

	/**
	 * Get all scripts registered to be displayed on this page.
	 *
	 * @return array
	 */
	public static function GetScripts() {
		return \Core\view()->scripts;
	}

	/**
	 * Add some content to the body before rendering is complete.
	 * This is useful to modify page content from a widget or plugin.
	 *
	 * @param string $content
	 * @param string $location 'pre' or 'post', prepend or append the regular body.
	 */
	public static function AddBodyContent($content, $location = 'pre') {
		$obj = self::Singleton();

		// Only allow content to be added once.
		if (in_array($content, $obj->_prebody)) return;
		if (in_array($content, $obj->_postbody)) return;

		// No? alright, add it to the requested location!
		if ($location == 'pre') $obj->_prebody[] = $content;
		else $obj->_postbody[] = $content;
	}

	/**
	 * Add a linked stylesheet file to the current page.
	 *
	 * @deprecated 2013.09
	 *
	 * @param string $link The link of the stylesheet
	 * @param type   $media Media to display the stylesheet with.
	 */
	public static function AddStylesheet($link, $media = "all") {
		trigger_error('CurrentPage::AddStylesheet is deprecated, please use \\Core\\view()->addStylesheet instead.', E_USER_DEPRECATED);
		\Core\view()->addStylesheet($link, $media);
	}

	/**
	 * Add an inline style to the current page.
	 *
	 * @deprecated 2013.09
	 *
	 * @param string $style The contents of the <style> tag.
	 */
	public static function AddStyle($style) {
		trigger_error('CurrentPage::AddStyle is deprecated, please use \\Core\\view()->addStyle instead.', E_USER_DEPRECATED);
		\Core\view()->addStyle($style);
	}

	/**
	 * @deprecated 2013.09
	 *
	 * @param $attribute
	 * @param $value
	 */
	public static function SetHTMLAttribute($attribute, $value) {
		trigger_error('CurrentPage::SetHTMLAttribute is deprecated, please use \\Core\\view()->setHTMLAttribute instead.', E_USER_DEPRECATED);
		\Core\view()->setHTMLAttribute($attribute, $value);
	}

	/**
	 * @deprecated 2013.09
	 *
	 * @return string
	 */
	public static function GetHead() {
		trigger_error('CurrentPage::GetHead is deprecated, please use \\Core\\view()->getHead instead.', E_USER_DEPRECATED);
		return View::GetHead();
	}

	/**
	 * @deprecated 2013.09
	 *
	 * @return string
	 */
	public static function GetFoot() {
		trigger_error('CurrentPage::GetFoot is deprecated, please use \\Core\\view()->getFoot instead.', E_USER_DEPRECATED);
		return View::GetFoot();
	}

	public static function GetBodyPre() {
		return trim(implode("\n", self::Singleton()->_prebody));
	}

	public static function GetBodyPost() {
		return trim(implode("\n", self::Singleton()->_postbody));
	}

	/**
	 * @deprecated 2013.09
	 *
	 * @param bool $asarray
	 *
	 * @return array|string
	 */
	public static function GetHTMLAttributes($asarray = false) {
		trigger_error('CurrentPage::GetHTMLAttributes is deprecated, please use \\Core\\view()->getHTMLAttributes instead.', E_USER_DEPRECATED);
		return \Core\view()->getHTMLAttributes($asarray);
	}

	private function _render() {

		// This may or may not be the original page...
		if ($this->_page) {
			$view = $this->_page->execute();
			Core::RecordNavigation($this->_page);
		}
		else {
			$view        = new View();
			$view->error = View::ERROR_NOTFOUND;
		}

		// Dispatch the hooks here if it's a 404 or 403.
		if ($view->error == View::ERROR_ACCESSDENIED || $view->error == View::ERROR_NOTFOUND) {
			// Let other things chew through it... (optionally)
			HookHandler::DispatchHook('/core/page/error-' . $view->error, $view);
		}

		if ($view->error != View::ERROR_NOERROR) {
			// Update some information in the view.
			// Transpose some useful data for it.
			$view->baseurl = '/Error/Error' . $view->error;
			$view->setParameters(array());
			$view->templatename   = '/pages/error/error' . $view->error . '.tpl';
			$view->mastertemplate = ConfigHandler::Get('/theme/default_template');
		}


		try {
			$data = $view->fetch();
		}
			// If something happens in the rendering of the template... consider it a server error.
		catch (Exception $e) {
			$view->error   = View::ERROR_SERVERERROR;
			$view->baseurl = '/Error/Error' . $view->error;
			$view->setParameters(array());
			$view->templatename   = '/pages/error/error' . $view->error . '.tpl';
			$view->mastertemplate = ConfigHandler::Get('/theme/default_template');
			$view->assignVariable('exception', $e);
			$data = $view->fetch();
		}

		// Yay, send the content type and status to the browser.
		switch ($view->error) {
			case View::ERROR_NOERROR:
				header('Status: 200 OK', true, $view->error);
				break;
			case View::ERROR_ACCESSDENIED:
				header('Status: 403 Forbidden', true, $view->error);
				break;
			case View::ERROR_NOTFOUND:
				header('Status: 404 Not Found', true, $view->error);
				break;
			case View::ERROR_SERVERERROR:
				header('Status: 500 Internal Server Error', true, $view->error);
				break;
			default:
				header('Status: 500 Internal Server Error', true, $view->error);
				break; // I don't know WTF happened...
		}

		if ($view->response['contenttype']) header('Content-Type: ' . $view->response['contenttype']);

		if (DEVELOPMENT_MODE) header('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());

		echo $data;


		// If the viewmode is regular and DEVELOPMENT_MODE is enabled, show some possibly useful information now that everything's said and done.
		if (DEVELOPMENT_MODE && $view->mode == View::MODE_PAGE && $view->response['contenttype'] == View::CTYPE_HTML) {
			echo '<pre class="xdebug-var-dump">';
			echo "Database Reads: " . Core::DB()->readCount() . "\n";
			echo "Database Writes: " . Core::DB()->writeCount() . "\n";
			//echo "Number of queries: " . DB::Singleton()->counter . "\n";
			echo "Amount of memory used by PHP: " . Core::FormatSize(memory_get_usage()) . "\n";
			echo "Total processing time: " . round(Core::GetProfileTimeTotal(), 4) * 1000 . ' ms' . "\n";
			if (FULL_DEBUG) {
				foreach (Core::GetProfileTimes() as $t) {
					echo "[" . Core::FormatProfileTime($t['timetotal']) . "] - " . $t['event'] . "\n";
				}
			}
			// Tack on what components are currently installed.
			echo '<b>Available Components</b>' . "\n";
			foreach (Core::GetComponents() as $l => $v) {
				echo $v->getName() . ' ' . $v->getVersion() . "\n";
			}

			echo '<b>Query Log</b>' . "\n";
			var_dump(Core::DB()->queryLog());
			echo '</pre>';
		}
	}
}
