<?php
/**
 * File for class Table definition in the coreplus project
 *
 * @package Core\ListingTable
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140406.2004
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

namespace Core\ListingTable;
use Core\Templates\Template;


/**
 * A short teaser of what Table does.
 *
 * More lengthy description of what Table does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Table
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 *
 * @package Core\ListingTable
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Table implements \Iterator {

	/** @var string The session / key name of this listing table. */
	private $_name;

	/** @var \ModelFactory|null The underlying factory to control this listing table. */
	private $_modelFactory;

	/** @var \FilterForm|null The underlying FilterForm to handle pagination and filters on this listing table. */
	private $_filters;

	/** @var \Form|null The underlying Form object used with quick-edit on this listing table. */
	private $_editform;

	/** @var array The columns on this listing table. */
	private $_columns = [];

	/** @var bool Set to true when the filters have been applied to the Model, (set automatically). */
	private $_applied = false;

	/** @var bool Set automatically when a column is added with a sortable flag. */
	private $_hassort = false;

	/** @var array|null Array of the results, pulled directly from the ModelFactory's get method. */
	private $_results;

	/** @var null|\ViewControls Any controls added to this table as a whole. */
	private $_controls = null;

	public function __construct(){
		// Set the name by default to the system request's baseurl.
		// This is usually a safe default, but can be set by the caller script if desired.
		$request = \Core\page_request();
		$this->setName($request->getBaseURL());
	}


	//-----------------------------------------------------------------------\\
	//                                  GETTERS
	//-----------------------------------------------------------------------\\

	/**
	 * Get the underlying ModelFactory for this listing table, or null if none exists.
	 *
	 * @return \ModelFactory|null
	 */
	public function getModelFactory(){
		return $this->_modelFactory;
	}

	/**
	 * Get the underlying FilterForm object to control this listing table's pagination and filters.
	 *
	 * Will auto-create one if it doesn't exist.
	 *
	 * @return \FilterForm
	 */
	public function getFilters(){
		if($this->_filters === null){
			$this->_filters = new \FilterForm();
			$this->_filters->setName($this->_name);
			// Listing tables by default have pagination and sorting!
			$this->_filters->haspagination = true;
			$this->_filters->hassort = true;
		}

		return $this->_filters;
	}

	/**
	 * Get the value of the corresponding Filter element.
	 *
	 * @param $filter string
	 * @return mixed
	 */
	public function getFilterValue($filter){
		return $this->getFilters()->get($filter);
	}

	/**
	 * Get any/all the listings on this table.
	 *
	 * @return array|\Model|null
	 */
	public function getListings(){
		if(!$this->_applied){
			$this->getFilters()->applyToFactory($this->getModelFactory());
			$this->_results = $this->getModelFactory()->get();
			$this->_applied = true;
		}

		return $this->_results;
	}

	/**
	 * Get the total count for the number of records filtered.
	 *
	 * @return int|null
	 */
	public function getTotalCount(){
		if(!$this->_applied){
			$this->getFilters()->applyToFactory($this->getModelFactory());
			$this->_results = $this->getModelFactory()->get();
			$this->_applied = true;
		}

		return $this->getFilters()->getTotalCount();
	}

	/**
	 * Get the edit form for this listing table.
	 *
	 * @return \Form
	 */
	public function getEditForm(){
		if($this->_editform === null){
			$this->_editform = new \Form();
			$this->_editform->set('orientation', 'vertical');
		}

		return $this->_editform;
	}

	/**
	 * Get the controls for this table, if any.
	 *
	 * @return \ViewControls
	 */
	public function getControls(){
		if($this->_controls === null){
			$this->_controls = new \ViewControls();
			$this->_controls->setProxyText('Bulk Actions');
			$this->_controls->setProxyForce(true);
		}

		return $this->_controls;
	}



	//-----------------------------------------------------------------------\\
	//                                  SETTERS
	//-----------------------------------------------------------------------\\

	/**
	 * Add a new filter for this listing table.
	 *
	 * @param string $type
	 * @param array $atts
	 */
	public function addFilter($type, $atts){
		$form = $this->getFilters();
		$form->addElement($type, $atts);
	}

	/**
	 * Add a new Column onto this Listing Table.
	 *
	 * Will handle the filter sortable work automatically.
	 *
	 * @param string      $title
	 * @param string|null $sortkey
	 * @param boolean     $visible
	 */
	public function addColumn($title, $sortkey = null, $visible = true){
		$c = new Column();
		$c->title = $title;
		$c->sortkey = $sortkey;
		$c->visible = $visible;
		$this->_columns[] = $c;

		if($sortkey){
			$f = $this->getFilters();
			$f->addSortKey($sortkey);
			$this->_hassort = true;
		}
	}

	/**
	 * Set the model name, (and the underlying Factory object).
	 *
	 * @param string $name
	 */
	public function setModelName($name){
		$this->_modelFactory = new \ModelFactory($name);
	}

	/**
	 * Set the model factory itself.
	 *
	 * This is useful if it's a child model from another Model.
	 *
	 * @param \ModelFactory $factory
	 */
	public function setModelFactory(\ModelFactory $factory){
		$this->_modelFactory = $factory;
	}

	/**
	 * Set the name for this table (and the corresponding filters), required for any session saving/lookup.
	 *
	 * @param string $name
	 */
	public function setName($name){
		$this->_name = $name;

		if($this->_filters !== null){
			$this->_filters->setName($this->_name);
		}
	}

	/**
	 * Set the callsmethod attribute on the edit form.
	 *
	 * @param string $method
	 */
	public function setEditFormCaller($method){
		$this->getEditForm()->set('callsmethod', $method);
	}

	/**
	 * Set the limit of results to show before pagination kicks in.
	 *
	 * @param $limit
	 */
	public function setLimit($limit){
		$this->getFilters()->setLimit($limit);
	}

	/**
	 * Set the default sort key and direction.
	 *
	 * This should be done prior to loading the results!
	 *
	 * @param string $key       The key of the column to sort by
	 * @param string $direction "DESC" or "ASC" for descending or ascending sort
	 */
	public function setDefaultSort($key, $direction = 'DESC'){
		$this->getFilters()->setSortKey($key);
		$this->getFilters()->setSortDirection($direction);
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
		$control = new \ViewControl();

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
			$control->link = \Core::ResolveLink($link);
		}

		$this->getControls()->addLink($control);
	}



	//-----------------------------------------------------------------------\\
	//                                  OTHER/ACTIONABLE
	//-----------------------------------------------------------------------\\

	/**
	 * Load the underlying Filters from a given request, (optionally).
	 *
	 * @param \PageRequest|null $request
	 */
	public function loadFiltersFromRequest($request = null){
		if($request === null){
			$request = \Core\page_request();
		}

		$this->getFilters()->load($request);
	}

	public function render($section){
		if(!$this->_applied){
			$this->getFilters()->applyToFactory($this->getModelFactory());
			$this->_results = $this->getModelFactory()->get();
			$this->_applied = true;
		}

		switch($section){
			case 'head':
				return $this->_renderHead();
			case 'foot':
				return $this->_renderFoot();
			case 'filters':
				return $this->_renderFilters();
			case 'pagination':
				return $this->_renderPagination();
			default:
				return 'Unknown section to render for this listing table, [' . $section . ']';
		}
	}

	/**
	 * Send a CSV header and setup all necessary options to the View object to provide a download.
	 *
	 * All the data headers will be rendered automatically, (with the exception of the final 'controls' column).
	 *
	 * @param \View  $view  Page view to manipulate
	 * @param string $title Title of the file, (will get converted to a valid URL)
	 */
	public function sendCSVHeader(\View $view, $title = 'csv export'){
		$filename = \Core\str_to_url($title) . '-' . date('Y-m-d') . '.csv';

		$view->mode = \View::MODE_NOOUTPUT;
		$view->contenttype = 'text/csv';
		$view->addHeader('Content-Disposition', 'attachment; filename=' . $filename);

		// Set the limits and everything as necessary.
		$this->setLimit(99999);
		if(!$this->_applied){
			$this->getFilters()->applyToFactory($this->getModelFactory());
			$this->_results = $this->getModelFactory()->get();
			$this->_applied = true;
		}

		// Build the CSV header to send, (the first record).
		$header = [];
		foreach($this->_columns as $c){
			/** @var Column $c */
			$header[] = $c->title;
		}

		// Send the headers and start the output.
		$view->render();
		$this->sendCSVRecord($header);
	}

	/**
	 * Send an indexed array to the browser as a valid CSV record.
	 *
	 * To be used in conjunction with sendCSVHeader().
	 *
	 * All scalar data in the array will be sanitized automatically.
	 *
	 * @param array $data
	 */
	public function sendCSVRecord($data){
		// CSV data is.... difficult.
		foreach($data as $k => $v){
			$v = trim($v);

			$escape = false;
			if($v == ''){
				$escape = false;
			}
			elseif(strpos($v, '"') !== false){
				// Strings with a quote in them get the quote escaped out and then wrapped in quotes.
				$escape = true;
				$v = str_replace('"', '\\"', $v);
			}
			elseif(strpos($v, ' ') !== false){
				// Strings with a space in the content get wrapped in quotes.
				$escape = true;
			}
			elseif(strpos($v, ',') !== false){
				// Strings with a comma in the content get wrapped in quotes.
				$escape = true;
			}
			elseif(strpos($v, "\n") !== false){
				// Strings with a newline in the content get wrapped in quotes.
				$escape = true;
			}
			elseif(strpos($v, "\r") !== false){
				// Strings with a newline in the content get wrapped in quotes.
				$escape = true;
			}

			if($escape){
				$data[$k] = '"' . $v . '"';
			}
		}

		// Now...
		echo implode(',', $data) . "\r\n";
	}


	//-----------------------------------------------------------------------\\
	//                            ITERATOR ACCESS
	//-----------------------------------------------------------------------\\

	public function rewind() {
		reset($this->_results);
	}

	public function current() {
		return current($this->_results);
	}

	public function key() {
		return key($this->_results);
	}

	public function next() {
		next($this->_results);
	}

	public function valid() {
		return isset($this->_results[key($this->_results)]);
	}

	//-----------------------------------------------------------------------\\
	//                              PRIVATE
	//-----------------------------------------------------------------------\\


	/**
	 * Render this table's head content, (everything above the records).
	 *
	 * @return string Full HTML Markup.
	 */
	private function _renderHead(){

		$template = Template::Factory('includes/listingtable/head.tpl');

		$f = $this->getFilters();

		if(!$this->_hassort){
			// One final check for if these filters are sortable.
			$f->hassort = false;
		}

		$tableclasses = ['listing'];
		if($this->_hassort){
			$tableclasses[] = 'listing-table-sortable';
		}
		$atts = [];
		$atts['class'] = implode(' ', $tableclasses);
		$atts['data-table-name'] = $this->_name;
		$atts['data-table-sortable'] = ($this->_hassort ? 1 : 0);
		$tableAttributes = '';
		foreach($atts as $k => $v){
			$tableAttributes .= ' ' . $k . '="' . $v . '"';
		}

		if($this->_editform !== null){
			$this->addControl(
				[
					'link' => '#',
					'class' => 'control-edit-toggle',
					'icon' => 'pencil-square-o',
					'title' => 'Quick Edit',
				]
			);
		}

		$template->assign('filters', $f);
		$template->assign('filters_rendered', $this->_renderFilters());
		$template->assign('pagination_rendered', $this->_renderPagination());
		$template->assign('table_attributes', $tableAttributes);
		$template->assign('edit_form', $this->_editform);
		$template->assign('columns', $this->_columns);
		$template->assign('controls', $this->getControls());


		return $template->fetch();
	}

	/**
	 * Render the filters
	 *
	 * @return string Full HTML Markup.
	 */
	private function _renderFilters(){
		$out = '';

		$f = $this->getFilters();

		if(!$this->_hassort){
			// One final check for if these filters are sortable.
			$f->hassort = false;
		}

		if(!$f->hasFilters()){
			return '';
		}

		$out .= '<div class="screen">' . $f->render() . '</div>';
		$out .= '<div class="print">' . $f->renderReadonly() . '</div>';

		return $out;
	}

	/**
	 * Render the pagination options.
	 *
	 * @return string
	 */
	private function _renderPagination(){
		return $this->getFilters()->pagination();
	}

	/**
	 * Render this table's foot content, (everything below the records).
	 * @return string Full HTML Markup
	 */
	private function _renderFoot(){
		$out = '';

		if($this->_editform !== null){
			$out .= '<tr class="edit edit-record-buttons"><td colspan="' . (sizeof($this->_columns) + 1) . '">' .
				'<a href="#" class="control-edit-toggle button">Cancel</a>' .
				'<input type="submit" value="Save Quick Edit"/>' .
				'</td></tr>';
		}

		$out .= '</table>';

		if($this->_editform !== null){
			$out .= $this->_editform->render('foot');
		}

		$f = $this->getFilters();
		$out .= $f->pagination();

		if($f->getTotalCount() == 0 && !$f->hasSet()){
			return '';
		}

		// Don't forget the necessary scripts!
		\Core\view()->addScript('assets/js/core.listingtable.js', 'foot');
		\Core\view()->addScript("<script>new Core.ListingTable(\$('table[data-table-name=\"" . $this->_name . "\"]'), '" . $this->getFilters()->getSortKey() . "', '" . $this->getFilters()->getSortDirection() . "');</script>", 'foot');

		return $out;
	}
} 