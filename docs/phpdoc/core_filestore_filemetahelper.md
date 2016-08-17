Core\Filestore\FileMetaHelper
===============

A short teaser of what FileMetaHelper does.

More lengthy description of what FileMetaHelper does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: FileMetaHelper
* Namespace: Core\Filestore
* This class implements: ArrayAccess




Properties
----------


### $_file

    protected \Core\Filestore\File $_file





* Visibility: **protected**


### $_filename

    protected string $_filename





* Visibility: **protected**


### $_metas

    protected array $_metas





* Visibility: **protected**


Methods
-------


### __construct

    mixed Core\Filestore\FileMetaHelper::__construct($file)





* Visibility: **public**


#### Arguments
* $file **mixed**



### setMeta

    mixed Core\Filestore\FileMetaHelper::setMeta($key, $value)





* Visibility: **public**


#### Arguments
* $key **mixed**
* $value **mixed**



### getMetas

    array Core\Filestore\FileMetaHelper::getMetas()

Get the array of meta tags that are associated to this file object.



* Visibility: **public**




### getMeta

    array|\FileMetaModel|null Core\Filestore\FileMetaHelper::getMeta($key)

Get either a single Model object, null if it doesn't exist, or an array of them, (if keywords is requested).



* Visibility: **public**


#### Arguments
* $key **mixed**



### getMetaTitle

    mixed Core\Filestore\FileMetaHelper::getMetaTitle($key)





* Visibility: **public**


#### Arguments
* $key **mixed**



### getAsHTML

    string Core\Filestore\FileMetaHelper::getAsHTML()

Get this file's metadata as an HTML string, useful for giving credit for photos or sources.



* Visibility: **public**




### getForm

    \Form Core\Filestore\FileMetaHelper::getForm(string $prefix)

Get a form object pre-populated with this file's metadata.



* Visibility: **public**


#### Arguments
* $prefix **string** - &lt;p&gt;Form prefix for elements&lt;/p&gt;



### addElementsToForm

    mixed Core\Filestore\FileMetaHelper::addElementsToForm(\Form $form, string $prefix)

Just add the appropriate elements to a given form.



* Visibility: **public**


#### Arguments
* $form **[Form](form.md)**
* $prefix **string**



### offsetExists

    boolean Core\Filestore\FileMetaHelper::offsetExists(mixed $offset)

Whether an offset exists



* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;An offset to check for.&lt;/p&gt;



### offsetGet

    mixed Core\Filestore\FileMetaHelper::offsetGet(mixed $offset)

Offset to retrieve

Alias of Model::get()

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to retrieve.&lt;/p&gt;



### offsetSet

    void Core\Filestore\FileMetaHelper::offsetSet(mixed $offset, mixed $value)

Offset to set

Alias of Model::set()

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to assign the value to.&lt;/p&gt;
* $value **mixed** - &lt;p&gt;The value to set.&lt;/p&gt;



### offsetUnset

    void Core\Filestore\FileMetaHelper::offsetUnset(mixed $offset)

Offset to unset

This just sets the value to null.

* Visibility: **public**


#### Arguments
* $offset **mixed** - &lt;p&gt;The offset to unset.&lt;/p&gt;



### _getAsImageHTML

    mixed Core\Filestore\FileMetaHelper::_getAsImageHTML()





* Visibility: **private**




### GetMetaElements

    array Core\Filestore\FileMetaHelper::GetMetaElements()

Get an array of the form elements to use in the metadata edit page.



* Visibility: **public**
* This method is **static**.



