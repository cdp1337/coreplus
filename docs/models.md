# Model Declarations

The Model in Core is the foundation of the "M" part of "MVC".
It provides a control point for all metadata, structures, indexes, and datalogic associated with a given database table.

## Naming Conventions

### Table Name / Class Name

Each table in the datastore *must* have exactly *one* associated Model with a corresponding name.
If you need your table called "`my_something`", then its Model is "`MySomethingModel`".

If you have an object that will use a prefix such as "CRM", please name the class "CrmFooModel" instead of "CRMFooModel".  This ensures that your table name does not end up being "c_r_m_foo".

    correct:   CrmFooModel => "crm_foo"
    incorrect: CRMFooModel => "c_r_m_foo"

### Date/DateTime/Timestamp Columns

#### UTC/GMT Timestamps

In Core, storing a date is recommended to be done as unix timestamp in GMT.  This means that your dates will be of type Model::ATT_TYPE_INT.  The use of GMT across the board is to alleviate any issues of cross-timezone viewing and what not.

#### YYYY-MM-DD / YYYY-MM-DD HH:ii:ss

Core does also support human-readable date strings, although their use is discouraged against unless you have a good reason to, such as `I don't care when precisely the event actually happens, I just want to display to the user that on this day, something is scheduled`.  A real-world example of this would be a due date for a billing system.  You don't need the precision to bill at exactly the precise second, just sometime on that day in your local timezone.

When using these types of data fields, please use `Model::ATT_TYPE_DATE` and `Model::ATT_TYPE_DATETIME` as necessary.

#### Column Prefixes

When creating a column of type date/datetime/timestamp, please do one of two options.

1. Ignore prefix altogether.
2. Use the appropriate date-type prefix.

Option 1 is best seen with automatic fields, such as created and updated timestamps.

Option 2 is useful (for example):

    Application is a billing system / shopping cart.
    There are date billed, due date, and date updated.
    
    date billed is the exact moment the order was placed.
    due date is a day on a given month after which the order payment is considered late.
    date updated is the exact moment the order was last updated.
    
    public static $Schema = [
        ...
        'timestamp_billed' => [
            // Timestamps are stored in INT format.
            'type' => Model::ATT_TYPE_INT,
        ],
        'timestamp_updated' => [
            // UPDATED is a magic type that sets automatically on save.
            'type' => Model::ATT_TYPE_UPDATED,
        ],
        'date_due' => [
            // Generic dates are stored in "YYYY-MM-DD" format.
            'type' => Model::ATT_TYPE_DATE,
        ],
        'datetime_blah' => [
            // Another column just to have it for reference,
            // Datetimes are stored in "YYYY-MM-DD HH:ii:ss" format.
            'type' => Model::ATT_TYPE_DATETIME,
        ],
        ...
    ];

## Business Logic vs Data Logic

