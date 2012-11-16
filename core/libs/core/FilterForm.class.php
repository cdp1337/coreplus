<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 10/28/12
 * Time: 10:28 PM
 * To change this template use File | Settings | File Templates.
 */
class FilterForm {

	private $_name = null;

	private $_elements = array();

	private $_elementindexes = array();

	/**
	 * Create a new filter form object
	 */
	public function __construct(){
		if(!isset($_SESSION['filters'])) $_SESSION['filters'] = array();
	}

	/**
	 * Set the name for this filter, required for any session saving/lookup.
	 *
	 * @param $filtername
	 */
	public function setName($filtername){
		$this->_name = $filtername;
	}

	/**
	 * Add an element to this filter set.
	 *
	 * This is the exact same as the native Form system.
	 *
	 * @param string     $element Type of element, (or the form element itself)
	 * @param null|array $atts [optional] An associative array of parameters for this form element
	 */
	public function addElement($element, $atts = null) {
		// Since this allows for just plain names to be submitted, translate
		// them to the form object to be rendered.

		if ($element instanceof FormElement || is_a($element, 'FormElement')) {
			// w00t, already in the right format!
			if ($atts) $element->setFromArray($atts);
		}
		elseif ($element instanceof FormGroup) {
			// w00t, already in the right format!
			if ($atts) $element->setFromArray($atts);
		}
		else {
			if (!isset(Form::$Mappings[$element])) $element = 'text'; // Default.

			$element = new Form::$Mappings[$element]($atts);
		}

		// Lastly...
		/** @var $element FormElement */

		// Make sure the name is correct and a few useful attributes are set.
		if(strpos($element->get('name'), 'filter[') === false){
			$element->set('name', 'filter[' . $element->get('name') . ']');
		}
		$element->set('class', $element->get('class') . ' filter');

		$this->_elements[] = $element;
		$this->_elementindexes[$element->get('name')] = $element;
	}

	/**
	 * Load the values from either the page request or the session data.
	 *
	 * @param PageRequest $request
	 */
	public function load(PageRequest $request){
		// First, load everything from the session.
		$this->loadSession();

		$a = array();

		if($request->getParameter('filter')){
			$filters = $request->getParameter('filter');
		}
		else{
			// Ok
			return;
		}

		foreach($filters as $f => $v){
			if(!isset($this->_elementindexes['filter[' . $f . ']'])) continue;
			/** @var $el FormElement */
			$el = $this->_elementindexes['filter[' . $f . ']'];
			$el->setValue($v);

			// Remember this for the session data.
			$a[$f] = $v;
		}

		$_SESSION['filters'][$this->_name] = $a;
	}

	/**
	 * Load the values from the session data.
	 *
	 * This is automatically called by the load function.
	 */
	public function loadSession(){
		if($this->_name && isset($_SESSION['filters'][$this->_name])){
			$filters = $_SESSION['filters'][$this->_name];
		}
		else{
			// Ok
			return;
		}

		foreach($filters as $f => $v){
			if(!isset($this->_elementindexes['filter[' . $f . ']'])) continue;
			/** @var $el FormElement */
			$el = $this->_elementindexes['filter[' . $f . ']'];
			$el->setValue($v);
		}

		// No need to save it back to the session, it just came from there!
	}

	/**
	 * Fetch this filter set as a string
	 *
	 * (should probably be called fetch, but whatever)
	 *
	 * @return string
	 */
	public function render(){
		$filterset = false;
		foreach($this->_elements as $element){
			/** @var $element FormElement */
			if($element->get('value') !== ''){
				$filterset = true;
				break;
			}
		}

		$tpl = new Template();
		$tpl->assign('filtersset', $filterset);
		$tpl->assign('elements', $this->_elements);
		return $tpl->fetch('forms/filters.tpl');
	}

	/**
	 * Get one element's value based on its name
	 *
	 * @param string $name The name of the element to retrieve
	 * @return mixed|null Value or null if not set.
	 */
	public function get($name){
		if(strpos($name, 'filter[') === false){
			$name = 'filter[' . $name . ']';
		}

		if(!isset($this->_elementindexes[$name])) return null;
		return $this->_elementindexes[$name]->get('value');
	}
}
