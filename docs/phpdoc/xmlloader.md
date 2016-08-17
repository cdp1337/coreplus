XMLLoader
===============

The XMLLoader object, capable of easily loading and saving XML data documents.

This class is slightly more complex than the SimpleXML system in PHP5, but simplier than direct DOM manipulation.


* Class name: XMLLoader
* Namespace: 
* This class implements: Serializable




Properties
----------


### $_rootname

    protected string $_rootname

The name of the root node.

This IS required for loading the xml file, as every file MUST have exactly one root node.

* Visibility: **protected**


### $_filename

    protected string $_filename

The filename of the original XML file.

This IS required and is used in loading and saving of the data.

* Visibility: **protected**


### $_file

    protected \Core\Filestore\File $_file

The file object of this XML Loader.

This is an option parameter for advanced usage, (ie: loading an XML file from a remote server).

* Visibility: **protected**


### $_DOM

    protected \DOMDocument $_DOM

The original DOM document for this object.



* Visibility: **protected**


### $_rootnode

    private null $_rootnode = null

Root node cache, used to make the getRootDOM faster by bypassing the lookup.



* Visibility: **private**


### $_schema

    protected null $_schema = null

Set this to a valid URL string to ensure that the document is set that for its root node.



* Visibility: **protected**


Methods
-------


### serialize

    string XMLLoader::serialize()