["Business logic"](https://en.wikipedia.org/wiki/Business_logic), is the part of the program that encodes the real-world business rules that determine how data can be created, displayed, stored, and changed.
**THIS HAS NO PLACE IN MODELS!**
All business logic *MUST* reside inside Controllers, as business logic generally applies to user roles, so user A may have different permissions than user B,
such as being able to create a user without a password or assign admin rights to a user.

These are all business logic examples that *must not* be in Models.

Data logic on the other hand, *(@todo find correct term for this)*, 
such as no two users on the system can have the same email address otherwise there would be data corruption,
**DOES** belong in Models, as the Model is one of the lowest tiers that interact with the raw data.

## Static Public Properties

Each model must have a small handful of static properties to define its structure.

* `$Schema`
	* The schema of the model, (see below).
* `$Indexes`
	* The indexes of the model, (see below).
* `$HasSearch`
	* bool
	* Defaults to false
	* Set to true if this model should have the additional search index fields and a Search method.
	* (Will auto-create the necessary columns for this option).
* `$HasCreated`
    * bool
    * Defaults to false
    * Set to true if this model has a created timestamp.
	* (Will auto-create the necessary columns for this option).
* `$HasUpdated`
    * bool
    * Defaults to false
    * Set to true if this model has an updated timestamp.
	* (Will auto-create the necessary columns for this option).
* `$HasDeleted`
    * bool
    * Defaults to false
    * Set to true if this model has a deleted timestamp.
	* (Will auto-create the necessary columns for this option).


## Model Schemas

In order to make models as fast as possible, the schema of a given model should be readable without instantiating a new object.  In PHP, the main way to do this is with use of static properties.  As consequence, that's how the schema in models is setup.

This is an example of a very simple schema containing two columns or keys.

    class MySomethingModel extends Model {

        public static $Schema = array(
            'key1' => [
                'type' => Model::ATT_TYPE_ID
            ],
            'key2' => [
                'type' => Model::ATT_TYPE_STRING
            ],
        );

    }


### Model Schema Attribute Overview

Each key supports several attributes, one ("type"), required, the rest optional.

* type
	* Specifies the data type contained in this column.  Must be one of the Model::ATT_TYPE_* fields.
	* Type: string

* required
	* Set to true to disallow blank values
	* Type: boolean
	* Default: false

* maxlength
	* Maximum length in characters (or bytes), of data stored.
	* This is automatic for many of the types, as appropriate.  For example, ATT_TYPE_STRING defaults to 255, ATT_TYPE_INT defaults to 15, ATT_TYPE_DATA defaults to something really big such as 24 Mb or some such.
	* Type: int

* validation
	* Validation options for this column.
	* The validation logic for data in the column, can be a regex (indicated by "/ ... /" or "# ... #"), a public static method ("SomeClass::ValidateSomething"), or an internal method ("this::validateField").
	* Type: string

* validationmessage
	* The message to send to the user if validation of this property fails.
	* Type: string

* options
    * ATT_TYPE_ENUM column types expect a set of values.  This is defined here as an array.
    * Type: array

* default
    * Default value to use for this column
    * Type: string|int|float|boolean

* null
    * Allow null values for this column.  If set to true, null is preserved as null.  False will change null values to blank.
    * Type: boolean
    * Default: false

* formtype
    * Shortcut for specifying the form type when rendering as a form.  Should be a valid form type
    * Type: string
    * Default: "text"

* form
    * Full version of specifying form parameters for this column.  See below for full description.
    * Type: array

* comment
    * Comment to add onto the database column.  Useful for administrative comments for.
    * Type: string

* precision
    * ATT_TYPE_FLOAT supports precision for its data.  Should be set as a string such as "6,2" for 6 digits left of decimal, 2 digits right of decimal.
    * Type: string

* encrypted
    * Core+ allows data to be encrypted / decrypted on-the-fly.  This is useful for sensitive information such as credit card data or authorization credentials for external sources.  Setting this to true will store all information as encrypted, and allow it to be read decrypted.
    * *WARNING*, since encrypted data cannot be utilized at the datastore level, no indexed column can be encrypted
    * Type: boolean
    * Default: false
* alias
	* When 'type' => Model::ATT_TYPE_ALIAS, this column definition acts as an alias of another column when performing 
	get lookups via get().
	* Mainly useful when renaming a column to a new name while still ensuring non-updated components compatibility.

* formatter
    * Provide a formatter method to format HTML output for this column value.
    * Useful built-in methods:
    * \Core\Formatter\CurrencyFormatter::BTC - Format as BTC
    * \Core\Formatter\CurrencyFormatter::EUR - Format as EUR
    * \Core\Formatter\CurrencyFormatter::GBP - Format as GBP
    * \Core\Formatter\CurrencyFormatter::USD - Format as USD
    * \Core\Formatter\GeneralFormatter::BoolEnabledDisabled - Format as "enabled" or "disabled"
    * \Core\Formatter\GeneralFormatter::BoolYesNo - Format as "yes" or "no"
    * \Core\Formatter\GeneralFormatter::DateStringFD - Format a date as a full long date
    * \Core\Formatter\GeneralFormatter::DateStringFDT - Format a date as a full long date+time
    * \Core\Formatter\GeneralFormatter::DateStringSD - Format a date as short date
    * \Core\Formatter\GeneralFormatter::DateStringSDT - Format a date as short date+time
    * \Core\Formatter\GeneralFormatter::Filesize - Format as filesize with automatic suffixes
    * \Core\Formatter\GeneralFormatter::IPAddress - Format as IP Address (with geo lookups when available)
    * \Core\Formatter\GeneralFormatter::TimeDuration - Format an amount of time into human-readable version.
    * \Core\Formatter\GeneralFormatter::TimeDurationSinceNow - Format an amount of time into human-readable version.
    * \Core\Formatter\GeneralFormatter::User - Format a user ID into the username.
    * \Core\Formatter\GeneralFormatter::UserAgent - Format a useragent into human-friendly formats.


## Models defining form elements

Many times, the data contained in a given model may be expected to be a particular format, 
ie: a string input type may actually be a file upload, or a text type may be HTML code.

To define what form elements are created for each property, 
the "formtype" attribute can be used inside of the Model::$Schema array.

If more advanced settings are required to be set, ie: setting basedir, descriptions, etc, 
use a "form" attribute which is an array containing all parameters necessary.

    'form' => [
        'type' => 'file',
        'basedir' => 'public/something',
    ],

This array can contain any standard or custom attribute for the assigned FormElement type.
Example: it wouldn't make any sense to have `type => text` and `cols => 4`, as FormElementText does not care about the "cols" attribute.

### Common form keys

#### type

Set the form element type as-per the defined form element map.  
Ex: 'text' is a text input, 'textarea' is a textarea, 'wysiwyg' yields an HTML editor, etc.

#### title

Set the title for this linked form element.

#### description

Set the description for this linked form element.

#### source

Set a method's return value to be used for the options value of this element.
This is only useful for select form element types.

The return of the method MUST be an array and it is passed into the option set as-is.

##### Examples

`'source' => 'MyFooModel::GetBlahOptions'`,

Set the options as the return value from the 'GetBlahOptions' static method on the 'MyFooModel' class.

`'source' => 'this::getBlahOptions'`,

Set the options as the return value from the 'getBlahOptions' instantiated method of the original source instance.

## Linked Models

Even though the datastore system is built as a non-relational system, relationships can still be utilized for convenience in the code.  To do this, "Linked" models can be created.  To do so, set the "$this->_linked" property in one model or the other.

For example in a gallery system,

GalleryAlbumModel may have a page for it, and numerous images under it, making the constructor look like

    public function __construct($key = null) {
        $this->_linked = array(
            'Page' => [
                'link' => Model::LINK_HASONE,
                'on' => 'baseurl', // Where both models share the same key "baseurl"
            ],
            'GalleryImage' => [
                'link' => Model::LINK_HASMANY,
                'on' => [ 'albumid' => 'id' ], // Where $that[key] => $this[key]
            ],
        );
        
        parent::__construct($key);
    }

Links can be defined from within the Schema if it exists on a single local property,
or from within the constructor if more complex.  Depending on where they are defined depends slightly on the keys.

### Attributes when defined in the `__constructor`

* class
	* Override the class name of the foreign record.

* link
	* Specify the link type of this relationship, from the standpoint of the current Model.
	* Alias of `type` for the `$Schema` version.
	* MUST be one of the `Model::LINK_*` constants.

* on
	* Specify the local/foreign keys that define the relationship.
	* If this is a single scalar value, then both Models MUST have the same key that relates them.
	* If this is an array, each pair is used in the relationship.  `['foreign_key_name' => 'local_key_name']`.

* order
	* Specify the default order clause for this link.

### Attributes when defined in the `$Schema`

* class
	* Override the class name of the foreign record.

* type
    * Specify the link type of this relationship, from the standpoint of the current Model.
    * Alias of `link` for the `__constructor` version.
    * MUST be one of the `Model::LINK_*` constants.

* on
	* Specify the local/foreign keys that define the relationship.
	* This value MUST be a single scalar value, which maps to the foreign key name.

* model
	* Specify the foreign model name, (without the "Model" suffix).
	* __REQUIRED__

* order
	* Specify the default order clause for this link.
	
## Model Upgrades and Installations

Generally speaking, database upgrades are something of the past when it comes to Core, as the full database schema is created on-the-fly based on the model schemas currently present.  This means that table creation and updating happens automatically when an upgrade or reinstallation is performed!

Q: I added a new Model to my application and need the table to be created too, do I need to export the CREATE TABLE sql and place that into an upgrade file?

A: Nope, just run the `utilities/packager.php` (available with the developer version of Core), to ensure that the Model is registered with your component and browse to `/admin/reinstalall` or execute `utilities/reinstall.php`.  The table is created automatically as per your Schema definition.

Q: I added a bunch of columns to my table/Model, what do now?

A: `/admin/reinstalall` or `utilities/reinstall.php`.  That's it; Core will update the table as necessary!

Q: I no longer need this column, what happens to it?

A: In Core, columns that exist in the database but do not exist in the defined Model are simply shuffled to the end of the table; they are never deleted.  This means that if you accidently remove a column from your Model, but realize you needed it afterall, just put it back in and Core will move the column, (and all of its data), back into the correct spot.

Q: I renamed a column from `foo` to `baz`, and I need the data preserved too!

A: OK, this is where things get tricky.  Simply by renaming the column in Core, the old column is shuffled to the end and a new column is created in its place, (with none of its data!).  This is probably not quite what you want.  To get around this, create a rename upgrade task for that next version of your component.  This will execute a rename command instead of shuffle/create like the default installer will.

    <upgrade from="next" to="next">
		<dataset action="alter" table="shopping_cart">
			<datasetrenamecolumn oldname="dateordered" newname="timestamp_ordered"/>
		</dataset>
	</upgrade>

Q: If all this database schema manipulation happens on-the-fly, what happens when something blows up mid-transaction?!?

A: WELL... This has long been a concern of any dynamically-generated source code and table structure.  To minimize any issues from corrupt code / structure, the entire table structure AND data is copied to a temporary table in the database, then the manipulation operations are applied THERE.  If something blows up, the temporary table is corrupt and simply disregarded, and you as a developer receive a message explaining what happened.  On a successful transaction, the temporary table is copied, (new structure and all), back ontop of the original table.

Q: wait wait wait, copying every single record of a table may consume a HUGE amount of resources when performing installations and upgrades.

A: Yes.

## Supplemental Models

Sometimes it can be extremely useful to be able to extend a Model in another component.
This can be done to provide new control links for another model, add custom scripts to the pre or post save hooks,
or even add new columns to the original table.

To make use of this supplemental system, create a new class in your extending component
that matches the rules of `YourComponent_OrignalNameModelSupplemental`

Where:

    Some unique name space, usually your component name
    exactly one "_"
    the original model base name
    "ModelSupplemental"

For example if you have a component named Baz and you wanted to extend the User model,
your supplemental name should be `Baz_UserModelSupplemental`.

### Supplemental Properties

`public static $Schema` can be used to add new columns onto the original model.

`public static $Index` can be used to add new indexes to the original model.

### Supplemental Methods

`public static PreSaveHook($model)` Called prior to save completion.

`public static PostSaveHook($model)` Called after the model has been saved to the database.

`public static PreDeleteHook($model)` Called before the model is deleted from the database.

`public static GetControlLinks($model)` Called during getControlLinks to return additional links in the controls.

### ModelSupplemental Interface

For convenience, the interface `ModelSupplemental` has been created that contains
all these methods.  Feel free to implement it in the extending ModelSupplemental class
to pull in these methods.

Please note, you will still need to define
`$Schema` and `$Indexes` as necessary.