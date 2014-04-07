<?php
/**
 * Filter system
 *
 * @package Core
 * @since 2.3.2
 */
class FilterForm {

	/**
	 * Set to true to look for (and remember), sortkey and sortdir as well.
	 *
	 * @var bool
	 */
	public $hassort = false;

	/**
	 * Set to true to look for (and remember), the pagination values.
	 *
	 * @var bool
	 */
	public $haspagination = false;

	private $_name = null;

	private $_elements = array();

	private $_elementindexes = array();

	/**
	 * null or an array of valid sort keys, the first being the default.
	 *
	 * @var null|array
	 */
	private $_sortkeys = null;

	/**
	 * null or the sort key of the current view.
	 *
	 * @var null
	 */
	private $_sortkey = null;

	/**
	 * Sorting direction, think ascending or descending.
	 *
	 * @var string "up" or "down"
	 */
	private $_sortdir = 'down';

	/**
	 * The limit for this filterset, only takes effect if $haspagination is set to true.
	 *
	 * @var int
	 */
	private $_limit = 50;

	/**
	 * The total number of entries for this filterset.  Set automatically from applyToFactory and callable externally.
	 *
	 * @var null|int
	 */
	private $_total = null;

	/**
	 * The current page, only takes effect if $haspagination is set to true.
	 *
	 * @var int
	 */
	private $_currentpage = 1;

	/**
	 * Standard link type for a given model factory and its filter.
	 *
	 * format: key = value
	 */
	const LINK_TYPE_STANDARD = ' = ';

	/**
	 * Greater than link type for a given model factory and its filter.
	 *
	 * format: key > value
	 */
	const LINK_TYPE_GT = ' > ';

	/**
	 * Greater than or equal-to link type for a given model factory and its filter.
	 *
	 * format: key >= value
	 */
	const LINK_TYPE_GE = ' >= ';

	/**
	 * Less than link type for a given model factory and its filter.
	 *
	 * format: key < value
	 */
	const LINK_TYPE_LT = ' < ';

	/**
	 * Less than or equal-to link type for a given model factory and its filter.
	 *
	 * format: key <= value
	 */
	const LINK_TYPE_LE = ' <= ';

	/**
	 * Use the LIKE connector to find anything that starts with the value.
	 *
	 * format: key LIKE value%
	 */
	const LINK_TYPE_STARTSWITH = '_startswith_';

	/**
	 * Use the LIKE connector to find anything that contains the value.
	 *
	 * format: key LIKE %value%
	 */
	const LINK_TYPE_CONTAINS = '_contains_';

	/**
	 * Create a new filter form object
	 */
	public function __construct(){
		if(!isset($_SESSION['filters'])) $_SESSION['filters'] = array();
	}

	/**
	 * Set the name for this filter, required for any session saving/lookup.
	 *
	 * @param string $filtername
	 */
	public function setName($filtername){
		$this->_name = $filtername;
	}

	/**
	 * Add an element to this filter set.
	 *
	 * This is the exact same as the native Form system.
	 *
	 * @param string|FormElement $element Type of element, (or the form element itself)
	 * @param null|array         $atts [optional] An associative array of parameters for this form element
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
		$s = array();
		$p = array();

		// Check the sort keys?
		if($this->hassort){
			if($request->getParameter('sortkey')){
				$this->setSortKey($request->getParameter('sortkey'));
				$s['sortkey'] = $this->_sortkey;
			}
			if($request->getParameter('sortdir')){
				$this->setSortDirection($request->getParameter('sortdir'));
				$s['sortdir'] = $this->_sortdir;
			}
		}

		// Did the user change a filter?
		// If a filter was changed, reset back to page 1!
		if($request->getParameter('filter')){
			$filters = $request->getParameter('filter');

			foreach($filters as $f => $v){
				if(!isset($this->_elementindexes['filter[' . $f . ']'])) continue;
				/** @var $el FormElement */
				$el = $this->_elementindexes['filter[' . $f . ']'];
				$el->setValue($v);