Serialize this object, preserving the underlying DOMDocument, (which otherwise wouldn't be perserved).



* Visibility: **public**




### unserialize

    mixed|void XMLLoader::unserialize(string $serialized)

Magic method called to convert a serialized object back to a valid XMLLoader object.



* Visibility: **public**


#### Arguments
* $serialized **string**



### load

    boolean XMLLoader::load()

Setup the internal DOMDocument for usage.

This MUST be called before any operations are applied to this object!

* Visibility: **public**




### loadFromFile

    boolean XMLLoader::loadFromFile(\Core\Filestore\File|string $file)

Load the document from a valid File object or a filename.



* Visibility: **public**


#### Arguments
* $file **[Core\Filestore\File](core_filestore_file.md)|string**



### loadFromNode

    boolean XMLLoader::loadFromNode(\DOMNode $node)

Load from a DOMNode



* Visibility: **public**


#### Arguments
* $node **DOMNode**



### loadFromString

    mixed XMLLoader::loadFromString($string)

Load from an XML string



* Visibility: **public**


#### Arguments
* $string **mixed** - &lt;p&gt;@return bool&lt;/p&gt;



### setFilename

    mixed XMLLoader::setFilename(string $file)

Set the filename for this XML document



* Visibility: **public**


#### Arguments
* $file **string**



### setRootName

    mixed XMLLoader::setRootName(string $name)

Set the root name for this XML document



* Visibility: **public**


#### Arguments
* $name **string**



### setSchema

    mixed XMLLoader::setSchema($url)

Method to set the schema externally.

This will update the DOM object if it's different.

* Visibility: **public**


#### Arguments
* $url **mixed**



### getRootDOM

    \DOMElement XMLLoader::getRootDOM()

Get the DOM root node



* Visibility: **public**




### getDOM

    \DOMDocument XMLLoader::getDOM()

Get the complete DOM object.



* Visibility: **public**




### getElementsByTagName

    \DOMNodeList XMLLoader::getElementsByTagName(string $name)

Searches for all elements with given tag name



* Visibility: **public**


#### Arguments
* $name **string**



### getElementByTagName

    \DOMNode XMLLoader::getElementByTagName(string $name)

Get the first element with the given tag name.



* Visibility: **public**


#### Arguments
* $name **string**



### getElement

    \DOMElement XMLLoader::getElement(string $path, boolean $autocreate)

This behaves just like getElementByTagName, with the exception that you can pass
'/' seperated paths of a node you want.

In simple, you can send it book/chapter/page and it will find the first book and its first chapter and its first page.

In addition, if the node does not exist it will be created automagically.

In addition, you can send arbitrary attributes and their values.
It will search for those, and again create them if they don't exist.

Everything is relative to the root, and /book is the same as book.

Examples:
<code>
// XML:
// <book>
//   <chapter chapter="1">
//     <page number="1">...</page>
//     ...
//     <page number="25">...</page>
//   </chapter>
// </book>

$this->getElement('page'); // Will return page 1 of chapter 1.
$this->getElement('chapter[chapter=1]/page[number=25]'); // Will return page 25 of chapter 1.
</code>

* Visibility: **public**


#### Arguments
* $path **string**
* $autocreate **boolean** - &lt;p&gt;Automatically create the element if it does not exist.&lt;/p&gt;



### getElementFrom

    \DOMElement XMLLoader::getElementFrom(string $path, \DOMNode|boolean $el, boolean $autocreate)

Lookup an element using XPath.



* Visibility: **public**


#### Arguments
* $path **string** - &lt;p&gt;The path to search for.&lt;/p&gt;
* $el **DOMNode|boolean** - &lt;p&gt;The element to search for the path in.&lt;/p&gt;
* $autocreate **boolean** - &lt;p&gt;Automatically create the element if it does not exist.&lt;/p&gt;



### _translatePath

    string XMLLoader::_translatePath(string $path)

Ensure a path is a valid one and absolute to the root node or relative.



* Visibility: **private**


#### Arguments
* $path **string**



### createElement

    boolean|\DOMElement|\DOMNode XMLLoader::createElement(string $path, boolean $el, integer $forcecreate)

Create an XML node based on the given path.

This will by default not create duplicate nodes of the same name, but can be forced to by using the $forcecreate option.

* Visibility: **public**


#### Arguments
* $path **string** - &lt;p&gt;Pathname to create, should be absolutely resolved if no $el is provided, otherwise relative is preferred.&lt;/p&gt;
* $el **boolean** - &lt;p&gt;Element to create this node as a child of, set to false to just use root node.&lt;/p&gt;
* $forcecreate **integer** - &lt;p&gt;Instructions on how to handle duplicate nodes.
0 - do not create any duplicate nodes, ie: unique attributes have to exist to create a different node
1 - create duplicate a node at the final tree level, (useful for nodes with no attributes)
2 - create all duplicate nodes from the root level on up, useful for creating completely different trees&lt;/p&gt;



### getElements

    \DOMNodeList XMLLoader::getElements($path)





* Visibility: **public**


#### Arguments
* $path **mixed**



### getElementsFrom

    \DOMNodeList XMLLoader::getElementsFrom(string $path, boolean|\DomNode $el)





* Visibility: **public**


#### Arguments
* $path **string** - &lt;p&gt;The path to search for.&lt;/p&gt;
* $el **boolean|DomNode** - &lt;p&gt;The element to start the search in, defaults to the root node.&lt;/p&gt;



### removeElements

    boolean XMLLoader::removeElements($path)

Remove elements that match the requested path from the XML object.

Shortcut of removeElementsFrom

* Visibility: **public**


#### Arguments
* $path **mixed**



### removeElementsFrom

    boolean XMLLoader::removeElementsFrom(string $path, \DOMNode $el)

Remove elements that match the requested path from the XML object.



* Visibility: **public**


#### Arguments
* $path **string**
* $el **DOMNode**



### elementToArray

    array XMLLoader::elementToArray(\DOMNode $el, boolean $nesting)

Converts a given element and its children into an associative array
containing all the values, attributes, and optionally children.



* Visibility: **public**


#### Arguments
* $el **DOMNode**
* $nesting **boolean**



### asXML

    string XMLLoader::asXML()

Get this XML object without ANY string manipulations!

This is useful if you have CDATA that needs to be preserved.

* Visibility: **public**




### asMinifiedXML

    string XMLLoader::asMinifiedXML()

Get this XML object as a minified string

NOTE, this DOES NOT PLAY NICELY WITH CDATA!!!!
Use asXML() for that!

* Visibility: **public**




### asPrettyXML

    string XMLLoader::asPrettyXML(boolean $html_output)

Prettifies an XML string into a human-readable and indented work of art

NOTE, this DOES NOT PLAY NICELY WITH CDATA!!!!
Use asXML() for that!

* Visibility: **public**


#### Arguments
* $html_output **boolean** - &lt;p&gt;True if the output should be escaped (for use in HTML)&lt;/p&gt;


