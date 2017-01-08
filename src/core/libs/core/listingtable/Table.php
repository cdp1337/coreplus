<?php
/**
 * File for class Table definition in the coreplus project
 *
 * @package Core\ListingTable
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140406.2004
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
	
	/** @var bool T/F if the record controls should be hover reactive */
	public $recordControlsHoverSensitive = true;
	
	/** @var bool|"auto" Set to T/F to force a UI proxy either force on or force off. */
	public $recordControlsForceProxy = 'auto';
	
	/** @var string Text of the proxy source text, no function if forceProxy is set to false */
	public $recordControlsProxyText = 't:STRING_CONTROLS';
	
	/** @var bool|string Set to T/F to force rendering a traditional &lt;table&gt;. */
	public $renderTraditionalTable = 'auto';
	
	/** @var bool Set to true to trigger "test" mode for this rendering.  */
	public $testmode = false;
	
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
	
	/** @var bool Set automatically when a column is added with a renderable flag */
	private $_hasrender = false;

	/** @var array|null Array of the results, pulled directly from the ModelFactory's get method. */
	private $_results;

	/** @var null|\ViewControls Any controls added to this table as a whole. */
	private $_controls = null;
	
	/** @var null|Model blank model required for polling a resolvable object for metadata. */
	private $_blankModel = null;

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
			$this->_controls->setProxyText('t:STRING_TABLE_ACTIONS');
			$this->_controls->setProxyForce(true);
			
			// Include a show/hide columns link.
			// <a href="#" class="control-column-selection" title="Show / Hide Columns"><i class="icon icon-columns"></i></a>
			$this->_controls->addLink([
				'title' => 't:STRING_SHOW_HIDE_COLUMNS',
				'icon' => 'columns',
				'class' => 'control-column-selection',
				'href' => '#',
			]);
		}

		return $this->_controls;
	}
	
	/**
	 * Get the appropriate render method for this table.
	 * 
	 * If set to auto, (default), the rendering method is guessed from the environment and UA.
	 * 
	 * @return string "div" or "table"
	 */
	public function getRenderMethod(){
		if($this->renderTraditionalTable === true){
			return 'table';
		}
		elseif($this->renderTraditionalTable === false){
			return 'div';
		}
		else{
			// AUTO!
			
			$ua = \Core\UserAgent::Construct();
			if(!$this->_hasrender){
				// If this table is not configured with renderable columns, then render a tradtional table.
				return 'table';
			}
			
			if($ua->browser == 'Internet Explorer'){
				// MSIE probably doesn't handle flex very well.
				return 'table';
			}
			
			// Otherwise?
			return 'div';
		}
	}
	
	public function getModelName(){
		if(($fac = $this->getModelFactory())){
			// Usual method.
			return $fac->getModelName();
		}
		
		// If the records are manually assigned, then it may not be so easy.
		// Instead, grab the first record and return its name.
		if(sizeof($this->_results)){
			return get_class($this->_results[0]);
		}
		
		// ummm
		return '';
	}
	
	public function getBlankModel(){
		if($this->_blankModel === null){
			$n = $this->getModelFactory()->getModelName();
			$ref = new \ReflectionClass($n);
			$this->_blankModel = $ref->newInstance();
		}
		
		return $this->_blankModel;
	}



	//-----------------------------------------------------------------------\\
	//                                  SETTERS
	//-----------------------------------------------------------------------\\
	
	/**
	 * Manually set the records of this listing table.
	 * By doing this, you lose ALL sorting possibilities and any dynamic nicety that the default behaviour gives.
	 * 
	 * Each record is still expected to support get as the traditional Model system does.
	 * 
	 * @param array $arrayOfRecords
	 */
	public function setRecords($arrayOfRecords){
		$this->_results = $arrayOfRecords;
		$this->_applied = true;
		$this->_hassort = false;
	}

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
	 * ## Usage:
	 * 
	 * ### Associative Array Example
	 * 
	 * This method can be used two ways, the first and recommended way is via an associative array.
	 * 
	 * ```
	 * $table = new \Core\ListingTable\Table();
	 * $table->addColumn(
	 *     [
	 *         'title' => 't:STRING_THING',
	 *         'sortkey' => 'thing',
	 *         'renderkey' => 'thing',
	 *         'visible' => true,
	 *     ]
	 * );
	 * ```
	 * 
	 * #### Associative Options
	 * 
	 * * title: (required); The title or t: string key to display
	 * * sortkey: If this column is sortable, this is the value of the ORDER BY clause
	 * * renderkey: If this Model supports automatic rendering, this is the value of the render() method
	 * * visible: Set to false to initially hide this column.  True (default), displays it.
	 * * FUTURE icon
	 * * FUTURE ??? (@todo)
	 * 
	 * ### Legacy Example
	 * 
	 * The second option is the legacy approach, which is still supported.
	 * 
	 * ```
	 * $table = new \Core\ListingTable\Table();
	 * $table->addColumn('t:STRING_THING', 'thing', true);
	 * ```
	 * 
	 * ### Simple Legacy Example
	 * 
	 * The shortest possible option for this command is simply pass one value as the title to display.
	 * By using this method, you will be required to render all the contents of the table.
	 * 
	 * ```
	 * $table = new \Core\ListingTable\Table();
	 * $table->addColumn('t:STRING_THING');
	 * ```
	 *
	 * @param array|string $dat_or_title Title or associative array containing: [title|key|renderkey|sortkey|visible]
	 * @param string|null  $sortkey      Sortkey when used in legacy mode
	 * @param boolean      $visible      T/F if this col is visible by default
	 * 
	 * @throws \Exception
	 */
	public function addColumn($dat_or_title, $sortkey = null, $visible = true){
		
		if(is_array($dat_or_title)){
			// New method supports an array as the first parameter!
			
			if(!(
				isset($dat_or_title['renderkey']) || isset($dat_or_title['key']) || isset($dat_or_title['title'])
			)){
				// At least a key or title MUST be set, (optionally both)!
				// If both keys are missing, this script will not have enough information to render the column with!
				throw new \Exception('Please ensure that all listing table columns have either a title or key attribute!');
			}
			
			if(isset($dat_or_title['key'])){
				// Allow the `key` attribute to be set to set both render and sort keys.
				$dat_or_title['renderkey'] = $dat_or_title['key'];
				$dat_or_title['sortkey'] = $dat_or_title['key'];
			}
			
			// The title can be derived from the render key and the model if left empty.
			// This will pull from the native Model's i18n support structure!
			// Allow the user to override this by setting the `title` attribute though!
			$title = isset($dat_or_title['title']) ?
				$dat_or_title['title'] :
				't:STRING_MODEL_' . strtoupper($this->getModelName()) . '_' . strtoupper($dat_or_title['renderkey']);
			
			$sortkey   = isset($dat_or_title['sortkey']) ? $dat_or_title['sortkey'] : null;
			$renderkey = isset($dat_or_title['renderkey']) ? $dat_or_title['renderkey'] : null;
			$visible   = isset($dat_or_title['visible']) ? $dat_or_title['visible'] : true;
			$abbr      = isset($dat_or_title['abbr']) ? $dat_or_title['abbr'] : null;
			$group     = isset($dat_or_title['group']) ? $dat_or_title['group'] : null;
			
			// The name can come from a variety of locations, as it's generally calculated.
			// However, allow the user to override this calculation if a `name` attribute is set manually!
			if(isset($dat_or_title['name'])){
				$name = $dat_or_title['name'];
			}
			elseif($renderkey){
				$name = $renderkey;
			}
			elseif($sortkey){
				$name = $sortkey;
			}
			else{
				$name = \Core\str_to_url($title);
			}
		}
		else{
			$title     = $dat_or_title;
			$renderkey = null;
			$abbr      = null;
			$group     = null;
			$name      = \Core\str_to_url($dat_or_title);
		}
		
		$c = new Column();
		$c->title     = $title;
		$c->sortkey   = $sortkey;
		$c->visible   = $visible;
		$c->renderkey = $renderkey;
		$c->abbr      = $abbr;
		$c->group     = $group;
		$c->name      = $name;
		$this->_columns[] = $c;

		if($sortkey){
			$f = $this->getFilters();
			$f->addSortKey($sortkey);
			$this->_hassort = true;
		}
		
		if($renderkey){
			$this->_hasrender = true;
		}
	}

	/**
	 * Set the model name, (and the underlying Factory object).
	 * 
	 * Please ensure to use the fully resolved class name when setting the class.
	 * 
	 * @param string $name
	 * 
	 * @throws \Exception
	 */
	public function setModelName($name){
		if(!preg_match('/Model$/', $name)){
			throw new \Exception('Please set the Model name the fully resolved model class');
		}
		if(!class_exists($name)){
			throw new \Exception('Class ' . $name . ' does not appear to exist!');
		}
		
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
			$control->link = \Core\resolve_link($link);
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

	public function render($section = null){
		if(!$this->_applied){
			$this->getFilters()->applyToFactory($this->getModelFactory());
			$this->_results = $this->getModelFactory()->get();
			$this->_applied = true;
		}

		switch($section){
			case null:
				return $this->_renderHead() . $this->_renderBody() . $this->_renderFoot();
			case 'head':
				return $this->_renderHead();
			case 'foot':
				return $this->_renderFoot();
			case 'filters':
				return $this->_renderFilters();
			case 'pagination':
				return $this->_renderPagination();
			case 'body':
				return $this->_renderBody();
			case 'csv':
				return $this->_renderCSV();
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
		$filename = \Core\str_to_url($title) . '-' . date('Y-m-d@Hi') . '.csv';

		$view->mode = \View::MODE_NOOUTPUT;
		
		if($this->testmode && DEVELOPMENT_MODE){
			// Allow rendering as a test output for easy debugging for the developer.
			// For security reasons, this is only allowed when in DEVELOPMENT_MODE!
			$view->contenttype = 'text/plain';
		}
		else{
			$view->contenttype = 'text/csv';
			$view->addHeader('Content-Disposition', 'attachment; filename=' . $filename);
		}

		// Set the limits and everything as necessary.
		$this->setLimit(99999);
		$filters = $this->getFilters();
		if(!$this->_applied){
			$filters->applyToFactory($this->getModelFactory());
			$this->_results = $this->getModelFactory()->get();
			$this->_applied = true;
		}
		
		// Build the CSV header to send, (the first record).
		$header = [];
		foreach($this->_columns as $c){
			/** @var Column $c */
			$header[] = $c->getTitle();
		}
		
		// Render comment records to assist the reader of the file.
		// This is not supported by many systems, but should still render as a record for their viewing pleasure.
		$comments = [];
		
		$comments[] = ['#COMMENT', 'Generated', \Core\Date\DateTime::Now('FDT') . ' by ' . \Core\user()->get('email')];
		foreach($filters->getElements() as $el){
			if($el->get('value')){
				$comments[] = ['#COMMENT', 'Filter ' . $el->get('title'), $el->get('value')];
			}
		}

		// Send the headers and start the output.
		$view->render();
		
		foreach($comments as $c){
			$this->sendCSVRecord($c);
		}
		
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
		// What type of renderer should be used?
		$method = $this->getRenderMethod();

		if($method == 'div'){
			// Render-compatible sources use the new div-based layout.
			$template = Template::Factory('includes/listingtable/div-head.tpl');
		}
		else{
			$template = Template::Factory('includes/listingtable/head.tpl');
		}
		
		$f = $this->getFilters();

		if(!$this->_hassort){
			// One final check for if these filters are sortable.
			$f->hassort = false;
		}
		
		// Set to true if there's no model factory, (ie: records were externally set/managed).
		$manualRecords = (!$this->getModelFactory());

		$tableclasses = ['listing', 'listing-table'];
		if($this->_hassort){
			$tableclasses[] = 'listing-table-sortable';
		}
		$atts = [];
		$atts['class'] = implode(' ', $tableclasses);
		$atts['data-table-name'] = $this->_name;
		$atts['data-table-sortable'] = ($this->_hassort ? 1 : 0);
		$attributes = '';
		foreach($atts as $k => $v){
			$attributes .= ' ' . $k . '="' . $v . '"';
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

		$template->assign('manual_records', $manualRecords);
		$template->assign('filters', $f);
		$template->assign('filters_rendered', $manualRecords ? '' : $this->_renderFilters());
		$template->assign('pagination_rendered', $manualRecords ? '' : $this->_renderPagination());
		$template->assign('attributes', $attributes);
		$template->assign('edit_form', $this->_editform);
		$template->assign('columns', $this->_columns);
		$template->assign('controls', $this->getControls());

		$html = $template->fetch();
		
		if($method == 'div'){
			// Dynamically render each column header.
			// This needs to be dynamic because 
			
			$lastGroup = null;
			
			$html .= '<div class="listing-table-record listing-table-header">';
			
			foreach($this->_columns as $c){
				/** @var Column $c */
				// Close the previous group if this is a new one.
				// Also start the new group while we're here.
				if($c->group !== $lastGroup){
					if($lastGroup !== null){
						// Only close the previous group if there was a previous group.
						//$html .= '</div>';
					}
					
					if($c->group !== null){
						//$html .= '<div class="listing-table-group column-group-' . $c->group . '">';
					}
					
					$lastGroup = $c->group;
				}
				
				// Render the column header itself.
				$html .= $c->getDIV();
			}
			
			// If there is a group open, close that group!
			if($lastGroup !== null){
				//$html .= '</div>';
			}
			
			// Render the control links column that should be present in all tables.
			$html .= '<div class="listing-table-cell-header listing-table-cell column-controls">' .
				$this->getControls()->fetch() .
				'</div>';
			
			// Close the table-record group
			$html .= '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Render a CSV in its entirety and send to the browser as a file stream.
	 */
	private function _renderCSV(){
		// Use the default View as the view to render to.
		$view = \Core\view();
		
		// Send the CSV header to the browser
		$this->sendCSVHeader($view, $this->_name);
		
		// And send each record to the browser
		foreach($this as $rec){
			$data = [];
			foreach($this->_columns as $c){
				/** @var Column $c */
				$data[] = $rec->get($c->renderkey, \View::CTYPE_PLAIN);
			}
			
			$this->sendCSVRecord($data);
		}
	}
	
	private function _renderBody(){
		// Each cell is primarily controlled by the contained Model for each record.
		// As such, the use of a template is not as important here.
		
		// What type of renderer should be used?
		$method = $this->getRenderMethod();
		
		$out = '';
		
		if($method == 'div'){
			foreach($this as $rec){
				
				$out .= '<div class="listing-table-record listing-table-body ' . $rec->getClass() . '"';
				// Append any/all data tags.
				$dataTags = $rec->getDataTags();
				foreach($dataTags as $tag => $value){
					$out .= 'data-' . $tag . '="' . str_replace('"', '&quot;', $value) . '"';
				}
				$out .= '>';

				// Render each column herein
				foreach($this->_columns as $c){
					/** @var Column $c */

					// Render the column data itself.
					$out .= '<div class="listing-table-cell-ungrouped ' . $c->getClass() . '" data-title="' . $c->getTitle(true) . '">';
					$out .= $rec->get($c->renderkey, \View::CTYPE_HTML);
					$out .= '</div>';
				}
				
				$lastGroup = null;

				// Render each column for use in grouping; this will skip optional columns.
				foreach($this->_columns as $c){
					/** @var Column $c */
					if(!$c->visible){
						continue;
					}
					// Close the previous group if this is a new one.
					// Also start the new group while we're here.
					if($c->group !== $lastGroup){
						if($lastGroup !== null){
							// Only close the previous group if there was a previous group.
							$out .= '</div>';
						}

						if($c->group !== null){
							$out .= '<div class="listing-table-group column-group-' . $c->group . '">';
						}

						$lastGroup = $c->group;
					}

					// Render the column data itself.
					$out .= '<div class="listing-table-cell-grouped ' . $c->getClass() . '" data-title="' . $c->getTitle(true) . '">';
					$out .= $rec->get($c->renderkey, \View::CTYPE_HTML);
					$out .= '</div>';
				}

				// If there is a group open, close that group!
				if($lastGroup !== null){
					$out .= '</div>';
				}

				// Render the controls for this line item.
				$controls = \ViewControls::DispatchModel($rec);
				$controls->hovercontext = $this->recordControlsHoverSensitive;
				$controls->setProxyForce($this->recordControlsForceProxy);
				$controls->setProxyText($this->recordControlsProxyText);

				$out .= '<div class="listing-table-cell column-controls">' . $controls->fetch() . '</div>';

				$out .= '</div>';
			}
		}
		else{
			foreach($this as $rec){
				$out .= '<tr>';

				// Render each column herein
				foreach($this->_columns as $c){
					/** @var Column $c */
					
					// Render the column data itself.
					$out .= '<td class="' . $c->getClass() . '">';
					$out .= $rec->render($c->renderkey);
					$out .= '</td>';
				}

				// Render the controls for this line item.
				$controls = \ViewControls::DispatchModel($rec);
				$controls->hovercontext = $this->recordControlsHoverSensitive;
				$controls->setProxyForce($this->recordControlsForceProxy);
				$controls->setProxyText($this->recordControlsProxyText);

				$out .= '<td class="column-controls">' . $controls->fetch() . '</td>';

				$out .= '</tr>';
			}
		}
		
		return $out;
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
		// What type of renderer should be used?
		$method = $this->getRenderMethod();
		
		$out = '';

		if($this->_editform !== null){
			$out .= '<tr class="edit edit-record-buttons"><td colspan="' . (sizeof($this->_columns) + 1) . '">' .
				'<a href="#" class="control-edit-toggle button">Cancel</a>' .
				'<input type="submit" value="Save Quick Edit"/>' .
				'</td></tr>';
		}

		if($method == 'div'){
			$out .= '</div>';
		}
		else{
			$out .= '</table>';
		}

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
		\Core\view()->addScript("<script>new Core.ListingTable(\$('" . $method . "[data-table-name=\"" . $this->_name . "\"]'), '" . $this->getFilters()->getSortKey() . "', '" . $this->getFilters()->getSortDirection() . "');</script>", 'foot');

		return $out;
	}
} 