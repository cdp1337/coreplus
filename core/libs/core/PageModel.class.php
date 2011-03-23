<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PageModel
 *
 * @author powellc
 */
class PageModel extends Model{

	private $_class;
	private $_method;
	private $_params;
	
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

			// Transpose some useful data for it.
			$this->_view->baseurl = $this->get('baseurl');
			$this->_view->setParameters($this->getParameters());
			$this->_view->templatename = $this->getTemplateName();
			$this->_view->mastertemplate = ($this->get('template'))? $this->get('template') : ConfigHandler::GetValue('/core/theme/default_template');
			
			$this->_view->setBreadcrumbs($this->getParentTree());
			$this->_view->addBreadcrumb($this->get('title'), $this->getResolvedURL());
		}
		
		return $this->_view;
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
	 * @return RenderablePage
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

		// @todo A save in here somewhere...
		
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
				// I need the array merge because getParentTree only returns << parents >>.
				return array_merge($p->getParentTree(), array($p));
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

		// Remove the first '/'.
		if($base{0} == '/') $base = substr($base, 1);


		// Logic for the Controller.
		if(strpos($base, '/')) $controller = substr($base, 0, strpos($base, '/'));
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


		// Logic for the Method.
		if(substr_count($base, '/') >= 1){
			$method = substr($base, strpos($base, '/')+1);
			if(strpos($method, '/')) $method = substr($method, 0, strpos($method, '/'));
		}
		else{
			// The controller may have an "Index" controller.  That doesn't need to be explictly called.
			//if(method_exists($controller, 'Index')) $method = 'Index';
			//else return null;
			$method = 'Index';
		}

		if(!method_exists($controller, $method)){
			return null;
		}
		
		
		// Provide some logic for security.
		// Keep any method starting with a '_' private by preventing
		// direct access from the browser.
		if($method{0} == '_') return null;


		// Logic for the parameters.
		if(substr_count($base, '/') >= 2){
			$params = substr($base, strpos($base, '/')+1);
			$params = substr($params, strpos($params, '/')+1);
			
			//if(strpos($params, '/')) $params = substr($params, 0, strpos($params, '/'));
			$params = explode('/', $params);
		}
		else{
			$params = null;
		}
		
		
		// Build these onto a base for a standardized callable URL.
		$baseurl = '/' . ((strpos($controller, 'Controller') == strlen($controller) - 10)? substr($controller, 0, -10) : $controller);
		// No need to add a method if it's the index.
		if(!($method == 'Index' && !$params)) $baseurl .= '/' . $method;
		$baseurl .= ($params)? '/' . implode('/', $params) : '';
		// Rewrite URL may be useful too!
		$rewriteurl = self::_LookupReverseUrl($baseurl);

		return array('controller' => $controller, 'method' => $method, 'parameters' => $params, 'baseurl' => $baseurl, 'rewriteurl' => $rewriteurl);
	}
	
	/**
	 * Lookup a url in the rewrite cache.  Useful for initial rewrite -> base conversions
	 * @param type $url 
	 */
	private static function _LookupUrl($url){
		if(!self::$_RewriteCache){
			$s = new SQLBuilderSelect();
			$s->select('rewriteurl, baseurl');
			$s->from(DB_PREFIX . 'page');
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
