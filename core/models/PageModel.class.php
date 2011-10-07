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
 * Description of PageModel
 *
 * @author powellc
 */
class PageModel extends Model{
	
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'rewriteurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => false,
		),
		'parenturl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'null' => true,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'comment' => '[Cached] Title of the page',
			'null' => true,
		),
		'metas' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'comment' => '[Cached] Serialized array of metainformation',
			'null' => false,
			'default' => ''
		),
		'theme_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
			'comment' => 'Allows the page to define its own theme and widget information.'
		),
		'page_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
			'comment' => 'Allows the specific page template to be overridden.'
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'comment' => '[Cached] Access string of the page',
			'null' => false,
			'default' => '*'
		),
		'fuzzy' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => '[Cached] If this url is fuzzy or an exact match',
			'null' => false,
			'default' => '0'
		),
		'admin' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'comment' => 'If this page is an administration page',
			'null' => false,
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);
	
	public static $Indexes = array(
		'primary' => array('baseurl'),
		'unique:rewrite_url' => array('rewriteurl'),
	);

	private $_class;
	private $_method;
	private $_params;
	
	/**
	 * The View component for this page.
	 * @var View
	 */
	private $_view;
	
	/**
	 * A cache of rewrite to baseurls to serve as a quick lookup.
	 * @var array
	 */
	private static $_RewriteCache = null;

	public function  __construct($key = null) {

		// Set some defaults first.
		// @todo Make these automatic somehow.
		$this->_updatedcolumn = 'updated';
		$this->_createdcolumn = 'created';
		
		$this->_linked = array(
			'Insertable' => array(
				'link' => Model::LINK_HASMANY,
				'on' => 'baseurl'
			),
		);

		parent::__construct($key);
	}
	
	

	/**
	 * Get the controller name based on the url.
	 * @return string
	 */
	public function getControllerClass(){
		if(!$this->_class){
			$a = PageModel::SplitBaseURL($this->get('baseurl'));
			$this->_class = ($a)? $a['controller'] : null;
		}
		return $this->_class;
	}

	public function getControllerMethod(){
		if(!$this->_method){
			$a = PageModel::SplitBaseURL($this->get('baseurl'));
			$this->_method = ($a)? $a['method'] : null;
		}
		return $this->_method;
	}

	public function getParameters(){
		if(!$this->_params){
			$a = PageModel::SplitBaseURL($this->get('baseurl'));
			$this->_params = ($a)? $a['parameters'] : array();
		}
		return $this->_params;
	}

	public function getParameter($key){
		$p = $this->getParameters();
		return (array_key_exists($key, $p))? $p[$key] : null;
	}
	public function setParameter($key, $val){
		$this->_params[$key] = $val;
	}

	public function getTemplateName(){
		$t = 'pages/';

		$c = $this->getControllerClass();
		// If it ends with Controller... just drop that bit off.
		// strlen and strrpos execute quicker than preg_match.
		if(strlen($c) - strrpos($c, 'Controller') == 10){
			// Trim that bit off.
			$c = substr($c, 0, -10);
		}
		$t .= strtolower($c) . '/';

		// Allow the specific template to be overridden.
		if(($override = $this->get('page_template'))) $t .= $override;
		else $t .= strtolower($this->getControllerMethod()) . '.tpl';

		return $t;
	}

	/**
	 *
	 * @return View
	 */
	public function getView(){
		if(!$this->_view){
			// Create a new data container for use in the transport of the data ultimately.
			$this->_view = new View();
			$this->_populateView();
		}
		
		return $this->_view;
	}
	
	/**
	 * Hijack an external view, (presumably from another page),
	 * and load in my stuff over top it.
	 * 
	 * This is useful because a single view can be passed around multiple functions,
	 * but it cannot be replaced entirely due to scope reasons.
	 * 
	 * @param View $view
	 */
	public function hijackView(View $view){
		$this->_view = $view;
		$this->_populateView();
	}

	public function getMetas(){
		if(!$this->get('metas')) return array();

		$m = $this->get('metas');
		$m = unserialize($m);

		if(!$m) return array();
		else return $m;
	}

	public function setMetas($metaarray){
		$m = serialize($metaarray);
		return $this->set('metas', $m);
	}

	public function getResolvedURL(){
		if($this->exists()){
			return ROOT_URL . substr($this->get('rewriteurl'), 1);
		}
		else{
			$s = self::SplitBaseURL($this->get('baseurl'));
			return ROOT_URL . substr($s['baseurl'], 1);
		}
	}

	

	/**
	 *
	 * @return View
	 */
	public function execute(){

		$transport = $this->getView();

		// I need a valid class/method pair!
		$c = $this->getControllerClass();
		$m = $this->getControllerMethod();
		if(!($c && $m)){
			$transport->error = View::ERROR_NOTFOUND;
			return $transport;
		}
		
		// Check if this Controller has an AccessString set statically.
		// This allows the method to be skipped entirely.
		if($c::$AccessString !== null){
			$transport->access = $c::$AccessString;
			
			if(!Core::User()->checkAccess($c::$AccessString)){
				$transport->error = View::ERROR_ACCESSDENIED;
				return $transport;
			}
		}
		
		// Populate the transport view object with some preliminary information
		// if the page exists and has it.
		// This information can get overwrote in the view method if requested.
		if($this->exists()){
			$transport->title = $this->get('title');
			$transport->access = $this->get('access');
		}

		$r = call_user_func(array($c, $m), $transport);

		// Multiple return values can be accepted.
		// nothing, an error code, or the page.
		if($r === null){
			// No return needed, assume this same object.
			$r = $transport;
		}
		elseif(is_numeric($r)){
			// Should be a valid error code.
			$transport->error = $r;
		}

		if($transport->error == View::ERROR_NOERROR && $this->exists()){
			// This information is cached.
			$this->set('title', $transport->title);
			$this->set('access', $transport->access);

			$this->save();
		}
		
		return $transport;
	}

	public function  save() {
		// Ensure some helper variables are set.
		if(!$this->get('rewriteurl')) $this->set('rewriteurl', $this->get('baseurl'));
		
		parent::save();
	}

	public function getParentTree(){
		// Allow pages that do not exist to have a bit of "extended" logic for determining the breadcrumbs.
		if(!$this->exists()){
			// Do a bit of custom logic here.
			
			// If the page is currently Edit and there is a View... handle that instance.
			if($this->getControllerMethod() == 'Edit' && method_exists($this->getControllerClass(), 'View')){
				$p = new PageModel(str_replace('/Edit/', '/View/', $this->get('baseurl')));
				// I need the array merge because getParentTree only returns << parents >>.
				return array_merge($p->getParentTree(), array($p));
			}
			
			// If the page is currently Delete and there is a View... handle that instance.
			if($this->getControllerMethod() == 'Delete' && method_exists($this->getControllerClass(), 'View')){
				$p = new PageModel(str_replace('/Delete/', '/View/', $this->get('baseurl')));
				// Only try to call the script if it exists.
				if($p->exists()){
					// I need the array merge because getParentTree only returns << parents >>.
				return array_merge($p->getParentTree(), array($p));
				}
			}
		}
		
		return $this->_getParentTree();
	}

	private function _getParentTree($antiinfiniteloopcounter = 5){
		if(!$this->get('parenturl')) return array();

		if($antiinfiniteloopcounter <= 0) return array();

		$antiinfiniteloopcounter--;
		$ret = array();
		$p = new PageModel($this->get('parenturl'));
		return array_merge($p->_getParentTree($antiinfiniteloopcounter), array($p));
	}
	
	private function _populateView(){
		// Transpose some useful data for it.
		$this->_view->error = View::ERROR_NOERROR;
		$this->_view->baseurl = $this->get('baseurl');
		$this->_view->setParameters($this->getParameters());
		$this->_view->templatename = $this->getTemplateName();
		$this->_view->mastertemplate = ($this->get('template'))? $this->get('template') : ConfigHandler::Get('/core/theme/default_template');

		$this->_view->setBreadcrumbs($this->getParentTree());
	}





	/****************** Helper Static Functions **************************/

	/**
	 * Split a base url into its corresponding parts, controller method and parameters.
	 * Also supports the rewriteurl.
	 * 
	 * @param string $url
	 * @return array
	 */
	public static function SplitBaseURL($base){
		
		if(!$base) return null;
		
		// Resolve any rewriteurl to the base.  won't affect it if it doesn't exist.
		$base = self::_LookupUrl($base);

		// Trim off both beginning and trailing slashes.
		$base = trim($base, '/');
		
		
		$args = null;
		// Support additional arguments
		if(($qpos = strpos($base, '?')) !== false){
			$argstring = substr($base, $qpos + 1);
			preg_match_all('/([^=&]*)={0,1}([^&]*)/', $argstring, $matches);
			$args = array();
			foreach($matches[1] as $k => $v){
				if(!$v) continue;
				$args[$v] = $matches[2][$k];
			}
			$base = substr($base, 0, $qpos);
		}
		
		// Logic for the Controller.
		$posofslash = strpos($base, '/');
		
		if($posofslash) $controller = substr($base, 0, $posofslash);
		else $controller = $base;

		if(class_exists($controller . 'Controller') && is_subclass_of($controller . 'Controller', 'Controller')){
			$controller = $controller . 'Controller';
		}
		elseif(class_exists($controller) && is_subclass_of($controller, 'Controller')){
			$controller = $controller;
		}
		else{
			return null;
		}
		
		// Trim the base.
		if($posofslash !== false) $base = substr($base, $posofslash + 1);
		else $base = false;

		// Logic for the Method.
		//if(substr_count($base, '/') >= 1){
		if($base){
			
			$posofslash = strpos($base, '/');
			
			// The method can be extended.
			// This means that a method can be in the format of Sites/Edit, which should resolve to Sites_Edit.
			// This only taks effect if the method exists on the controller.
			if($posofslash){
				$method = str_replace('/', '_', $base);
				while(!method_exists($controller, $method) && strpos($method, '_')){
					$method = substr($method, 0, strrpos($method, '_'));
				}
			}
			else{
				$method = $base;
			}
			
			// Now trim the base again based on the length of the method.
			$base = substr($base, strlen($method) + 1);
		}
		else{
			// The controller may have an "Index" controller.  That doesn't need to be explictly called.
			$method = 'Index';
		}

		// One last check that the method exists, (because there's only 1 scenerio that checks above)
		if(!method_exists($controller, $method)){
			return null;
		}
		
		
		// Provide some logic for security.
		// Keep any method starting with a '_' private by preventing
		// direct access from the browser.
		if($method{0} == '_') return null;


		// Logic for the parameters.
		$params = ($base !== false) ? explode('/', $base) : null;
		
		
		// Build these onto a base for a standardized callable URL.
		$baseurl = '/' . ((strpos($controller, 'Controller') == strlen($controller) - 10)? substr($controller, 0, -10) : $controller);
		// No need to add a method if it's the index.
		if(!($method == 'Index' && !$params)) $baseurl .= '/' . str_replace('_', '/', $method);
		$baseurl .= ($params)? '/' . implode('/', $params) : '';
		// Rewrite URL may be useful too!
		$rewriteurl = self::_LookupReverseUrl($baseurl);
		
		// Keep the arguments on the rewrite version.
		if($args){
			$rewriteurl .= '?' . $argstring;
			if($params) $params = array_merge($params, $args);
			else $params = $args;
		}
		
		// Tack on the "arguments" too, these are 

		return array('controller' => $controller, 'method' => $method, 'parameters' => $params, 'baseurl' => $baseurl, 'rewriteurl' => $rewriteurl);
	}
	
	/**
	 * Lookup a url in the rewrite cache.  Useful for initial rewrite -> base conversions
	 * @param type $url 
	 */
	private static function _LookupUrl($url){
		if(!self::$_RewriteCache){
			$s = new Dataset();
			$s->select('rewriteurl, baseurl');
			$s->table(DB_PREFIX . 'page');
			$rs = $s->execute();
			self::$_RewriteCache = array();
			foreach($rs as $row){
				self::$_RewriteCache[$row['rewriteurl']] = $row['baseurl'];
			}
		}
		
		return (isset(self::$_RewriteCache[$url]))? self::$_RewriteCache[$url] : $url;
	}
	
	/**
	 * Lookup the rewrite url for a given url.
	 * @param type $url 
	 */
	private static function _LookupReverseUrl($url){
		// Lookup something, just to ensure it's in the cache.
		self::_LookupUrl('/');
		
		return (($key = array_search($url, self::$_RewriteCache)) !== false)? $key : $url;
	}
	
	/**
	 * Get all pages, (with an optional where clause), as a valid option array
	 * 
	 * This array contains key of "baseurl", value of "parent &raquo; title ( url )"
	 * that is directly pluggable into the Form system or a manual foreach loop.
	 * 
	 * @param mixed $where Either a ModelFactory (usually with custom-crafted where clauses),
	 *                     or an array of valid SQLBuilder where clauses
	 *                     or a string of the where clause
	 *                     or false to omit the where clause.
	 * @param mixed $blanktext The text to include with the blank entry
	 *                         If false, no blank field is included.
	 * @return array
	 */
	public static function GetPagesAsOptions($where = false, $blanktext = false){
		if($where instanceof ModelFactory){
			$f = $where;
		}
		elseif(!$where){
			$f = new ModelFactory('PageModel');
		}
		else{
			$f = new ModelFactory('PageModel');
			$f->where($where);
		}
		
		// Get the pages
		$pages = $f->get();

		// Assemble a list of page titles for quick reference.
		//$titles = array();
		//foreach($pages as $p){
		//	$titles[$p->get('baseurl')] = $p->get('title');
		//}

		// Now I can assemble the list of options with useful labels
		$opts = array();
		foreach($pages as $p){
			$t = '';
			foreach($p->getParentTree() as $subp){
				$t .= $subp->get('title') . ' &raquo; ';
			}
			$t .= $p->get('title');
			$t .= ' ( ' . $p->get('rewriteurl') . ' )';
			$opts[$p->get('baseurl')] = $t;
		}

		// Sort'em
		asort($opts);

		// Default should always be at the top (if requested).
		if($blanktext) $opts = array_merge(array("" => $blanktext), $opts);
		
		// And here ya go!
		return $opts;
	}
}
?>
