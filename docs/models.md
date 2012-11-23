## Model Schemas

In order to make models as fast as possible, the schema of a given model should be readable without instantiating a new object.  In PHP, the main way to do this is with use of static properties.  As consequence, that's how the schema in models is setup.

This is an example of a very simple schema containing two columns or keys.

    class MySomethingModel extends Model {

        public static $Schema = array(
            'key1' => array(
                'type' => Model::ATT_TYPE_ID
            ),
            'key2' => array(
                'type' => Model::ATT_TYPE_STRING
            ),
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


## Models defining form elements

Many times, the data contained in a given model may be expected to be a particular format, ie: a string input type may actually be a file upload, or a text type may be HTML code.

To define what form elements are created for each property, the "formtype" attribute can be used inside of the Model::$Schema array.

If more advanced settings are required to be set, ie: setting basedir, descriptions, etc, use a "form" attribute which is an array containing all parameters necessary.

    'form' => array(
        'type' => 'file',
        'baseidr' => 'public/something',
    ),


## Linked Models

Even though the datastore system is built as a non-relational system, relationships can still be utilized for convenience in the code.  To do this, "Linked" models can be created.  To do so, set the "$this->_linked" property in one model or the other.

For example in a gallery system,

GalleryAlbumModel may have a page for it, and numerous images under it, making the constructor look like

    public function __construct($key = null) {
        $this->_linked = array(
            'Page' => array(
                'link' => Model::LINK_HASONE,
                'on' => 'baseurl',
            ),
                'GalleryImage' => array(
                'link' => Model::LINK_HASMANY,
                'on' => array('id' => 'albumid'),
            ),
        );
        
        parent::__construct($key);
    }
