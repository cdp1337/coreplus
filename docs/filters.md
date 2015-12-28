# Filters

Filters are an extension of the Form system and are based strictly on GET parameters, though session data is used to accompany it.
Since they are merely an extension of the form system, most of the standard form options will work with filters, including
`addElement()` and `render()`.


## Minimum Use Example

At its simpliest, a Filter simply requires some elements, (and even these could be considered optional, though the usefulness
of the system is in dire question at that stage).  In order to make the full use of it however, assigning a unique name
will allow the Filter to be saved automatically and looked up upon revisiting the same filter later.  This is highly advantageous
to users who set a filter on a given list, edit an entry in the list, and return to the listing, expecting the same view as
when he or she left.

### Controller

	$filters = new FilterForm();
	$filters->setName('my-awesome-name');
	$filters->addElement(
		'select',
		array(
			'title' => 'Thing',
			'name' => 'thing',
			'options' => array(
				'' => '-- All --',
				'hourly' => 'hourly',
				'daily' => 'daily',
				'weekly' => 'weekly',
				'monthly' => 'monthly'
			)
		)
	);
	$filters->load($request);

In this example, "my-awesome-name" filter is created, one element assigned to it, and the data loaded from the PageRequest
object $request.  This is required to be done manually because given it's simply a standard GET submission, the actual form data
is not saved in the session for a later lookup.  The parameter it will be looking for is "`filters[thing]`".

Logic, (say a Where statement), called later on in the controller can access the element value like this:

	if($filters->get('thing')) $myobjectfactory->where('thingone = ' . $filters->get('thing'));


## Full Use Example

In this example, all extensions of the Filters are utilized.

### Controller
	$filters = new FilterForm();
	// Set the name
	$filters->setName('my-awesome-name');
	// Enabling sorting
	$filters->hassort = true;
	// Enabling pagination
	$filters->haspagination = true;
	// Add one element called "Thing!" that's linked to the model property "thing"
	$filters->addElement(
		'select',
		array(
			'title' => 'Thing!',
			'name' => 'thing',
			'options' => array(
				'' => '-- All --',
				'hourly' => 'hourly',
				'daily' => 'daily',
				'weekly' => 'weekly',
				'monthly' => 'monthly'
			),
			// This will enable linking to the model
			'link' => FilterForm::LINK_TYPE_STANDARD,
		)
	);
	$filters->load($request);

	$factory = new ModelFactory('MyAwesomeModel');
	$filters->applyToFactory($factory);
	$listings = $factory->get();

### Template
	{* Display the filter options themselves *}
	{$filters->render()}
	{* Display the pagination options *}
	{$filters->pagination()}
	{* And the listing table. *}
	<table class="listing column-sortable">
		<tr>
			<th sortkey="thing1">Thing 1</th>
			<th sortkey="thing2">Thing 2</th>
			<th>I am not sortable</th>
		</tr>
		{* foreach ... *}
	</table>

## Sort Clauses

Another use of the Filters object is to record sort keys and direction.  This makes use of the "sortkey" and "sortdir" GET
values and a corresponding "column-sortable" classed table with `<th/>`'s that have a "sortkey" attribute.

There are a couple requirements and suggested calls for using sorting.

### Controller

	$filters->hassort = true;
	$filters->setSortkeys(array('thing1', 'thing2'));

### Template
	
	<table class="listing column-sortable">
		<tr>
			<th sortkey="thing1">Thing 1</th>
			<th sortkey="thing2">Thing 2</th>
			<th>I am not sortable</th>
		</tr>
	</table>

In this example, the hassort property is set to true.  This is required to notify the form system to make use of this extended functionality.
Also, the "setSortKeys" method is called with a list of valid "sortkey" values.  Any GET value not in this list will be ignored.
These have to be called before the `load()` method is called in order to make use of the session data.

## Pagination Clause

Another use of the Filters object is to provide pagination.  Upon setting pagination to true and calling pagination() in the template, the pages are displayed in the view.

### Controller
	
	$filters->haspagination = true;

### Template
	
	{$filters->pagination()}

## Automatic Linking

Model properties can be automatically linked to filter keys via the "link" attribute on its respective `FormElement` construction array.  The different types of linkeages supported are FilterForm::LINK_TYPE_STANDARD, LINK_TYPE_GE, LINK_TYPE_GT, LINK_TYPE_LE, LINK_TYPE_LT.

Upon calling `applyToFactory()`, the model factory's where clause is populated with the values appropriately.

## Comment Element Attributes (beyond standard attributes)

* link
	* Set to the link type for the desired type
	* String, one of the following:
	* FilterForm::LINK_TYPE_STANDARD
	* FilterForm::LINK_TYPE_GE
	* FilterForm::LINK_TYPE_GT
	* FilterForm::LINK_TYPE_LE
	* FilterForm::LINK_TYPE_LT
* linkname
	* Optional if the model name is different from the element name.
	* String|Array
	* If set to an array, a sub where is performed with an OR on all of the provided values.
	  Useful for "Omni" searches such as "User Name" which search first, last, and email.
	

## Common Public Properties
	
* $hassort
	* Set to true to make use of the extended sort functionality.
	* Boolean
* $haspagination
	* Set to true to make use of the extended pagination functionality.
	* Boolean

## Common Public Methods

* addElement
	* Add an element to this filter set.
	* This is the exact same as the native Form system.
	* @param string|FormElement $element 
		* Type of element, (or the form element itself)
	* @param null|array         $atts [optional]
		* An associative array of parameters for this form element
* applyToFactory
	* Given all the user defined filter, sort, and what not, apply those values to the ModelFactory if possible.
	 * @param ModelFactory $factory
* get
	* Get one element's value based on its name
	* @param string $name 
		* The name of the element to retrieve
	* @return mixed|null 
		* Value or null if not set.
* getOrder
	* Get the sort keys as an order clause, passable into the dataset system.
* load
	* Load the values from either the page request or the session data.
	* @param PageRequest $request
* render
	* Fetch this filter set as a string
	* @return string
* setName
	* Set the name for this filter, required for any session saving/lookup.
	* @param string $filtername
* setSortKeys
	* Set the valid sort keys for this filterset.
	* @param array $arr
	* @return bool true/false on success or failure.

## Less Common Public Methods

	/**
	 * Load the values from the session data.
	 *
	 * This is automatically called by the load function.
	 */
	public function loadSession();

	/**
	 * Set the sort direction for this filterset.
	 *
	 * @param $dir "up" or "down".  It must be up or down because fontawesome has those keys instead of "asc" and "desc" :p
	 *
	 * @return bool true/false on success or failure.
	 */
	public function setSortDirection($dir);

	/**
	 * Get the sort direction, either up or down.
	 *
	 * @return string|null
	 */
	public function getSortDirection();

	/**
	 * Get the sort key.
	 *
	 * @return null|string
	 */
	public function getSortKey();

	/**
	 * Set the active sort key currently.
	 * If sotkeys is populated, it MUST be one of the keys in that array!
	 *
	 * @param string $key
	 *
	 * @return bool true/false on success or failure.
	 */
	public function setSortKey($key);