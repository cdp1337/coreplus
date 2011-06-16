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
class WidgetModel extends Model{
	
	public static $Schema = array(
		'baseurl' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'null' => false,
		),
		'wrapper_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'default' => null,
			'null' => true,
		),
		'widget_template' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
			'default' => null,
			'null' => true,
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'comment' => '[cached]',
			'null' => false,
		),
		'access' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 512,
			'default' => '*',
			'comment' => '[cached]',
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
	);

	private $_class;
	private $_method;
	private $_params;
	
	private $_view;
	
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
			$a = self::SplitBaseURL($this->get('baseurl'));
			$this->_class = ($a)? $a['controller'] : null;
		}
		return $this->_class;
	}

	public function getControllerMethod(){
		if(!$this->_method){
			$a = self::SplitBaseURL($this->get('baseurl'));
			$this->_method = ($a)? $a['method'] : null;
		}
		return $this->_method;
	}

	public function getParameters(){
		if(!$this->_params){
			$a = self::SplitBaseURL($this->get('baseurl'));
			$this->_params = ($a)? $a['parameters'] : array();
		}
		return $this->_params;
	}

	public function getParameter($key){
		$p = $this->getParameters();
		return (array_key_exists($key, $p))? $p[$key] : null;
	}

	public function getTemplateName(){
		$t = 'widgets/';

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
			$this->_view->mode = View::MODE_WIDGET;
			$this->_view->mastertemplate = 'default/container.tpl';
			// These views don't actually have master templates, as they're just stub HTML bits.
			//$this->_view->mastertemplate = ($this->get('template'))? $this->get('template') : ConfigHandler::GetValue('/core/theme/default_template');
			
			// Set some information so the template can relate back to this object if needed.
			$this->_view->assignVariable('widget', $this);
		}
		
		return $this->_view;
	}


	public function getResolvedURL(){
		$s = self::SplitBaseURL($this->get('baseurl'));
		return ROOT_URL . substr($s['baseurl'], 1);
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
		//var_dump($transport); die();

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
		// This is just a stub function to mimic functionality of pages.
		// Widgets don't actually have aliases.
		return $url;
	}
	
	/**
	 * Lookup the rewrite url for a given url.
	 * @param type $url 
	 */
	private static function _LookupReverseUrl($url){
		// This is just a stub function to mimic functionality of pages.
		// Widgets don't actually have aliases.
		return $url;
	}

}
?>
