HTMLToMD\Converter
===============

A short teaser of what Converter does.

More lengthy description of what Converter does and why it's fantastic.

<h3>Usage Examples</h3>


* Class name: Converter
* Namespace: HTMLToMD





Properties
----------


### $persistentData

    public array $persistentData = array()





* Visibility: **public**


### $_html

    private mixed $_html





* Visibility: **private**


### $_dom

    private mixed $_dom





* Visibility: **private**


### $_tagMap

    private mixed $_tagMap = array('__default__' => 'HTMLToMD\\Elements\\DefaultElement', 'head' => 'HTMLToMD\\Elements\\SkipElement', 'style' => 'HTMLToMD\\Elements\\SkipElement', 'script' => 'HTMLToMD\\Elements\\SkipElement', 'object' => 'HTMLToMD\\Elements\\SkipElement', 'meta' => 'HTMLToMD\\Elements\\SkipElement', '#comment' => 'HTMLToMD\\Elements\\SkipElement', 'ol' => 'HTMLToMD\\Elements\\ListElement', 'ul' => 'HTMLToMD\\Elements\\ListElement', 'p' => 'HTMLToMD\\Elements\\BlockElement', 'div' => 'HTMLToMD\\Elements\\BlockElement', 'article' => 'HTMLToMD\\Elements\\BlockElement', 'section' => 'HTMLToMD\\Elements\\BlockElement', 'h1' => 'HTMLToMD\\Elements\\HeaderElement', 'h2' => 'HTMLToMD\\Elements\\HeaderElement', 'h3' => 'HTMLToMD\\Elements\\HeaderElement', 'h4' => 'HTMLToMD\\Elements\\HeaderElement', 'h5' => 'HTMLToMD\\Elements\\HeaderElement', 'h6' => 'HTMLToMD\\Elements\\HeaderElement', 'a' => 'HTMLToMD\\Elements\\LinkElement', 'table' => 'HTMLToMD\\Elements\\TableElement', 'code' => 'HTMLToMD\\Elements\\PreElement', 'pre' => 'HTMLToMD\\Elements\\PreElement')





* Visibility: **private**


### $_elementsCreated

    private array $_elementsCreated = array()





* Visibility: **private**


Methods
-------


### convert

    mixed HTMLToMD\Converter::convert($html)





* Visibility: **public**


#### Arguments
* $html **mixed**



### setHTML

    mixed HTMLToMD\Converter::setHTML($html)





* Visibility: **public**


#### Arguments
* $html **mixed**



### setTagHandler

    mixed HTMLToMD\Converter::setTagHandler(string $tag, string $handler)

Set a handler for a requested tag.



* Visibility: **public**


#### Arguments
* $tag **string** - &lt;p&gt;The tag name to target&lt;/p&gt;
* $handler **string** - &lt;p&gt;The class name of the handler to use for the tag&lt;/p&gt;



### getRootDOM

    \DOMDocument HTMLToMD\Converter::getRootDOM()

Get the root DOM for this request.



* Visibility: **public**




### _resolveNodeToElement

    \HTMLToMD\Elements\ElementInterface HTMLToMD\Converter::_resolveNodeToElement(\DOMNode $node)





* Visibility: **public**


#### Arguments
* $node **DOMNode**