				// Remember this for the session data.
				$a[$f] = $v;
				$this->setPage(1);
				$p['page'] = 1;
			}
		}
		// How 'bout the pagination?
		elseif($this->haspagination && $request->getParameter('page')){
			$this->setPage($request->getParameter('page'));
			$p['page'] = $this->_currentpage;

			// Don't change the filter sets, those have been cached and are fine as-is.
		}
		else{
			// No pagination or filters were modified... don't do anything.
		}


		if(sizeof($a)){
			$_SESSION['filters'][$this->_name] = $a;
		}
		if(sizeof($s)){
			$_SESSION['filtersort'][$this->_name] = $s;
		}
		if(sizeof($p)){
			$_SESSION['filterpage'][$this->_name] = $p;
		}
	}

	/**
	 * Load the values from the session data.
	 *
	 * This is automatically called by the load function.
	 */
	public function loadSession(){
		if(!$this->_name){
			// Ok, no name.. no loading.
			return;
		}
		if(isset($_SESSION['filters'][$this->_name])){
			$filters = $_SESSION['filters'][$this->_name];
			foreach($filters as $f => $v){
				if(!isset($this->_elementindexes['filter[' . $f . ']'])) continue;
				/** @var $el FormElement */
				$el = $this->_elementindexes['filter[' . $f . ']'];
				$el->setValue($v);
			}
		}

		if(isset($_SESSION['filtersort'][$this->_name])){
			$this->_sortkey = $_SESSION['filtersort'][$this->_name]['sortkey'];
			$this->_sortdir = $_SESSION['filtersort'][$this->_name]['sortdir'];
		}

		if(isset($_SESSION['filterpage'][$this->_name])){
			$this->_currentpage = $_SESSION['filterpage'][$this->_name]['page'];
		}
	}

	/**
	 * Fetch this filter set as a string
	 *
	 * (should probably be called fetch, but whatever)
	 *
	 * @return string
	 */
	public function render(){
		return $this->_render(false);
	}

	/**
	 * Fetch this filter set as an HTML string
	 *
	 * This result set will be readonly however!
	 *
	 * @return string
	 */
	public function renderReadonly(){
		return $this->_render(true);
	}

	/**
	 * Return true/false on if this filter has any filters set by the user.
	 *
	 * Essentially will just check if all elements have their value set to "" or to null.
	 *
	 * @return boolean
	 */
	public function hasSet(){
		foreach($this->_elements as $element){
			/** @var $element FormElement */
			if($element->get('value') === ''){
				continue;
			}

			if($element->get('value') === null){
				continue;
			}

			// Haven't continued yet?
			return true;
		}

		// No element has anything set?
		return false;
	}

	/**
	 * Fet this filter set's pagination options as a string.
	 *
	 * @return string
	 */
	public function pagination(){
		if(!$this->haspagination) return null;

		// Give me the total number of pages given the current criteria.
		$maxpages = ceil($this->_total / $this->_limit);

		// If there are no more pages beyond the first, just return null, no pagination available!
		if($maxpages <= 1) return null;

		// The current page can't exceed past the maxpages.
		$currentpage = min($maxpages, $this->_currentpage);
		// nor can it be < 0
		if($currentpage < 1) $currentpage = 1;

		//$currentpage = 22;

		// The number of results on either side of the current.
		$offset = 4;

		// If there are more than so many pages, only display a subset of them.
		if($maxpages > 10){
			if($currentpage > $maxpages - (($offset)*2-1)){
				$displaymin = $maxpages - (($offset)*2+1);
				$displaymax = $maxpages;
			}
			elseif($currentpage < (($offset)*2)){
				$displaymin = 1;
				$displaymax = ($offset*2+1);
			}
			else{
				$displaymin = $currentpage - ($offset);
				$displaymax = $currentpage + ($offset);
			}
		}
		else{
			$displaymin = 1;
			$displaymax = $maxpages;
		}

		// Calculate the "Displaying x - y records of z total" numbers here!

		// Total number of records, simple.
		$records_total = $this->_total;
		// Current number of records on the page, only *not* the total if the total is < limit.
		$records_current = min($this->_limit, $this->_total);
		// Starting position.... always go n+1 here because people are used to seeing "1-10" instead of "0-9"
		$records_start = ($currentpage - 1) * $this->_limit + 1;
		// And the ending position.
		$records_end = min($records_start + $records_current - 1, $this->_total);


		$tpl = \Core\Templates\Template::Factory('forms/filters-pagination.tpl');
		$tpl->assign('page_current', $currentpage);
		$tpl->assign('page_max', $maxpages);
		$tpl->assign('display_min', $displaymin);
		$tpl->assign('display_max', $displaymax);
		$tpl->assign('records_total', $records_total);
		$tpl->assign('records_current', $records_current);
		$tpl->assign('records_start', $records_start);
		$tpl->assign('records_end', $records_end);
		return $tpl->fetch();
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

	/**
	 * Set the sort direction for this filterset.
	 *
	 * @since 2.4.0
	 * @param $dir "up" or "down".  It must be up or down because fontawesome has those keys instead of "asc" and "desc" :p
	 *
	 * @return bool true/false on success or failure.
	 */
	public function setSortDirection($dir){
		$dir = strtolower($dir);
		switch($dir){
			case 'down':
			case 'up':
				$this->_sortdir = $dir;
				return true;
			default:
				return false;
		}
	}

	/**
	 * Get the sort direction, either up or down.
	 *
	 * @since 2.4.0
	 * @return string|null
	 */
	public function getSortDirection(){
		if(!$this->hassort) return null;
		else return $this->_sortdir;
	}

	/**
	 * Get the sort key.
	 *
	 * @return null|string
	 */
	public function getSortKey(){
		if(!$this->hassort) return null;
		elseif($this->_sortkey) return $this->_sortkey;
		elseif(is_array($this->_sortkeys) && sizeof($this->_sortkeys)) return $this->_sortkeys[0];
		else return null;
	}

	/**
	 * Add a single sort key onto this Filter.
	 *
	 * @since 3.4.0
	 * @param string $key
	 *
	 * @return bool
	 */
	public function addSortKey($key){
		if(!$this->hassort) return false;

		if(!is_array($this->_sortkeys)){
			$this->_sortkeys = [];
		}

		$this->_sortkeys[] = $key;
		return true;
	}

	/**
	 * Set the valid sort keys for this filterset.
	 *
	 * @since 2.4.0
	 * @param array $arr
	 *
	 * @return bool true/false on success or failure.
	 */
	public function setSortkeys($arr){
		if(!$this->hassort) return false;
		if(!is_array($arr)) return false;

		$this->_sortkeys = $arr;
		return true;
	}

	/**
	 * Set the active sort key currently.
	 * If sotkeys is populated, it MUST be one of the keys in that array!
	 *
	 * @since 2.4.0
	 * @param string $key
	 *
	 * @return bool true/false on success or failure.
	 */
	public function setSortKey($key){
		if(is_array($this->_sortkeys) && sizeof($this->_sortkeys)){
			// It's set, enforce type!
			if(in_array($key, $this->_sortkeys)){
				$this->_sortkey = $key;
				return true;
			}
			else{
				return false;
			}
		}
		else{
			// Ok, just proceed blindly.
			$this->_sortkey = $key;
			return true;
		}
	}

	/**
	 * Get the total count for the number of records filtered.
	 *
	 * @return int|null
	 */
	public function getTotalCount(){
		return $this->_total;
	}

	/**
	 * Set the limit for pagination, will default to 50.
	 *
	 * @since 2.4.0
	 * @param int $limit
	 */
	public function setLimit($limit){
		$this->_limit = (int) $limit;
	}

	public function setPage($page){
		$this->_currentpage = (int) $page;
	}

	/**
	 * Get the sort keys as an order clause, passable into the dataset system.
	 *
	 * @since 2.4.0
	 */
	public function getOrder(){
		$key = $this->getSortKey();
		$dir = $this->getSortDirection();

		if(!$key) return null;

		return $key . ' ' . ($dir == 'up' ? 'ASC' : 'DESC');
	}

	/**
	 * Set the total count for the number of records.
	 * This is externally available in case the factory is modified externally, which is perfectly allowed.
	 *
	 * @param int $count
	 */
	public function setTotalCount($count){
		$this->_total = (int) $count;
	}

	/**
	 * Given all the user defined filter, sort, and what not, apply those values to the ModelFactory if possible.
	 *
	 * @since 2.4.0
	 * @param ModelFactory $factory
	 */
	public function applyToFactory(ModelFactory $factory){
		if($this->hassort){
			$factory->order($this->getOrder());
		}

		if($this->haspagination){
			// Determine the starting count if the page is requested.
			if($this->_currentpage > 1){
				$startat = $this->_limit * ($this->_currentpage - 1);
				$factory->limit($startat . ', ' . $this->_limit);
			}
			else{
				$factory->limit($this->_limit);
			}
		}

		foreach($this->_elements as $el){
			/** @var $el FormElement */
			$name = $el->get('name');
			$idxname = $name;

			if(strpos($name, 'filter[') === 0){
				$name = substr($name, 7, -1);
			}

			// If this element is not in the index of elements, skip to the next element.
			if(!isset($this->_elementindexes[$idxname])){
				continue;
			}

			// If this doesn't have a link attribute, just skip.
			if(!$el->get('link')){
				continue;
			}

			// No value, just skip.
			if($el->get('value') === '' || $el->get('value') === null){
				continue;
			}

			// If there is a "" option, interpret that as empty and allow "0" to be used.
			if($el->get('value') === '0'){
				if($el->get('options') && isset($el->get('options')[''])){
					// '' is set... proceed.
				}
				else{
					continue;
				}
			}

			$value = $el->get('value');

			// Was there a prefix and/or suffix requested?
			if($el->get('linkvalueprefix')){
				$value = $el->get('linkvalueprefix') . $value;
			}
			if($el->get('linkvaluesuffix')){
				$value = $value . $el->get('linkvaluesuffix');
			}

			// If this link is a date object, convert a date string to its unix timestamp representation.
			if($el instanceof FormDateInput || $el->get('dateformat')){
				// Default to a unix timestamp, but allow the user to override this.
				// This is useful for saving a date in the datastore as a human-readable format.
				$format = $el->get('dateformat') ? $el->get('dateformat') : 'U';
				$date = new CoreDateTime($value);
				$value = $date->getFormatted($format, Time::TIMEZONE_GMT);
			}


			if($el->get('linkname')){
				$name = $el->get('linkname');
			}

			switch($el->get('link')){
				case FilterForm::LINK_TYPE_STANDARD:
				case FilterForm::LINK_TYPE_GT:
				case FilterForm::LINK_TYPE_GE:
				case FilterForm::LINK_TYPE_LT:
				case FilterForm::LINK_TYPE_LE:
					$factory->where($name . $el->get('link') . $value);
					break;
				case FilterForm::LINK_TYPE_STARTSWITH:
					$factory->where($name . ' LIKE ' . $value . '%');
					break;
				case FilterForm::LINK_TYPE_CONTAINS:
					$factory->where($name . ' LIKE %' . $value . '%');
					break;
			}
		}

		// Might as well update the count now, it can always be updated later.
		$this->setTotalCount($factory->count());
	}

	private function _render($readonly = false){
		$filterset = $this->hasSet();

		$tpl = \Core\Templates\Template::Factory('forms/filters.tpl');
		$tpl->assign('filtersset', $filterset);
		$tpl->assign('elements', $this->_elements);
		$tpl->assign('hassort', $this->hassort);
		$tpl->assign('sortkey', $this->getSortKey());
		$tpl->assign('sortdir', $this->getSortDirection());
		$tpl->assign('readonly', $readonly);
		$tpl->assign('records_total', $this->_total);
		$tpl->assign('records_current', min($this->_limit, $this->_total));
		return $tpl->fetch();
	}
}
